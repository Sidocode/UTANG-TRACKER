<?php

namespace Tests\Feature;

use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class OwnerPaymentsTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_rejected_demo_is_idempotent_and_does_not_reduce_debt(): void
    {
        $this->seed(DemoCustomerSeeder::class);
        $this->seed(DemoCustomerSeeder::class);
        $this->assertSame(1, DB::table('payments')->where('paymentStatus', 'Rejected')->count());
        $this->assertSame(1, DB::table('payments')->where('paymentStatus', 'Pending')->whereNull('verifiedAt')->count());
        $this->assertEquals(6100, DB::table('debts')->sum('remaining'));
        $this->assertEquals(2500, DB::table('payments')->where('paymentStatus', 'Verified')->sum('paymentAmount'));
    }

    public function test_payment_filters_and_empty_results(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $this->get('/owner/payments')->assertOk()->assertViewHas('payments', fn ($rows) => $rows->count() === 6);
        $this->get('/owner/payments?search=Ana&payment=gcash&status=rejected')->assertOk()
            ->assertViewHas('payments', fn ($rows) => $rows->count() === 1 && $rows[0]->firstName === 'Ana')
            ->assertSee('6028871884223')->assertSee('Rejected');
        $this->get('/owner/payments?payment=cash&status=verified')->assertOk()->assertViewHas('payments', fn ($rows) => $rows->count() === 4);
        $this->get('/owner/payments?status=pending')->assertOk()->assertSee('Marco Reyes')->assertViewHas('payments', fn ($rows) => $rows->count() === 1);
        $this->get('/owner/payments?status=paid')->assertSessionHasErrors('status');
    }

    public function test_preview_is_local_only(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/payments')->assertRedirect('/login');
    }
}
