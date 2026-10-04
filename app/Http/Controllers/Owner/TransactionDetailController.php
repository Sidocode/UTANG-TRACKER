<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionDetailController extends Controller
{
    public function __invoke(int $transaction): View
    {

        $record = DB::table('transactions')->join('users', 'transactions.customerId', '=', 'users.userId')->leftJoin('debts', 'transactions.transactionId', '=', 'debts.transactionId')->where('transactions.transactionId', $transaction)->select('transactions.*', 'users.firstName', 'users.lastName', 'debts.remaining', 'debts.debtAmount')->first();
        abort_unless($record, 404);
        $status = $record->remaining === null ? '—' : ($record->remaining <= 0 ? 'Paid' : ($record->remaining < $record->debtAmount ? 'Partial' : 'Unpaid'));

        return view('owner.transaction-details', [
            'transaction' => $record,
            'status' => $status,
            'canModify' => ! DB::table('payments')->join('debts', 'payments.debtId', '=', 'debts.debtId')->where('debts.transactionId', $transaction)->exists(),
            'items' => DB::table('transaction_items')->where('transactionId', $transaction)->orderBy('itemId')->get(),
        ]);
    }
}
