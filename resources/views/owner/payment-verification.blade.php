@extends('layout.owner')
@section('title', 'GCash Payment Verification')
@section('heading', 'GCASH PAYMENT VERIFICATION')
@section('subtitle', 'confirm submitted GCash payment')
@section('content')
<section class="utang-details-screen" aria-label="GCash payment verification">
    <a class="back-to-customers" href="{{ route('owner.payments') }}"><img src="{{ asset('assets/icons/arrow-back.svg') }}" width="20" height="19.25" alt=""><span>back</span></a>
    <div class="payment-verification-content">
        <section class="utang-detail-summary payment-verification-summary" aria-label="Submitted payment details">
            <dl>
                <div><dt>Customer Name</dt><dd>{{ $payment->firstName }} {{ $payment->lastName }}</dd></div>
                <div><dt>Payment Amount</dt><dd class="money">₱{{ number_format($payment->paymentAmount, 2) }}</dd></div>
                <div><dt>Payment Date/Time</dt><dd>{{ \Illuminate\Support\Carbon::parse($payment->paymentDate)->format('M d, Y, g:i A') }}</dd></div>
                <div><dt>GCash Reference No.</dt><dd>{{ $payment->referenceNumber }}</dd></div>
                <div><dt>Payment Status</dt><dd><span class="payment-status-pending">{{ strtoupper($payment->paymentStatus) }}</span></dd></div>
            </dl>
        </section>
        <p class="payment-verification-note">Confirm that the amount, date/time, reference number, and proof of payment match before proceeding.</p>
        <div class="customer-actions">
            <button class="customer-action verification-reject" type="button" data-payment-review="reject"><span>Reject payment</span></button>
            <button class="customer-action customer-register" type="button" data-payment-review="verify"><span>Verify payment</span></button>
        </div>
        <p class="transaction-modal-feedback" role="status" data-payment-review-feedback hidden></p>
    </div>
</section>
<dialog class="transaction-details-modal transaction-save-confirmation payment-confirmation-dialog" data-payment-review-dialog data-review-url="{{ route('owner.payments.review', $payment->paymentId) }}" aria-labelledby="payment-review-title" aria-describedby="payment-review-description">
    <header class="transaction-modal-heading">
        <h2 id="payment-review-title">Verify GCash Payment?</h2>
        <button type="button" data-dismiss-payment-review aria-label="Close payment confirmation"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button>
    </header>
    <p id="payment-review-description"></p>
    <div class="transaction-modal-actions">
        <button type="button" class="customer-action customer-register" data-confirm-payment-review><span>Verify payment</span></button>
        <button type="button" class="customer-action" data-dismiss-payment-review><span>Cancel</span></button>
    </div>
    <template data-review-verify>Are you sure you received ₱{{ number_format($payment->paymentAmount, 2) }} via GCash from {{ $payment->firstName }} {{ $payment->lastName }}? This payment will be verified and applied to their outstanding balance.</template>
    <template data-review-reject>Are you sure you want to reject the ₱{{ number_format($payment->paymentAmount, 2) }} GCash payment from {{ $payment->firstName }} {{ $payment->lastName }}? This payment will not be applied to their outstanding balance.</template>
</dialog>
@endsection
