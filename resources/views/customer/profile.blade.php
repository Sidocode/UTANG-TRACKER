@extends('layout.customer-dashboard')
@section('title', 'Profile')
@section('heading', 'PROFILE')
@section('subtitle', 'personal information')
@section('screen', 'profile')
@section('content')
    <section class="customer-profile" aria-label="Personal information">
        <dl class="customer-profile-information">
            <div><dt class="sr-only">First name</dt><dd>{{ $customer->firstName }}</dd></div>
            <div><dt class="sr-only">Last name</dt><dd>{{ $customer->lastName }}</dd></div>
            <div><dt class="sr-only">Mobile number</dt><dd>{{ $customer->mobileNumber }}</dd></div>
        </dl>
        <div class="customer-profile-actions">
            <button type="button" class="customer-profile-edit" data-open-profile-edit>Edit Information</button>
            <button type="button" class="customer-profile-password" data-open-password-edit>Change Password</button>
        </div>
        <button type="button" class="customer-profile-logout" data-open-customer-logout>Log Out</button>
    </section>
    @include('shared.profile-dialogs')
    <dialog class="customer-logout-dialog" aria-labelledby="customer-logout-title" aria-describedby="customer-logout-description">
        <header class="customer-logout-heading">
            <h2 id="customer-logout-title">Log Out?</h2>
            <button type="button" data-cancel-customer-logout aria-label="Close logout confirmation"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button>
        </header>
        <p id="customer-logout-description">Are you sure you want to log out of your account?</p>
        <div class="customer-logout-actions">
            <form method="POST" action="{{ route('logout') }}" style="margin: 0">
                @csrf
                <button type="submit">Log out</button>
            </form>
            <button type="button" data-cancel-customer-logout autofocus>Cancel</button>
        </div>
    </dialog>
@endsection

