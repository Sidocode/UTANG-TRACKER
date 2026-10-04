<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {

        $customers = DB::table('users')->where('role', 'customer');
        $unpaid = DB::table('debts')->where('remaining', '>', 0);
        $payments = DB::table('payments')->join('users', 'payments.customerId', '=', 'users.userId');
        $pending = (clone $payments)->where('paymentMethod', 'GCash')->where('paymentStatus', 'Pending');

        return view('owner.dashboard', [
            'stats' => [
                'customers' => (clone $customers)->count(),
                'active' => (clone $customers)->where('status', 'Active')->count(),
                'outstanding' => DB::table('debts')->sum('remaining'),
                'debtors' => (clone $unpaid)->distinct()->count('customerId'),
                'received' => DB::table('payments')->where('paymentStatus', 'Verified')->sum('paymentAmount'),
                'unpaid' => $unpaid->count(),
                'pending' => (clone $pending)->count(),
            ],
            'transactions' => DB::table('transactions')
                ->join('users', 'transactions.customerId', '=', 'users.userId')
                ->leftJoin('debts', 'transactions.transactionId', '=', 'debts.transactionId')
                ->select('transactions.*', 'users.firstName', 'users.lastName', 'debts.remaining', 'debts.debtAmount')
                ->orderByDesc('date')->orderByDesc('transactions.transactionId')->limit(20)->get(),
            'payments' => (clone $payments)->orderByDesc('paymentDate')->orderByDesc('paymentId')->limit(20)->get(),
            'pendingPayments' => $pending->orderByDesc('paymentDate')->orderByDesc('paymentId')->limit(20)->get(),
        ]);
    }
}
