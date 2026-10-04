<?php

namespace Tests\Feature;

use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class OwnerDashboardTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_database_totals_and_pending_queue(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $paymentId = DB::table('payments')->where('referenceNumber', '602997188333')->value('paymentId');
        $this->get('/owner/dashboard')->assertSee(route('owner.payments.verification', $paymentId))->assertSee('data-detail-row', false);
        $this->get('/owner/dashboard')->assertOk()->assertSee('₱6,100')->assertSee('₱2,500')->assertSee('10 active')->assertSee('Ana Santos')->assertSee('Marco Reyes')->assertDontSee('No GCash payments awaiting verification.');
    }

    public function test_empty_database_displays_empty_states(): void
    {
        $this->withoutVite();
        $this->get('/owner/dashboard')->assertOk()->assertSee('No transactions yet.')->assertSee('No payments yet.');
    }

    public function test_preview_is_not_exposed_in_production_without_authentication(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/dashboard')->assertRedirect('/login');
    }
}
