<?php

namespace Tests\Feature;

use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class OwnerTransactionEntryTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_entry_lists_active_customers_without_creating_records(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        DB::table('users')->where('firstName', 'Ana')->update(['status' => 'Deactivated']);
        $this->get('/owner/transactions/create')->assertOk()->assertSee('Sofia Rivera')->assertDontSee('Ana Santos')
            ->assertViewHas('customers', fn ($customers) => $customers->count() === 9);
        $this->assertDatabaseCount('transactions', 10);
    }

    public function test_entry_preview_is_local_only(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/transactions/create')->assertRedirect('/login');
    }
}
