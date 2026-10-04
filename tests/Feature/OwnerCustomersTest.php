<?php

namespace Tests\Feature;

use Database\Seeders\DemoCustomerSeeder;
use Database\Seeders\DemoRegistrationRequestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class OwnerCustomersTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_pending_count_tracks_requests_without_duplicating_seed_data(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $this->seed(DemoRegistrationRequestSeeder::class);
        $this->seed(DemoRegistrationRequestSeeder::class);
        $this->assertDatabaseCount('registration_requests', 15);
        $this->assertDatabaseCount('users', 11);
        $this->get('/owner/customers')->assertOk()->assertSee('Pending registration (15)');
        DB::table('registration_requests')->orderBy('requestId')->limit(1)->update(['status' => 'Approved']);
        $this->get('/owner/customers?search=Mia')->assertOk()->assertSee('Pending registration (14)');
        $this->seed(DemoRegistrationRequestSeeder::class);
        $this->assertSame(14, DB::table('registration_requests')->where('status', 'Pending')->count());
    }

    public function test_customers_show_their_outstanding_balances(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $this->get('/owner/customers')->assertOk()
            ->assertSee('Ana Santos')->assertSee('₱500.00')
            ->assertSee('Mia Torres')->assertSee('₱0.00')
            ->assertViewHas('customers', fn ($customers) => $customers->count() === 10);
    }

    public function test_search_and_debt_filters_can_be_combined(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $this->get('/owner/customers?search=Mia&debt=paid')->assertOk()
            ->assertSee('Mia Torres')->assertDontSee('Ana Santos');
        $this->get('/owner/customers?search=Mia&debt=unpaid')->assertOk()
            ->assertSee('No customers match your search.')->assertDontSee('Mia Torres');
        $this->get('/owner/customers?account=Inactive')->assertOk()
            ->assertViewHas('customers', fn ($customers) => $customers->isEmpty());
    }

    public function test_preview_requires_local_environment(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/customers')->assertRedirect('/login');
    }
}
