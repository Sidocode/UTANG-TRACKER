<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UtangDetailController extends Controller
{
    public function __invoke(int $debt): View
    {

        $record = DB::table('debts')->join('users', 'debts.customerId', '=', 'users.userId')
            ->where('debts.debtId', $debt)->where('users.role', 'customer')
            ->select('debts.*', 'users.firstName', 'users.lastName')->first();
        abort_unless($record, 404);
        $payments = DB::table('payments')->where('debtId', $debt)->where('customerId', $record->customerId)
            ->orderByDesc('paymentDate')->orderByDesc('paymentId')->get();

        return view('owner.utang-details', [
            'debt' => $record, 'payments' => $payments,
            'amountPaid' => $payments->where('paymentStatus', 'Verified')->sum('paymentAmount'),
            'status' => $record->remaining <= 0 ? 'Paid' : ($record->remaining < $record->debtAmount ? 'Partial' : 'Unpaid'),
        ]);
    }
}
