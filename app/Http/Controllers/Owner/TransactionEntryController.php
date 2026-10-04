<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionEntryController extends Controller
{
    public function __invoke(?int $transaction = null): View
    {
        $record = null;
        $items = collect();
        if ($transaction) {
            $record = DB::table('transactions')->where('transactionId', $transaction)->first();
            abort_unless($record, 404);
            $debt = DB::table('debts')->where('transactionId', $transaction)->first();
            abort_if(DB::table('payments')->where('debtId', $debt->debtId)->exists(), 409, 'This transaction has payment history and cannot be modified.');
            $items = DB::table('transaction_items')->where('transactionId', $transaction)->orderBy('itemId')->get();
        }

        return view('owner.transaction-entry', [
            'transaction' => $record, 'items' => $items,
            'customers' => DB::table('users')->where('role', 'customer')->where('status', 'Active')
                ->orderBy('firstName')->orderBy('lastName')->get(['userId', 'firstName', 'lastName']),
        ]);
    }
}
