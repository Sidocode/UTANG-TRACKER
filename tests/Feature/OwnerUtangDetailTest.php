<?php

namespace Tests\Feature;

use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class OwnerUtangDetailTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_debt_details_scope_payments_and_show_partial_balance(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $id = DB::table('users')->where('firstName', 'Sofia')->value('userId');
        $debt = DB::table('debts')->where('customerId', $id)->first();
        $this->get('/owner/utang/'.$debt->debtId)->assertOk()->assertSee('Sofia Rivera')->assertSee('Partial')
            ->assertSee('₱1,000.00')->assertSee('₱400.00')->assertSee('₱600.00')->assertDontSee('Ana Santos')
            ->assertViewHas('payments', fn ($rows) => $rows->count() === 1 && $rows[0]->debtId === $debt->debtId);
        $this->get('/owner/utang/999999')->assertNotFound();
    }

    public function test_unverified_payments_do_not_count_and_empty_history_is_shown(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        foreach (['Ana', 'Marco', 'Nina'] as $name) {
            $id = DB::table('users')->where('firstName', $name)->value('userId');
            $debt = DB::table('debts')->where('customerId', $id)->first();
            $response = $this->get('/owner/utang/'.$debt->debtId)->assertOk()->assertViewHas('amountPaid', 0);
            if ($name === 'Nina') {
                $response->assertSee('No payments recorded for this debt.');
            }
        }
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/utang/1')->assertRedirect('/login');
    }
}
