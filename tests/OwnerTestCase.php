<?php

namespace Tests;

use App\Models\User;

abstract class OwnerTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'owner', 'status' => 'Active', 'mobileNumber' => '09999999999']));
    }
}
