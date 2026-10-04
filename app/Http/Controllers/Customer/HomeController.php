<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $customer = $request->user();
        $debts = DB::table('debts')->where('customerId', $customer->userId)->get();
        $payments = DB::table('payments')->where('customerId', $customer->userId)->orderByDesc('paymentDate')->orderByDesc('paymentId')->get();
        $transactions = DB::table('transactions')->where('customerId', $customer->userId)->get();
        if ($request->routeIs('customer.transaction.show')) {
            $transaction = $transactions->firstWhere('transactionId', (int) $request->route('transaction'));
            abort_unless($transaction, 404);
            $items = DB::table('transaction_items')->where('transactionId', $transaction->transactionId)->orderBy('itemId')->get();

            return view('customer.transaction-details', compact('customer', 'transaction', 'items'));
        }
        $activity = $payments->map(fn ($payment) => [
            'date' => $payment->paymentDate, 'amount' => $payment->paymentAmount,
            'type' => $payment->paymentMethod, 'description' => 'Payment submitted', 'status' => $payment->paymentStatus,
        ])->concat($transactions->map(function ($transaction) use ($debts) {
            $debt = $debts->firstWhere('transactionId', $transaction->transactionId);

            return ['date' => $transaction->date, 'amount' => $transaction->totalAmount, 'type' => 'Transaction',
                'description' => 'TXN-'.$transaction->transactionId,
                'status' => ! $debt || $debt->remaining <= 0 ? 'Paid' : ($debt->remaining < $debt->debtAmount ? 'Partial' : 'Unpaid')];
        }))->sortByDesc('date')->values();

        if ($request->routeIs('customer.balance')) {
            $activity = $debts->map(function ($debt) use ($transactions) {
                return ['date' => $transactions->firstWhere('transactionId', $debt->transactionId)?->date,
                    'amount' => $debt->debtAmount, 'description' => 'Original Debt'];
            })->concat($payments->where('paymentStatus', 'Verified')->map(fn ($payment) => [
                'date' => $payment->verifiedAt ?? $payment->paymentDate,
                'amount' => -$payment->paymentAmount, 'description' => 'Payment',
            ]))->sortBy('date')->values();
            if ($activity->isNotEmpty()) {
                $activity->push(['date' => $activity->last()['date'], 'amount' => $debts->sum('remaining'), 'description' => 'Remaining Balance']);
            }
        }

        $view = $request->routeIs('customer.transaction') ? 'customer.transaction' : ($request->routeIs('customer.balance') ? 'customer.balance' : 'customer.home');
        if ($request->routeIs('customer.payment')) {
            $view = 'customer.payment';
        }
        if ($request->routeIs('customer.payment.status')) {
            $view = 'customer.payment-status';
            $request->validate(['payment' => ['required', 'integer']]);
            $submittedPayment = $payments->firstWhere('paymentId', (int) $request->input('payment'));
            abort_unless($submittedPayment, 404);
        }
        if ($request->routeIs('customer.profile')) {
            $view = 'customer.profile';
        }
        if ($request->routeIs('customer.notifications')) {
            $view = 'customer.notifications';
        }

        return view($view, [
            'customer' => $customer, 'balance' => $debts->sum('remaining'), 'totalDebt' => $debts->sum('debtAmount'),
            'recentPayment' => $payments->firstWhere('paymentStatus', 'Verified')?->paymentAmount ?? 0,
            'activity' => $activity,
            'transactions' => $transactions->sortByDesc('date')->values(),
            'outstandingDebts' => $debts->where('remaining', '>', 0)->values(),
            'paymentSettings' => DB::table('payment_settings')->orderBy('paymentSettingId')->first(),
            'submittedPayment' => $submittedPayment ?? null,
            'notifications' => DB::table('notifications')->where('userId', $customer->userId)->orderByDesc('createdAt')->orderByDesc('notificationId')->get(),
        ]);
    }
}
