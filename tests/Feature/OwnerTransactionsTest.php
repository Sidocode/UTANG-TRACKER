<?php

namespace Tests\Feature;

use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class OwnerTransactionsTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_transactions_and_combined_filters(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $this->get('/owner/transactions')->assertOk()->assertSee('Ana Santos')->assertSee('₱500.00')
            ->assertViewHas('transactions', fn ($rows) => $rows->count() === 10);
        $date = now()->subDays(7)->toDateString();
        $this->get('/owner/transactions?search=Mia&status=paid&date='.$date)->assertOk()
            ->assertSee('Mia Torres')->assertDontSee('Ana Santos');
        $this->get('/owner/transactions?status=unpaid')->assertOk()
            ->assertViewHas('transactions', fn ($rows) => $rows->count() === 6);
        DB::table('debts')->where('remaining', 500)->update(['remaining' => 250]);
        $this->get('/owner/transactions?status=partial')->assertOk()->assertSee('Ana Santos')
            ->assertViewHas('transactions', fn ($rows) => $rows->count() === 3);
        $this->get('/owner/transactions?date=2000-01-01')->assertOk()->assertSee('No transactions match your filters.');
    }

    public function test_preview_is_local_only(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/transactions')->assertRedirect('/login');
    }
}
