@extends('layout.auth')
@section('title', 'Registration Status')
@section('customer-heading')
    <h1 class="customer-pending-title">{{ ($rejected ?? false) ? 'REJECTED' : ($registration->status ?? 'PENDING') }}</h1>
    <span class="customer-pending-subtitle">{{ ($rejected ?? false) ? 'Your registration has been rejected.' : (($registration->status ?? '') === 'Approved' ? 'Your account is approved. You can now log in.' : 'Your registration is waiting for admin approval.') }}</span>
@endsection
@section('content')
    <section class="customer-registration-form customer-pending" data-registration-pending aria-label="Registration details">
        <dl class="customer-pending-card">
            <div><dt>First Name</dt><dd>{{ $registration->firstName ?? '—' }}</dd></div>
            <div><dt>Last Name</dt><dd>{{ $registration->lastName ?? '—' }}</dd></div>
            <div><dt>Mobile No.</dt><dd>{{ $registration->mobileNumber ?? '—' }}</dd></div>
            <div><dt>Submitted Date</dt><dd>{{ isset($registration) ? \Carbon\Carbon::parse($registration->submittedAt)->format('M d, Y') : '—' }}</dd></div>
        </dl>
        @if ($rejected ?? false)
            <a class="customer-registration-submit customer-pending-back customer-rejected-back" href="{{ route('customer.register') }}">Register Again</a>
        @else
            <p class="customer-pending-waiting">{{ ($registration->status ?? '') === 'Approved' ? 'Registration Approved' : 'Waiting for Admin Approval' }}</p>
            <a class="customer-registration-submit customer-pending-back" href="{{ route('customer.login') }}">Back to login</a>
        @endif
        <p class="customer-registration-feedback" role="status">{{ isset($registration) ? 'Refresh this page to check the latest status.' : (($rejected ?? false) ? 'The request was removed. You may register again.' : 'Submit the registration form first.') }}</p>
    </section>
@endsection

