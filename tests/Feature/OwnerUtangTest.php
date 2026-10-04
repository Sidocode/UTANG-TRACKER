<?php

namespace Tests\Feature;

use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\OwnerTestCase;

class OwnerUtangTest extends OwnerTestCase
{
    use RefreshDatabase;

    public function test_debts_and_combined_filters(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $this->seed(DemoCustomerSeeder::class);
        $this->assertDatabaseCount('users', 11);
        $this->get('/owner/utang')->assertOk()->assertViewHas('debts', fn ($rows) => $rows->count() === 10);
        $id = DB::table('users')->where('mobileNumber', '09000000008')->value('userId');
        $this->get('/owner/utang?search=Sofia&status=partial&customer='.$id)->assertOk()
            ->assertViewHas('debts', fn ($rows) => $rows->count() === 1 && (float) $rows[0]->amountPaid === 400.0 && (float) $rows[0]->remaining === 600.0)
            ->assertSee('Sofia Rivera')->assertSee('₱600.00');
        $this->get('/owner/utang?status=unpaid')->assertOk()->assertViewHas('debts', fn ($rows) => $rows->count() === 6);
        $this->get('/owner/utang?status=paid')->assertOk()->assertViewHas('debts', fn ($rows) => $rows->count() === 2);
        $this->get('/owner/utang?search=missing')->assertOk()->assertSee('No debts match your filters.');
        $this->get('/owner/utang?status=invalid')->assertSessionHasErrors('status');
    }

    public function test_preview_is_local_only(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        Auth::logout();
        $this->get('/owner/utang')->assertRedirect('/login');
    }
}
