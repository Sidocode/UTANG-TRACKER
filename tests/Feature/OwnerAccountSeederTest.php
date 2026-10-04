<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\OwnerAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OwnerAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_is_hashed_and_existing_account_is_preserved(): void
    {
        config(['owner.first_name' => 'Test', 'owner.last_name' => 'Owner', 'owner.mobile_number' => '09999999999', 'owner.password' => 'OwnerTest123!']);
        $this->seed(OwnerAccountSeeder::class);
        $owner = User::first();
        $this->assertSame('owner', $owner->role);
        $this->assertSame('Active', $owner->status);
        $this->assertTrue(Hash::check('OwnerTest123!', $owner->password));
        config(['owner.password' => 'ChangedTest123!']);
        $this->seed(OwnerAccountSeeder::class);
        $this->assertDatabaseCount('users', 1);
        $this->assertTrue(Hash::check('OwnerTest123!', $owner->fresh()->password));
    }

    public function test_missing_credentials_create_no_account(): void
    {
        config(['owner.first_name' => '', 'owner.last_name' => '', 'owner.mobile_number' => '', 'owner.password' => '']);
        try {
            $this->seed(OwnerAccountSeeder::class);
            $this->fail('Missing credentials must be rejected.');
        } catch (\RuntimeException $exception) {
            $this->assertDatabaseCount('users', 0);
        }
    }
}
