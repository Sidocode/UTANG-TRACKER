<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __invoke(Request $request): View
    {

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:paid,unpaid,partial'],
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $query = DB::table('transactions')
            ->join('users', 'transactions.customerId', '=', 'users.userId')
            ->leftJoin('debts', 'transactions.transactionId', '=', 'debts.transactionId')
            ->select('transactions.transactionId', 'transactions.date', 'transactions.totalAmount', 'users.firstName', 'users.lastName')
            ->selectRaw("CASE WHEN COALESCE(debts.remaining, 0) <= 0 THEN 'Paid' WHEN debts.remaining < debts.debtAmount THEN 'Partial' ELSE 'Unpaid' END AS paymentStatus");
        if ($search = trim($filters['search'] ?? '')) {
            $query->where(function ($query) use ($search) {
                $query->whereRaw("LOWER(CONCAT(firstName, ' ', lastName)) LIKE ?", ['%'.mb_strtolower($search).'%'])
                    ->orWhere('mobileNumber', 'like', '%'.$search.'%');
            });
        }
        if ($filters['date'] ?? '') {
            $query->whereDate('transactions.date', $filters['date']);
        }
        $transactions = DB::query()->fromSub($query, 'records');
        if ($filters['status'] ?? '') {
            $transactions->where('paymentStatus', ucfirst($filters['status']));
        }

        return view('owner.transactions', [
            'transactions' => $transactions->orderByDesc('date')->orderByDesc('transactionId')->get(),
            'filters' => $filters,
        ]);
    }
}
