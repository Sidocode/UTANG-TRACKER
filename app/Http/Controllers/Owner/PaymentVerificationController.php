<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentVerificationController extends Controller
{
    public function __invoke(int $payment): View
    {

        $record = DB::table('payments')->join('users', 'payments.customerId', '=', 'users.userId')
            ->where('payments.paymentId', $payment)->where('payments.paymentStatus', 'Pending')
            ->whereRaw('LOWER(payments.paymentMethod) = ?', ['gcash'])
            ->select('payments.*', 'users.firstName', 'users.lastName')->first();
        abort_unless($record, 404);

        return view('owner.payment-verification', ['payment' => $record]);
    }
}
