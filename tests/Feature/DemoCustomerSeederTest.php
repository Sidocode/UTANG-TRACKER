<?php

namespace Tests\Feature;

use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoCustomerSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_samples_have_consistent_balances_and_can_be_seeded_twice(): void
    {
        $this->seed(DemoCustomerSeeder::class);
        $this->seed(DemoCustomerSeeder::class);
        $this->assertDatabaseCount('users', 10);
        $this->assertDatabaseCount('transactions', 10);
        $this->assertDatabaseCount('transaction_items', 10);
        $this->assertDatabaseCount('debts', 10);
        $this->assertDatabaseCount('payments', 6);
        $this->assertSame(8, DB::table('debts')->where('remaining', '>', 0)->count());
        $this->assertSame(2, DB::table('debts')->where('debtStatus', 'Partial')->count());
        $this->assertSame(1, DB::table('payments')->where('paymentMethod', 'GCash')->where('paymentStatus', 'Pending')->count());
        foreach (DB::table('users')->get() as $user) {
            $this->assertNotSame('DemoOnly123!', $user->password);
            $this->assertTrue(Hash::check($user->firstName.'Demo123!', $user->password));
            $this->assertFalse(Hash::check('DemoOnly123!', $user->password));
        }
        $this->assertSame(2, DB::table('debts')->where('remaining', 0)->where('debtStatus', 'Paid')->count());
        foreach (DB::table('debts')->get() as $debt) {
            $paid = DB::table('payments')->where('debtId', $debt->debtId)->where('paymentStatus', 'Verified')->sum('paymentAmount');
            $this->assertEquals((float) $debt->debtAmount - (float) $paid, (float) $debt->remaining);
            $total = DB::table('transaction_items')->where('transactionId', $debt->transactionId)->sum('subtotal');
            $this->assertEquals((float) $debt->debtAmount, (float) $total);
        }
    }
}
