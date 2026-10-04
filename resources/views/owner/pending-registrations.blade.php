@extends('layout.owner')

@section('title', 'Pending registration')
@section('heading', 'PENDING REGISTRATION')
@section('subtitle', 'review new customers sign-up')

@section('content')
<section class="pending-registration-screen" aria-label="Pending registration requests">
    @if (session('registration_status'))
        <p role="status">{{ session('registration_status') }}</p>
    @endif
    <a class="back-to-customers" href="{{ route('owner.customers') }}">
        <img src="{{ asset('assets/icons/arrow-back.svg') }}" width="20" height="19.25" alt="">
        <span>back to customers</span>
    </a>
    <div class="table-scroll pending-table-scroll" tabindex="0" role="region" aria-label="Pending requests; scroll to view more customers">
        <table class="pending-table" aria-label="Pending registrations">
            <thead><tr><th scope="col">CUSTOMER</th><th scope="col">MOBILE NUMBER</th><th scope="col">SUBMITTED DATE</th><th scope="col">STATUS</th></tr></thead>
            <tbody>
                @forelse ($requests as $request)
                    <tr data-registration-review="registration-review-{{ $request->requestId }}" data-applicant-name="{{ $request->firstName }} {{ $request->lastName }}" data-approve-url="{{ route('owner.customers.pending.approve', $request->requestId) }}" data-reject-url="{{ route('owner.customers.pending.reject', $request->requestId) }}">
                        <td>{{ $request->firstName }} {{ $request->lastName }}</td>
                        <td>{{ $request->mobileNumber }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($request->submittedAt)->format('M d, Y') }}</td>
                        <td><span class="registration-pending-status">{{ $request->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-state">No pending registration requests.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
<dialog class="transaction-details-modal registration-review-modal" aria-labelledby="registration-review-title">
    <header class="transaction-modal-heading">
        <h2 id="registration-review-title">Customer Registration Review</h2>
        <button type="button" data-close-review aria-label="Close registration review"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button>
    </header>
    <div data-registration-content></div>
    <div class="transaction-modal-actions">
        <button type="button" class="customer-action customer-register" data-registration-action="Approval"><span>Approve</span></button>
        <button type="button" class="customer-action" data-registration-action="Rejection"><span>Reject</span></button>
    </div>
    <p class="transaction-modal-feedback" data-registration-feedback role="status" hidden></p>
</dialog>
<dialog class="transaction-details-modal transaction-save-confirmation registration-approval-modal" aria-labelledby="registration-approval-title" aria-describedby="registration-approval-description">
    <header class="transaction-modal-heading">
        <h2 id="registration-approval-title">Approve Customer?</h2>
        <button type="button" data-cancel-approval aria-label="Close approval confirmation"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button>
    </header>
    <p id="registration-approval-description"></p>
    <div class="transaction-modal-actions">
        <button type="button" class="customer-action customer-register" data-confirm-approval><span>Approve</span></button>
        <button type="button" class="customer-action" data-cancel-approval autofocus><span>Cancel</span></button>
    </div>
</dialog>
<dialog class="transaction-details-modal transaction-save-confirmation registration-rejection-modal" aria-labelledby="registration-rejection-title" aria-describedby="registration-rejection-description">
    <header class="transaction-modal-heading">
        <h2 id="registration-rejection-title">Reject Customer?</h2>
        <button type="button" data-cancel-rejection aria-label="Close rejection confirmation"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button>
    </header>
    <p id="registration-rejection-description"></p>
    <form method="post" data-rejection-form class="transaction-modal-actions">
        @csrf
        @method('DELETE')
        <button type="submit" class="customer-action customer-register"><span>Reject</span></button>
        <button type="button" class="customer-action" data-cancel-rejection autofocus><span>Cancel</span></button>
    </form>
</dialog>
@foreach ($requests as $request)
    <template id="registration-review-{{ $request->requestId }}">
        <dl class="transaction-modal-summary">
            <div><dt>First name</dt><dd>{{ $request->firstName }}</dd></div>
            <div><dt>Last name</dt><dd>{{ $request->lastName }}</dd></div>
            <div><dt>Mobile number</dt><dd>{{ $request->mobileNumber }}</dd></div>
            <div><dt>Submitted at</dt><dd>{{ \Illuminate\Support\Carbon::parse($request->submittedAt)->format('M d, Y, g:i A') }}</dd></div>
            <div><dt>Registration status</dt><dd>{{ $request->status }}</dd></div>
        </dl>
    </template>
@endforeach
@endsection
