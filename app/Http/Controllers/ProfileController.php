<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('owner.profile', ['customer' => $request->user()]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'firstName' => ['required', 'string', 'max:100'],
            'lastName' => ['required', 'string', 'max:100'],
            'mobileNumber' => ['required', 'regex:/^09[0-9]{9}$/', Rule::unique('users', 'mobileNumber')->ignore($user->userId, 'userId'), Rule::unique('registration_requests', 'mobileNumber')->where('status', 'Pending')],
        ]);
        $user->fill($data);
        $user->name = $data['firstName'].' '.$data['lastName'];
        $user->save();

        return response()->json(['message' => 'Your information has been updated.']);
    }

    public function password(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed', 'different:current_password'],
        ]);
        $user = $request->user();
        $user->password = Hash::make($data['password']);
        $user->save();
        if (config('session.driver') === 'database') {
            DB::table('sessions')->where('user_id', $user->userId)->where('id', '!=', $request->session()->getId())->delete();
        }
        $request->session()->regenerate();

        return response()->json(['message' => 'Your password has been changed.']);
    }
}
