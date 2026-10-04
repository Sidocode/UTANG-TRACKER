@extends('layout.auth')
@section('title', 'Create Account')
@section('content')
        <form class="customer-registration-form" id="customer-registration-form" action="{{ route('customer.register.store') }}" method="post" novalidate>
            @csrf
            <h1>CREATE ACCOUNT</h1>
            <div class="customer-registration-fields">
                @foreach ([['firstName', 'FIRST NAME', 'text', 'given-name'], ['lastName', 'LAST NAME', 'text', 'family-name'], ['mobileNumber', 'MOBILE NUMBER', 'tel', 'tel'], ['password', 'PASSWORD', 'password', 'new-password'], ['password_confirmation', 'CONFIRM PASSWORD', 'password', 'new-password']] as [$name, $label, $type, $autocomplete])
                    <div class="customer-registration-field">
                        <label for="{{ $name }}">{{ $label }}</label>
                        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" required
                            aria-describedby="{{ $name }}-error"
                            @if ($type === 'password') minlength="8" @endif
                            @if ($type === 'tel') inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" @endif
                            @if ($type === 'text') maxlength="100" @endif>
                        <span class="customer-registration-error" id="{{ $name }}-error" hidden></span>
                    </div>
                @endforeach
            </div>
            <button class="customer-registration-submit" type="submit">Submit registration</button>
            <p class="customer-registration-feedback" role="status" hidden></p>
            <noscript>Enable JavaScript to submit your registration.</noscript>
        </form>
        <dialog class="registration-confirmation" aria-labelledby="registration-confirmation-title" aria-describedby="registration-confirmation-description">
            <h2 id="registration-confirmation-title">Submit registration?</h2>
            <p id="registration-confirmation-description">Are you sure you want to submit your registration? Please make sure your information is correct before continuing.</p>
            <div class="registration-confirmation-actions">
                <button type="button" data-confirm-registration>submit</button>
                <button type="button" data-cancel-registration data-cancel-confirmation autofocus>Cancel</button>
            </div>
        </dialog>
@endsection


