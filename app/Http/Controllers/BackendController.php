<?php

namespace App\Http\Controllers;

use App\Support\Ledger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BackendController extends Controller
{
    public function settings(Request $request): JsonResponse
    {
        $existing = DB::table('payment_settings')->where('userId', $request->user()->userId)->first();
        $data = $request->validate([
            'accountName' => ['required', 'string', 'max:100'],
            'mobileNumber' => ['required', 'regex:/^09[0-9]{9}$/'],
            'qrImage' => [$existing ? 'nullable' : 'required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ]);
        $path = $request->file('qrImage')?->store('gcash', 'local') ?? $existing?->qrImagePath;
        try {
            DB::transaction(function () use ($request, $data, $path): void {
                DB::table('payment_settings')->updateOrInsert(['userId' => $request->user()->userId], [
                    'accountName' => $data['accountName'], 'mobileNumber' => $data['mobileNumber'], 'qrImagePath' => $path,
                ]);
                Ledger::audit($request->user()->userId, 'payment_settings', 'Updated GCash settings', 'GCash recipient details updated.');
            });
        } catch (\Throwable $error) {
            if ($path !== $existing?->qrImagePath) {
                Storage::disk('local')->delete($path);
            }
            throw $error;
        }
        if ($existing && $path !== $existing->qrImagePath) {
            Storage::disk('local')->delete($existing->qrImagePath);
        }

        return response()->json(['message' => 'GCash settings saved.']);
    }

    public function qr(int $setting): BinaryFileResponse
    {
        $row = DB::table('payment_settings')->where('paymentSettingId', $setting)->first();
        abort_unless($row && Storage::disk('local')->exists($row->qrImagePath), 404);

        return response()->file(Storage::disk('local')->path($row->qrImagePath), ['Cache-Control' => 'private, no-store']);
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'firstName' => ['required', 'string', 'max:100'], 'lastName' => ['required', 'string', 'max:100'],
            'mobileNumber' => ['required', 'regex:/^09[0-9]{9}$/', 'unique:users,mobileNumber', 'unique:registration_requests,mobileNumber'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);
        $owner = $request->routeIs('owner.customers.store');
        DB::transaction(function () use ($request, $data, $owner): void {
            $record = ['firstName' => $data['firstName'], 'lastName' => $data['lastName'], 'mobileNumber' => $data['mobileNumber'], 'password' => Hash::make($data['password'])];
            if ($owner) {
                $id = DB::table('users')->insertGetId($record + ['name' => $data['firstName'].' '.$data['lastName'], 'role' => 'customer', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now()], 'userId');
                Ledger::audit($request->user()->userId, 'users', 'Registered customer', 'Created customer #'.$id.'.');
            } else {
                $id = DB::table('registration_requests')->insertGetId($record + ['status' => 'Pending', 'submittedAt' => now()], 'requestId');
                $request->session()->put('registration_request', $id);
                foreach (DB::table('users')->where('role', 'owner')->where('status', 'Active')->pluck('userId') as $user) {
                    Ledger::notify($user, 'Pending registration', $data['firstName'].' '.$data['lastName'].' submitted a registration request.');
                }
            }
        });

        return response()->json(['redirect' => route($owner ? 'owner.customers' : 'customer.registration.pending')]);
    }

    public function registrationStatus(Request $request): View
    {
        $id = $request->session()->get('registration_request');
        $registration = $id ? DB::table('registration_requests')->where('requestId', $id)->first() : null;

        return view('auth.registration-pending', ['registration' => $registration, 'rejected' => $id && ! $registration]);
    }

    public function approve(Request $request, int $registration): JsonResponse
    {
        DB::transaction(function () use ($request, $registration): void {
            $row = DB::table('registration_requests')->where('requestId', $registration)->lockForUpdate()->first();
            abort_unless($row, 404);
            if ($row->status === 'Approved') {
                return;
            }
            abort_unless($row->status === 'Pending', 409);
            if (DB::table('users')->where('mobileNumber', $row->mobileNumber)->exists()) {
                throw ValidationException::withMessages(['mobileNumber' => 'This mobile number already has an account.']);
            }
            $id = DB::table('users')->insertGetId([
                'firstName' => $row->firstName, 'lastName' => $row->lastName, 'name' => $row->firstName.' '.$row->lastName,
                'mobileNumber' => $row->mobileNumber, 'password' => $row->password, 'role' => 'customer', 'status' => 'Active', 'created_at' => now(), 'updated_at' => now(),
            ], 'userId');
            DB::table('registration_requests')->where('requestId', $registration)->update(['status' => 'Approved', 'reviewedAt' => now()]);
            Ledger::audit($request->user()->userId, 'registration_requests', 'Approved registration', 'Approved request #'.$registration.'.');
            Ledger::notify($id, 'Registration approved', 'Your account is now active.');
        });

        return response()->json(['redirect' => route('owner.customers.pending')]);
    }

    public function deactivate(Request $request, int $customer): JsonResponse
    {
        DB::transaction(function () use ($request, $customer): void {
            $row = DB::table('users')->where('userId', $customer)->where('role', 'customer')->lockForUpdate()->first();
            abort_unless($row, 404);
            $activate = $request->routeIs('owner.customers.activate');
            DB::table('users')->where('userId', $customer)->update(['status' => $activate ? 'Active' : 'Inactive', 'remember_token' => null, 'updated_at' => now()]);
            DB::table('sessions')->where('user_id', $customer)->delete();
            Ledger::audit($request->user()->userId, 'users', $activate ? 'Activated customer' : 'Deactivated customer', 'Changed status of customer #'.$customer.'.');
        });

        return response()->json(['redirect' => route('owner.customers')]);
    }

    public function transaction(Request $request, ?int $transaction = null): JsonResponse
    {
        $data = $request->validate([
            'customer' => ['required', 'integer', Rule::exists('users', 'userId')->where('role', 'customer')->where('status', 'Active')],
            'submissionKey' => [$transaction ? 'nullable' : 'required', 'uuid'], 'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.name' => ['required', 'string', 'max:150'], 'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'items.*.price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999.99'],
        ]);
        DB::transaction(function () use ($request, $data, $transaction): void {
            $customer = DB::table('users')->where('userId', $data['customer'])->lockForUpdate()->first();
            abort_unless($customer->status === 'Active', 409);
            $existing = isset($data['submissionKey']) ? DB::table('transactions')->where('submissionKey', $data['submissionKey'])->first() : null;
            if ($existing) {
                abort_unless((int) $existing->customerId === (int) $data['customer'], 409);

                return;
            }
            $total = array_sum(array_map(fn (array $item): int => Ledger::cents($item['price']) * $item['quantity'], $data['items']));
            if ($total > 999999999999) {
                throw ValidationException::withMessages(['items' => 'The transaction total is too large.']);
            }
            if ($transaction) {
                $record = DB::table('transactions')->where('transactionId', $transaction)->lockForUpdate()->first();
                abort_unless($record, 404);
                $debt = DB::table('debts')->where('transactionId', $transaction)->lockForUpdate()->first();
                if (DB::table('payments')->where('debtId', $debt->debtId)->exists()) {
                    throw ValidationException::withMessages(['transaction' => 'This transaction has payment history and cannot be modified.']);
                }
                if ((int) $record->customerId !== (int) $data['customer']) {
                    throw ValidationException::withMessages(['customer' => 'The customer of a recorded transaction cannot be changed.']);
                }
                $id = $transaction;
                DB::table('transactions')->where('transactionId', $id)->update(['totalAmount' => Ledger::money($total)]);
                DB::table('transaction_items')->where('transactionId', $id)->delete();
                DB::table('debts')->where('debtId', $debt->debtId)->update(['debtAmount' => Ledger::money($total), 'remaining' => Ledger::money($total), 'debtStatus' => 'Unpaid']);
            } else {
                $id = DB::table('transactions')->insertGetId(['customerId' => $data['customer'], 'date' => now(), 'totalAmount' => Ledger::money($total), 'submissionKey' => $data['submissionKey']], 'transactionId');
                DB::table('debts')->insert(['customerId' => $data['customer'], 'transactionId' => $id, 'debtAmount' => Ledger::money($total), 'remaining' => Ledger::money($total), 'debtStatus' => 'Unpaid']);
            }
            foreach ($data['items'] as $item) {
                DB::table('transaction_items')->insert(['transactionId' => $id, 'itemName' => $item['name'], 'quantity' => $item['quantity'], 'price' => $item['price'], 'subtotal' => Ledger::money(Ledger::cents($item['price']) * $item['quantity'])]);
            }
            Ledger::audit($request->user()->userId, 'transactions', $transaction ? 'Modified transaction' : 'Created transaction', 'Transaction #'.$id.' for ₱'.Ledger::money($total).'.');
            Ledger::notify($data['customer'], $transaction ? 'Transaction updated' : 'New transaction', 'Transaction #'.$id.' was recorded for ₱'.Ledger::money($total).'.');
        });

        return response()->json(['redirect' => route('owner.transactions')]);
    }

    public function payment(Request $request): JsonResponse
    {
        $customerSubmission = $request->routeIs('customer.payment.store');
        $data = $request->validate([
            'debt' => ['required', 'integer'], 'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'submissionKey' => ['required', 'uuid'],
            'customer' => [$customerSubmission ? 'nullable' : 'required', 'integer'],
            'referenceNumber' => [$customerSubmission ? 'required' : 'exclude', 'regex:/^[0-9]{6,30}$/'],
        ]);
        $id = DB::transaction(function () use ($request, $data, $customerSubmission): int {
            $customer = $customerSubmission ? $request->user()->userId : $data['customer'];
            $user = DB::table('users')->where('userId', $customer)->where('role', 'customer')->lockForUpdate()->first();
            abort_unless($user && $user->status === 'Active', 404);
            $existing = DB::table('payments')->where('submissionKey', $data['submissionKey'])->first();
            if ($existing) {
                abort_unless((int) $existing->customerId === (int) $customer, 409);

                return $existing->paymentId;
            }
            $debt = DB::table('debts')->where('debtId', $data['debt'])->where('customerId', $customer)->lockForUpdate()->first();
            abort_unless($debt, 404);
            $amount = Ledger::cents($data['amount']);
            if ($amount > Ledger::cents($debt->remaining)) {
                throw ValidationException::withMessages(['amount' => 'The amount exceeds the remaining balance.']);
            }
            $method = $customerSubmission ? 'GCash' : 'Cash';
            $reference = $method === 'GCash' ? $data['referenceNumber'] : null;
            if ($reference && DB::table('payments')->where('referenceNumber', $reference)->exists()) {
                throw ValidationException::withMessages(['referenceNumber' => 'This GCash reference number has already been submitted.']);
            }
            if ($customerSubmission && ! DB::table('payment_settings')->exists()) {
                throw ValidationException::withMessages(['payment' => 'The owner has not configured GCash yet.']);
            }
            $id = DB::table('payments')->insertGetId([
                'customerId' => $customer, 'debtId' => $debt->debtId, 'paymentAmount' => Ledger::money($amount),
                'paymentMethod' => $method, 'paymentStatus' => $customerSubmission ? 'Pending' : 'Verified', 'paymentDate' => now(),
                'referenceNumber' => $reference, 'verifiedAt' => $customerSubmission ? null : now(), 'submissionKey' => $data['submissionKey'],
            ], 'paymentId');
            if (! $customerSubmission) {
                Ledger::applyPayment($debt, $amount);
            }
            Ledger::audit($request->user()->userId, 'payments', $customerSubmission ? 'Submitted GCash Payment' : 'Recorded '.$method.' Payment', 'Payment #'.$id.' for ₱'.Ledger::money($amount).'.', $id);
            Ledger::notify($customer, $customerSubmission ? 'Payment pending' : 'Payment verified', 'Payment #'.$id.' for ₱'.Ledger::money($amount).($customerSubmission ? ' is awaiting verification.' : ' was applied to your balance.'));
            if ($customerSubmission) {
                foreach (DB::table('users')->where('role', 'owner')->where('status', 'Active')->pluck('userId') as $owner) {
                    Ledger::notify($owner, 'GCash payment needs verification', $user->firstName.' '.$user->lastName.' submitted a GCash payment of ₱'.Ledger::money($amount).'. Payment #'.$id.' needs your verification.');
                }
            }

            return $id;
        });

        return response()->json(['redirect' => $customerSubmission ? route('customer.payment.status', ['payment' => $id]) : route('owner.payments')]);
    }

    public function review(Request $request, int $payment): JsonResponse
    {
        $data = $request->validate(['action' => ['required', Rule::in(['verify', 'reject'])]]);
        DB::transaction(function () use ($request, $payment, $data): void {
            $row = DB::table('payments')->where('paymentId', $payment)->lockForUpdate()->first();
            abort_unless($row && $row->paymentMethod === 'GCash', 404);
            $status = $data['action'] === 'verify' ? 'Verified' : 'Rejected';
            if ($row->paymentStatus === $status) {
                return;
            }
            if ($row->paymentStatus !== 'Pending') {
                throw ValidationException::withMessages(['payment' => 'This payment was already reviewed.']);
            }
            $debt = DB::table('debts')->where('debtId', $row->debtId)->lockForUpdate()->first();
            if ($status === 'Verified') {
                if (Ledger::cents($row->paymentAmount) > Ledger::cents($debt->remaining)) {
                    throw ValidationException::withMessages(['payment' => 'This payment exceeds the current remaining balance.']);
                }
                Ledger::applyPayment($debt, Ledger::cents($row->paymentAmount));
            }
            DB::table('payments')->where('paymentId', $payment)->update(['paymentStatus' => $status, 'verifiedAt' => $status === 'Verified' ? now() : null]);
            Ledger::audit($request->user()->userId, 'payments', $status.' GCash Payment', 'Payment #'.$payment.' was '.strtolower($status).'.', $payment);
            Ledger::notify(
                $row->customerId,
                'GCash payment '.strtolower($status),
                $status === 'Verified'
                    ? 'The owner verified your GCash payment of ₱'.Ledger::money(Ledger::cents($row->paymentAmount)).'. Your remaining transaction balance is ₱'.Ledger::money(Ledger::cents($debt->remaining) - Ledger::cents($row->paymentAmount)).'.'
                    : 'The owner rejected your GCash payment of ₱'.Ledger::money(Ledger::cents($row->paymentAmount)).'. Your balance has not changed.',
            );
        });

        return response()->json(['redirect' => route('owner.payments')]);
    }

    public function unreadNotifications(Request $request): JsonResponse
    {
        return response()->json(['unread' => DB::table('notifications')->where('userId', $request->user()->userId)->whereNull('readAt')->count()]);
    }

    public function readNotification(Request $request, int $notification): JsonResponse
    {
        $row = DB::table('notifications')->where('notificationId', $notification)->where('userId', $request->user()->userId)->first();
        abort_unless($row, 404);
        DB::table('notifications')->where('notificationId', $notification)->whereNull('readAt')->update(['readAt' => now()]);

        return response()->json(['message' => 'Marked as read.']);
    }
}
