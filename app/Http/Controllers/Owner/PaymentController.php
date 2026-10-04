<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __invoke(Request $request): View
    {

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'payment' => ['nullable', 'in:cash,gcash'],
            'status' => ['nullable', 'in:pending,verified,rejected'],
        ]);
        $query = DB::table('payments')->join('users', 'payments.customerId', '=', 'users.userId')
            ->select('payments.*', 'users.firstName', 'users.lastName');
        if ($search = trim($filters['search'] ?? '')) {
            $query->where(function ($query) use ($search) {
                $query->whereRaw("LOWER(CONCAT(firstName, ' ', lastName)) LIKE ?", ['%'.mb_strtolower($search).'%'])
                    ->orWhere('mobileNumber', 'like', '%'.$search.'%');
            });
        }
        if ($filters['payment'] ?? '') {
            $query->whereRaw('LOWER(paymentMethod) = ?', [$filters['payment']]);
        }
        if ($filters['status'] ?? '') {
            $query->where('paymentStatus', ucfirst($filters['status']));
        }

        return view('owner.payments', [
            'payments' => $query->orderByDesc('paymentDate')->orderByDesc('paymentId')->get(),
            'filters' => $filters,
            'paymentSettings' => DB::table('payment_settings')->where('userId', $request->user()->userId)->first(),
            'paymentDebts' => DB::table('debts')->join('users', 'debts.customerId', '=', 'users.userId')
                ->where('debts.remaining', '>', 0)->where('users.role', 'customer')
                ->select('debts.debtId', 'debts.customerId', 'debts.transactionId', 'debts.remaining', 'users.firstName', 'users.lastName')
                ->orderBy('users.firstName')->get(),
        ]);
    }
}
