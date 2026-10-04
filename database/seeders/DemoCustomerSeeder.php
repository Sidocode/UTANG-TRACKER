<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoCustomerSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo customers may only be seeded locally or in tests.');
        }

        $customers = [
            ['Ana', 'Santos', '09000000001', 500, false],
            ['Marco', 'Reyes', '09000000002', 750, false],
            ['Liza', 'Cruz', '09000000003', 1200, false],
            ['Paolo', 'Garcia', '09000000004', 350, false],
            ['Nina', 'Mendoza', '09000000005', 900, false],
            ['Carlo', 'Ramos', '09000000006', 600, true],
            ['Mia', 'Torres', '09000000007', 1000, true],
            ['Sofia', 'Rivera', '09000000008', 1000, false, 400],
            ['Daniel', 'Lim', '09000000009', 1500, false, 500],
            ['Ella', 'Navarro', '09000000010', 800, false],
        ];
        $passwords = [
            '09000000001' => 'AnaDemo123!',
            '09000000002' => 'MarcoDemo123!',
            '09000000003' => 'LizaDemo123!',
            '09000000004' => 'PaoloDemo123!',
            '09000000005' => 'NinaDemo123!',
            '09000000006' => 'CarloDemo123!',
            '09000000007' => 'MiaDemo123!',
            '09000000008' => 'SofiaDemo123!',
            '09000000009' => 'DanielDemo123!',
            '09000000010' => 'EllaDemo123!',
        ];
        DB::transaction(function () use ($customers, $passwords): void {
            foreach ($customers as $customer) {
                [$first, $last, $mobile, $amount, $paid] = $customer;
                $amountPaid = $paid ? $amount : ($customer[5] ?? 0);
                $existing = DB::table('users')->where('mobileNumber', $mobile)->first();
                if ($existing) {
                    if ($existing->role === 'customer' && $existing->firstName === $first && $existing->lastName === $last
                        && Hash::check('DemoOnly123!', $existing->password)) {
                        DB::table('users')->where('userId', $existing->userId)->update([
                            'password' => Hash::make($passwords[$mobile]), 'updated_at' => now(),
                        ]);
                    }

                    continue;
                }

                $customerId = DB::table('users')->insertGetId([
                    'firstName' => $first, 'lastName' => $last,
                    'name' => $first.' '.$last, 'mobileNumber' => $mobile,
                    'role' => 'customer', 'status' => 'Active',
                    'password' => Hash::make($passwords[$mobile]), 'created_at' => now(), 'updated_at' => now(),
                ], 'userId');
                $transactionId = DB::table('transactions')->insertGetId([
                    'customerId' => $customerId, 'date' => now()->subDays(7),
                    'totalAmount' => $amount,
                ], 'transactionId');
                DB::table('transaction_items')->insert([
                    'transactionId' => $transactionId, 'itemName' => 'Sample grocery bundle',
                    'quantity' => 2, 'price' => $amount / 2, 'subtotal' => $amount,
                ]);
                $debtId = DB::table('debts')->insertGetId([
                    'customerId' => $customerId, 'transactionId' => $transactionId,
                    'debtAmount' => $amount, 'remaining' => $amount - $amountPaid,
                    'debtStatus' => $paid ? 'Paid' : ($amountPaid > 0 ? 'Partial' : 'Pending'),
                    'dueDate' => now()->addDays(7)->toDateString(),
                ], 'debtId');
                if ($amountPaid > 0) {
                    DB::table('payments')->insert([
                        'customerId' => $customerId, 'debtId' => $debtId,
                        'paymentAmount' => $amountPaid, 'paymentMethod' => 'Cash',
                        'paymentStatus' => 'Verified', 'paymentDate' => now()->subDay(),
                        'referenceNumber' => null, 'verifiedAt' => now()->subDay(),
                    ]);
                }
            }
            $debt = DB::table('debts')->join('users', 'debts.customerId', '=', 'users.userId')
                ->where('users.mobileNumber', '09000000001')->select('debts.debtId', 'debts.customerId')->first();
            if ($debt) {
                DB::table('payments')->where('debtId', $debt->debtId)
                    ->where('paymentStatus', 'Rejected')
                    ->where('referenceNumber', 'DEMO-REJECTED-001')
                    ->update(['referenceNumber' => '6028871884223']);
            }
            if ($debt && ! DB::table('payments')->where('referenceNumber', '6028871884223')->exists()) {
                DB::table('payments')->insert([
                    'customerId' => $debt->customerId, 'debtId' => $debt->debtId,
                    'paymentAmount' => 200, 'paymentMethod' => 'GCash',
                    'paymentStatus' => 'Rejected', 'paymentDate' => now(),
                    'referenceNumber' => '6028871884223', 'verifiedAt' => null,
                ]);
            }
            $pendingDebt = DB::table('debts')->join('users', 'debts.customerId', '=', 'users.userId')
                ->where('users.mobileNumber', '09000000002')->select('debts.debtId', 'debts.customerId')->first();
            if ($pendingDebt) {
                DB::table('payments')->where('debtId', $pendingDebt->debtId)
                    ->where('paymentStatus', 'Pending')
                    ->where('referenceNumber', '6028871884224')
                    ->update(['referenceNumber' => '602997188333']);
            }
            if ($pendingDebt && ! DB::table('payments')->where('referenceNumber', '602997188333')->exists()) {
                DB::table('payments')->insert([
                    'customerId' => $pendingDebt->customerId, 'debtId' => $pendingDebt->debtId,
                    'paymentAmount' => 300, 'paymentMethod' => 'GCash',
                    'paymentStatus' => 'Pending', 'paymentDate' => now(),
                    'referenceNumber' => '602997188333', 'verifiedAt' => null,
                ]);
            }
        });
    }
}
