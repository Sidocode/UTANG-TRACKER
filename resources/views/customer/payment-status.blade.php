@extends('layout.customer-dashboard')
@section('title', 'Payment Status')
@section('heading', 'PAYMENT STATUS')
@section('subtitle', '')
@section('screen', 'payment-status')
@section('content')
    <section class="customer-payment-status" aria-labelledby="payment-status-title">
        <h2 id="payment-status-title">{{ strtoupper($submittedPayment->paymentStatus) }}</h2>
        <p class="customer-payment-status-description">{{ match($submittedPayment->paymentStatus) { 'Verified' => 'Your payment has been verified and applied to your balance.', 'Rejected' => 'Your payment was rejected. Please check with the owner.', default => 'Your payment is waiting for owner verification.' } }}</p>
        <dl class="customer-payment-status-details">
            <div><dt>Amount</dt><dd>₱{{ number_format($submittedPayment->paymentAmount, 2) }}</dd></div>
            <div><dt>Method</dt><dd>GCash</dd></div>
            <div><dt>Reference No.</dt><dd>{{ $submittedPayment->referenceNumber }}</dd></div>
            <div><dt>Date / Time</dt><dd>{{ \Carbon\Carbon::parse($submittedPayment->paymentDate)->format('M d, Y g:i A') }}</dd></div>
        </dl>
        <a class="customer-payment-status-home" href="{{ route('customer.home') }}" data-customer-navigation>Back to Home</a>
    </section>
@endsection
