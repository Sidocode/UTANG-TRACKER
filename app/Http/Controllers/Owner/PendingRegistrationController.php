<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Support\Ledger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PendingRegistrationController extends Controller
{
    public function reject(Request $request, int $registration): RedirectResponse
    {

        DB::transaction(function () use ($request, $registration): void {
            $deleted = DB::table('registration_requests')->where('requestId', $registration)->where('status', 'Pending')->delete();
            abort_unless($deleted, 404);
            Ledger::audit($request->user()->userId, 'registration_requests', 'Rejected registration', 'Removed request #'.$registration.'.');
        });

        return redirect()->route('owner.customers.pending')->with('registration_status', 'Registration request rejected and removed.');
    }

    public function __invoke(): View
    {

        return view('owner.pending-registrations', [
            'requests' => DB::table('registration_requests')->where('status', 'Pending')
                ->select('requestId', 'firstName', 'lastName', 'mobileNumber', 'submittedAt', 'status')
                ->orderByDesc('submittedAt')->orderByDesc('requestId')->get(),
        ]);
    }
}
