<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UtangController extends Controller
{
    public function __invoke(Request $request): View
    {

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'customer' => ['nullable', 'integer', 'exists:users,userId'],
            'status' => ['nullable', 'in:paid,unpaid,partial'],
        ]);
        $query = DB::table('debts')->join('users', 'debts.customerId', '=', 'users.userId')
            ->select('debts.*', 'users.firstName', 'users.lastName')
            ->selectRaw('debts.debtAmount - debts.remaining AS amountPaid')
            ->selectRaw("CASE WHEN debts.remaining <= 0 THEN 'Paid' WHEN debts.remaining < debts.debtAmount THEN 'Partial' ELSE 'Unpaid' END AS displayStatus");
        if ($search = trim($filters['search'] ?? '')) {
            $query->where(function ($query) use ($search) {
                $query->whereRaw("LOWER(CONCAT(firstName, ' ', lastName)) LIKE ?", ['%'.mb_strtolower($search).'%'])
                    ->orWhere('mobileNumber', 'like', '%'.$search.'%');
            });
        }
        if ($filters['customer'] ?? '') {
            $query->where('debts.customerId', $filters['customer']);
        }
        $debts = DB::query()->fromSub($query, 'records');
        if ($filters['status'] ?? '') {
            $debts->where('displayStatus', ucfirst($filters['status']));
        }
        $customers = DB::table('users')->where('role', 'customer')->whereIn('userId', DB::table('debts')->select('customerId'))
            ->orderBy('firstName')->orderBy('lastName')->get(['userId', 'firstName', 'lastName'])
            ->mapWithKeys(fn ($customer) => [(string) $customer->userId => $customer->firstName.' '.$customer->lastName])->all();

        return view('owner.utang', ['debts' => $debts->orderByDesc('debtId')->get(), 'customers' => $customers, 'filters' => $filters]);
    }
}
