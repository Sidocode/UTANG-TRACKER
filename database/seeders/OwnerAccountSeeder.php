<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class OwnerAccountSeeder extends Seeder
{
    public function run(): void
    {
        $mobile = config('owner.mobile_number');
        $existing = $mobile ? User::where('mobileNumber', $mobile)->first() : null;
        if ($existing) {
            if ($existing->role !== 'owner') {
                throw new RuntimeException('This mobile number belongs to a customer. No account was changed.');
            }
            $this->command?->info('Owner account already exists. No changes made.');

            return;
        }

        $data = config('owner');
        $validator = Validator::make($data, [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'mobile_number' => ['required', 'regex:/^09[0-9]{9}$/', 'unique:users,mobileNumber', 'unique:registration_requests,mobileNumber'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        if ($validator->fails()) {
            throw new RuntimeException('Configure valid OWNER_FIRST_NAME, OWNER_LAST_NAME, OWNER_MOBILE_NUMBER and OWNER_PASSWORD in .env.');
        }

        $owner = new User;
        $owner->forceFill([
            'name' => $data['first_name'].' '.$data['last_name'],
            'firstName' => $data['first_name'],
            'lastName' => $data['last_name'],
            'mobileNumber' => $mobile,
            'password' => $data['password'],
            'role' => 'owner',
            'status' => 'Active',
        ])->save();
        $this->command?->info('Owner account created.');
    }
}
