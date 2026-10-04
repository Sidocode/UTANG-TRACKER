<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoRegistrationRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo requests may only be seeded locally or in tests.');
        }

        $names = [
            ['Ella', 'Flores'], ['Luis', 'Navarro'], ['Sofia', 'Lim'],
            ['Miguel', 'Diaz'], ['Isabel', 'Castro'], ['Rafael', 'Tan'],
            ['Chloe', 'Bautista'], ['Gabriel', 'Aquino'], ['Camille', 'Dizon'],
            ['Daniel', 'Valdez'], ['Julia', 'Soriano'], ['Adrian', 'Lopez'],
            ['Bea', 'Villanueva'], ['Enzo', 'Mercado'], ['Hannah', 'Rivera'],
        ];
        $password = Hash::make('DemoOnly123!');
        DB::transaction(function () use ($names, $password): void {
            foreach ($names as $index => [$firstName, $lastName]) {
                $mobileNumber = '090000001'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
                if (DB::table('registration_requests')->where('mobileNumber', $mobileNumber)->exists()) {
                    continue;
                }
                DB::table('registration_requests')->insert([
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'mobileNumber' => $mobileNumber,
                    'password' => $password,
                    'status' => 'Pending',
                    'submittedAt' => now()->subMinutes(15 - $index),
                    'reviewedAt' => null,
                ]);
            }
        });
    }
}
