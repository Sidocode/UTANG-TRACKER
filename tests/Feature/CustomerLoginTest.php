<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
    }

    public function test_customer_can_login_and_only_see_own_records(): void
    {
        $ana = User::where('mobileNumber', '09000000001')->firstOrFail();
        $sofia = User::where('mobileNumber', '09000000008')->firstOrFail();
        $this->post('/login', ['mobileNumber' => $ana->mobileNumber, 'password' => 'AnaDemo123!'])->assertRedirect('/customer/home');
        $this->assertAuthenticatedAs($ana);
        $this->get('/customer/home')->assertOk()->assertViewHas('customer', fn ($customer) => $customer->userId === $ana->userId);
        $transaction = DB::table('transactions')->where('customerId', $sofia->userId)->value('transactionId');
        $this->get('/customer/transactions/'.$transaction)->assertNotFound();
        $this->get('/owner/dashboard')->assertForbidden();
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/customer/home')->assertRedirect('/login');
    }

    public function test_invalid_and_inactive_accounts_cannot_login(): void
    {
        $this->from('/login')->post('/login', ['mobileNumber' => '09000000001', 'password' => 'wrong'])->assertSessionHasErrors('mobileNumber');
        $this->assertGuest();
        User::where('mobileNumber', '09000000001')->update(['status' => 'Inactive']);
        $this->post('/login', ['mobileNumber' => '09000000001', 'password' => 'AnaDemo123!'])->assertSessionHasErrors('mobileNumber');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['mobileNumber' => '09000000002', 'password' => 'wrong']);
        }
        $this->post('/login', ['mobileNumber' => '09000000002', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_owner_routes_require_login(): void
    {
        $this->get('/owner/dashboard')->assertRedirect('/login');
        $this->delete('/owner/customers/pending/1')->assertRedirect('/login');
    }

    public function test_owner_login_logout_and_role_protection(): void
    {
        $owner = User::where('mobileNumber', '09000000001')->firstOrFail();
        $owner->role = 'owner';
        $owner->save();
        $this->post('/login', ['mobileNumber' => $owner->mobileNumber, 'password' => 'AnaDemo123!'])->assertRedirect('/owner/dashboard');
        $this->app->detectEnvironment(fn () => 'production');
        $this->get('/owner/dashboard')->assertOk();
        $this->get('/customer/home')->assertForbidden();
        $this->app->detectEnvironment(fn () => 'testing');
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/owner/dashboard')->assertRedirect('/login');
    }
}
