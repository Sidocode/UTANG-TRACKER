<?php

namespace Tests\Feature;

use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class OwnerCustomerDetailTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_customer_tabs_are_scoped_and_summary_counts_only_verified_payments(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $id = DB::table('users')->where('firstName', 'Sofia')->value('userId');
        foreach (['overview', 'transactions', 'utang', 'payments'] as $tab) {
            $this->get('/owner/customers/'.$id.'?tab='.$tab)->assertOk()->assertSee('Sofia Rivera')
                ->assertDontSee('Ana Santos')->assertViewHas('balance', 600)->assertViewHas('totalPayments', 400)
                ->assertViewHas('transactions', fn ($rows) => $rows->count() === 1 && $rows[0]->customerId === $id
                    && $rows[0]->displayStatus === 'Partial' && (float) $rows[0]->amountPaid === 400.0 && (float) $rows[0]->remaining === 600.0)
                ->assertViewHas('payments', fn ($rows) => $rows->count() === 1 && $rows[0]->customerId === $id);
        }
        $this->get('/owner/customers/'.$id.'?tab=transactions')->assertOk()
            ->assertSeeInOrder(['TOTAL AMOUNT', 'AMOUNT PAID', 'REMAINING BALANCE', 'STATUS', '₱1,000.00', '₱400.00', '₱600.00', 'Partial']);
        $this->get('/owner/customers/'.$id.'?tab=payments')->assertSee('Verified')->assertSee('₱400.00');
        foreach (['Ana', 'Marco'] as $name) {
            $customer = DB::table('users')->where('firstName', $name)->first();
            $this->get('/owner/customers/'.$customer->userId.'?tab=payments')->assertOk()->assertViewHas('totalPayments', 0);
            $this->get('/owner/customers/'.$customer->userId.'?tab=transactions')->assertOk()
                ->assertViewHas('transactions', fn ($rows) => $rows[0]->displayStatus === 'Unpaid' && (float) $rows[0]->amountPaid === 0.0);
        }
        $this->get('/owner/customers?search=Sofia')->assertSee(route('owner.customers.show', $id));
        $this->get('/owner/customers/'.$id.'?tab=invalid')->assertSessionHasErrors('tab');
        $this->get('/owner/customers/99999')->assertNotFound();
    }

    public function test_empty_history_and_local_preview_guard(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $id = DB::table('users')->where('firstName', 'Nina')->value('userId');
        $this->get('/owner/customers/'.$id.'?tab=payments')->assertOk()->assertSee('No records for this customer yet.');
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/customers/'.$id)->assertRedirect('/login');
    }
}
