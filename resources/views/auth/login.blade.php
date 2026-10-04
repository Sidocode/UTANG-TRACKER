@extends('layout.auth')
@section('title', 'Login')
@section('content')
    <form class="customer-registration-form" id="customer-login-form" method="POST" action="{{ route('customer.login.store') }}" novalidate>
        @csrf
        <h1>WELCOME BACK</h1>
        <div class="customer-registration-fields">
            <div class="customer-registration-field">
                <label for="mobileNumber">MOBILE NUMBER</label>
                <input id="mobileNumber" name="mobileNumber" value="{{ old('mobileNumber') }}" type="tel" inputmode="numeric" autocomplete="username" maxlength="11" pattern="09[0-9]{9}" required aria-describedby="mobileNumber-error">
                <span class="customer-registration-error" id="mobileNumber-error" hidden></span>
            </div>
            <div class="customer-registration-field">
                <label for="password">PASSWORD</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required aria-describedby="password-error">
                <span class="customer-registration-error" id="password-error" hidden></span>
            </div>
        </div>
        <button class="customer-registration-submit" type="submit">LOGIN</button>
        <div class="customer-login-links">
            <button type="button" data-forgot-password>Forgot Password?</button>
            <p>Don’t have an account? <a href="{{ route('customer.register') }}">Register Account</a></p>
        </div>
        <p class="customer-registration-feedback" role="status" @unless($errors->any()) hidden @endunless>{{ $errors->first() }}</p>
    </form>
@endsection

