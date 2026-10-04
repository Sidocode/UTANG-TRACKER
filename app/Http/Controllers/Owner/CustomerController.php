<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __invoke(Request $request): View
    {

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'debt' => ['nullable', 'in:paid,unpaid'],
            'account' => ['nullable', 'in:Active,Inactive,Deactivated'],
        ]);
        $balances = DB::table('debts')->select('customerId')
            ->selectRaw('SUM(remaining) as balance, COUNT(*) as debt_count')->groupBy('customerId');
        $customers = DB::table('users')->where('role', 'customer')
            ->leftJoinSub($balances, 'balances', 'users.userId', '=', 'balances.customerId')
            ->select('users.userId', 'firstName', 'lastName', 'mobileNumber', 'status')
            ->selectRaw('COALESCE(balance, 0) as balance, COALESCE(debt_count, 0) as debt_count');

        if ($search = trim($filters['search'] ?? '')) {
            $customers->where(function ($query) use ($search) {
                $query->whereRaw("LOWER(CONCAT(firstName, ' ', lastName)) LIKE ?", ['%'.mb_strtolower($search).'%'])
                    ->orWhere('mobileNumber', 'like', '%'.$search.'%');
            });
        }
        if (($filters['debt'] ?? '') === 'unpaid') {
            $customers->where('balance', '>', 0);
        } elseif (($filters['debt'] ?? '') === 'paid') {
            $customers->where('balance', '<=', 0)->where('debt_count', '>', 0);
        }
        if ($filters['account'] ?? '') {
            $customers->where('status', $filters['account']);
        }

        return view('owner.customers', [
            'customers' => $customers->orderBy('firstName')->orderBy('lastName')->get(),
            'filters' => $filters,
            'pendingRegistrationCount' => DB::table('registration_requests')->where('status', 'Pending')->count(),
        ]);
    }
}
