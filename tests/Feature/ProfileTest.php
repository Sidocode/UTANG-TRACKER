<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_profile_updates_and_password_validation(): void
    {
        $this->withoutVite();
        $this->seed(DemoCustomerSeeder::class);
        $owner = User::first();
        $owner->role = 'owner';
        $owner->save();
        $this->actingAs($owner)->get('/owner/profile')->assertOk()->assertSee('EDIT INFORMATION')->assertSee($owner->mobileNumber);
        $this->patchJson('/owner/profile', ['firstName' => 'Test', 'lastName' => 'Owner', 'mobileNumber' => '09999999999'])->assertOk();
        $this->assertSame('Test', $owner->fresh()->firstName);
        $this->patchJson('/owner/profile', ['firstName' => 'Test', 'lastName' => 'Owner', 'mobileNumber' => '09000000002'])->assertUnprocessable();
        $this->patchJson('/owner/password', ['current_password' => 'wrong', 'password' => 'NewTest123!', 'password_confirmation' => 'NewTest123!'])->assertUnprocessable();
        $this->patchJson('/owner/password', ['current_password' => 'AnaDemo123!', 'password' => 'NewTest123!', 'password_confirmation' => 'NewTest123!'])->assertOk();
        $this->assertTrue(Hash::check('NewTest123!', $owner->fresh()->password));
    }

    public function test_profile_is_role_protected(): void
    {
        $this->get('/owner/profile')->assertRedirect('/login');
        $this->seed(DemoCustomerSeeder::class);
        $this->actingAs(User::first())->patchJson('/owner/profile', [])->assertForbidden();
    }
}
