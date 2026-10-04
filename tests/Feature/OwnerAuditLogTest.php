<?php

namespace Tests\Feature;

use App\Support\Ledger;
use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class OwnerAuditLogTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_filters_and_activity_records(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $this->get('/owner/audit-log')->assertOk()->assertViewHas('activities', fn ($rows) => $rows->isEmpty());
        $marco = DB::table('users')->where('firstName', 'Marco')->value('userId');
        $payment = DB::table('payments')->where('customerId', $marco)->value('paymentId');
        Ledger::audit($marco, 'payments', 'Submitted GCash Payment', 'Payment submitted.', $payment);
        $cash = DB::table('payments')->where('paymentMethod', 'Cash')->first();
        Ledger::audit(auth()->id(), 'payments', 'Recorded Cash Payment', 'Payment received.', $cash->paymentId);
        $this->get('/owner/audit-log')->assertOk()->assertViewHas('activities', fn ($rows) => $rows->count() === 2);
        $this->get('/owner/audit-log?search=Marco&payment=gcash&date='.now()->toDateString())->assertOk()->assertSee('Submitted GCash Payment')->assertViewHas('activities', fn ($rows) => $rows->count() === 1 && $rows[0]->firstName === 'Marco');
        $this->get('/owner/audit-log?payment=cash')->assertOk()->assertViewHas('activities', fn ($rows) => $rows->count() === 1);
        $this->get('/owner/audit-log?search=missing')->assertOk()->assertSee('No activity matches your filters.');
        $this->get('/owner/audit-log?date=invalid')->assertSessionHasErrors('date');
    }

    public function test_preview_is_local_only(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/audit-log')->assertRedirect('/login');
    }
}
