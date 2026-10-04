@extends('layout.owner')
@section('title', 'Profile')
@section('heading', 'Profile')
@section('subtitle', 'owner information')
@section('content')
<section class="owner-profile" aria-label="Owner information">
    <dl>
        <div><dt class="sr-only">First name</dt><dd>{{ $customer->firstName }}</dd></div>
        <div><dt class="sr-only">Last name</dt><dd>{{ $customer->lastName }}</dd></div>
        <div><dt class="sr-only">Mobile number</dt><dd>{{ $customer->mobileNumber }}</dd></div>
    </dl>
    <div class="owner-profile-actions">
        <button type="button" data-open-profile-edit>Edit Information</button>
        <button type="button" data-open-password-edit>Change Password</button>
    </div>
</section>
@include('shared.profile-dialogs')
@endsection
