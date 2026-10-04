<?php

namespace Tests\Feature;

use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class OwnerTransactionDetailsTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_details_show_selected_items_and_status(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $partial = DB::table('debts')->where('debtStatus', 'Partial')->first();
        $this->get('/owner/transactions/'.$partial->transactionId.'/details')
            ->assertSee('Amount paid')->assertSee('Remaining balance')
            ->assertSee(number_format($partial->remaining, 2))
            ->assertDontSee('Save Transaction')->assertDontSee('data-transaction-preview="modify"', false);
        foreach (DB::table('transactions')->get() as $transaction) {
            $debt = DB::table('debts')->where('transactionId', $transaction->transactionId)->first();
            $status = $debt->remaining <= 0 ? 'Paid' : ($debt->remaining < $debt->debtAmount ? 'Partial' : 'Unpaid');
            $this->get('/owner/transactions/'.$transaction->transactionId.'/details')->assertOk()->assertSee($status)->assertSee(number_format($transaction->totalAmount, 2))->assertViewHas('items', fn ($items) => $items->isNotEmpty() && $items->every(fn ($item) => $item->transactionId === $transaction->transactionId));
        }
        $this->get('/owner/dashboard')->assertSee('data-transaction-details', false);
        $this->get('/owner/transactions?search=Sofia')->assertSee('data-transaction-details', false)->assertSee('Sofia');
        $this->get('/owner/transactions/999999/details')->assertNotFound();
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/transactions/1/details')->assertRedirect('/login');
    }
}
