<?php

namespace Tests\Feature;

use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class OwnerPaymentVerificationTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_only_pending_gcash_payments_open_verification(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        foreach (DB::table('payments')->get() as $payment) {
            $response = $this->get('/owner/payments/'.$payment->paymentId.'/verification');
            if ($payment->paymentStatus === 'Pending' && strtolower($payment->paymentMethod) === 'gcash') {
                $response->assertOk()->assertSee('Marco Reyes')->assertSee('602997188333')->assertSee('₱300.00')->assertSee('Payment Status')->assertSee('Verify payment')->assertSee('Reject payment');
                $this->get('/owner/payments')->assertSee('/owner/payments/'.$payment->paymentId.'/verification');
            } else {
                $response->assertNotFound();
                $this->get('/owner/payments')->assertDontSee('/owner/payments/'.$payment->paymentId.'/verification');
            }
        }
        $this->get('/owner/payments/999999/verification')->assertNotFound();
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/payments/1/verification')->assertRedirect('/login');
    }
}
