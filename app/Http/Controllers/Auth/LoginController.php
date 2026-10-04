<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'mobileNumber' => ['required', 'regex:/^09[0-9]{9}$/'],
            'password' => ['required', 'string', 'max:255'],
        ]);
        $key = 'login:'.hash('sha256', $request->ip().'|'.$credentials['mobileNumber']);
        abort_if(RateLimiter::tooManyAttempts($key, 5), 429, 'Too many login attempts. Please try again in one minute.');
        if (! Auth::attempt([...$credentials, 'status' => 'Active'])) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['mobileNumber' => 'The mobile number or password is incorrect, or the account is inactive.']);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->route($request->user()->role === 'owner' ? 'owner.dashboard' : 'customer.home');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
