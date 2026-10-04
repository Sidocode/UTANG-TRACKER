<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerDetailController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, int $customer): View
    {

        $person = DB::table('users')->where('role', 'customer')->where('userId', $customer)->first();
        abort_unless($person, 404);
        $tab = $request->validate(['tab' => ['nullable', 'in:overview,transactions,utang,payments']])['tab'] ?? 'overview';
        $debts = DB::table('debts')->where('customerId', $customer)->orderByDesc('debtId')->get();
        foreach ($debts as $debt) {
            $debt->amountPaid = $debt->debtAmount - $debt->remaining;
            $debt->displayStatus = $debt->remaining <= 0 ? 'Paid' : ($debt->remaining < $debt->debtAmount ? 'Partial' : 'Unpaid');
        }
        $debtsByTransaction = $debts->keyBy('transactionId');
        $transactions = DB::table('transactions')->where('customerId', $customer)->orderByDesc('date')->orderByDesc('transactionId')->get();
        foreach ($transactions as $transaction) {
            $debt = $debtsByTransaction->get($transaction->transactionId);
            $transaction->remaining = $debt?->remaining ?? 0;
            $transaction->amountPaid = $debt?->amountPaid ?? $transaction->totalAmount;
            $transaction->displayStatus = $debt?->displayStatus ?? 'Paid';
        }
        $payments = DB::table('payments')->where('customerId', $customer)->orderByDesc('paymentDate')->orderByDesc('paymentId')->get();
        $activity = $transactions->map(fn ($transaction) => (object) [
            'date' => $transaction->date, 'type' => 'Transaction', 'amount' => $transaction->totalAmount, 'status' => $transaction->displayStatus,
        ])->concat($payments->map(fn ($payment) => (object) [
            'date' => $payment->paymentDate, 'type' => 'Payment', 'amount' => $payment->paymentAmount, 'status' => $payment->paymentStatus,
        ]))->sortByDesc('date')->values();

        return view('owner.customer-details', [
            'customer' => $person, 'tab' => $tab, 'debts' => $debts, 'transactions' => $transactions,
            'payments' => $payments, 'activity' => $activity, 'balance' => $debts->sum('remaining'),
            'totalPayments' => $payments->where('paymentStatus', 'Verified')->sum('paymentAmount'),
        ]);
    }
}
