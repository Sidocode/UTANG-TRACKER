@extends('layout.customer-dashboard')
@section('title', 'Payment')
@section('heading', 'PAYMENT')
@section('subtitle', '')
@section('screen', 'payment')
@section('content')
    <form class="customer-payment-form" data-customer-payment-form action="{{ route('customer.payment.store') }}" method="post" aria-describedby="payment-unavailable">
        @csrf
        <label class="customer-payment-amount">AMOUNT TO PAY
            <input name="amount" type="number" min="0.01" step="0.01" inputmode="decimal" placeholder="₱0.00" aria-label="Amount to pay" data-payment-amount required>
        </label>
        <section class="customer-payment-recipient" aria-labelledby="pay-to-label">
            <h2 id="pay-to-label">PAY TO</h2>
            <div class="customer-payment-recipient-panel">
                @if($paymentSettings)
                    <img class="customer-payment-qr-empty" src="{{ route('customer.payment.qr', $paymentSettings->paymentSettingId) }}" alt="GCash QR code" style="object-fit:contain">
                @else
                    <div class="customer-payment-qr-empty">QR code<br>Not set</div>
                @endif
                <p>Account name: {{ $paymentSettings?->accountName ?? 'Not set' }}</p>
                <p>GCash number: {{ $paymentSettings?->mobileNumber ?? 'Not set' }}</p>
            </div>
            <p id="payment-unavailable">{{ $paymentSettings ? 'Use these details to pay through GCash, then submit your reference number.' : 'The owner has not set up GCash payment details yet.' }}</p>
        </section>
        <label class="customer-payment-debt">Debt / Transaction
            <select name="debt" aria-label="Debt or transaction" data-payment-debt required @disabled($outstandingDebts->isEmpty())>
                <option value="">{{ $outstandingDebts->isEmpty() ? 'No outstanding transactions' : 'select transaction' }}</option>
                @foreach ($outstandingDebts as $debt)
                    <option value="{{ $debt->debtId }}" data-remaining="{{ $debt->remaining }}">Transaction #{{ $debt->transactionId }} — ₱{{ number_format($debt->remaining, 2) }} remaining</option>
                @endforeach
            </select>
            <small data-payment-remaining aria-live="polite"></small>
        </label>
        <label class="customer-payment-reference">GCash Reference No.
            <input name="referenceNumber" type="text" inputmode="numeric" pattern="[0-9]{6,30}" maxlength="30" aria-label="GCash Reference No." autocomplete="off" required>
        </label>
        <button class="customer-payment-submit" type="submit" @disabled($outstandingDebts->isEmpty() || !$paymentSettings) aria-describedby="payment-preview-notice">Submit Payment</button>
        <p id="payment-preview-notice" role="status">Payments require owner verification before reducing your balance.</p>
    </form>
    <dialog class="registration-confirmation" data-payment-confirmation data-status-url="{{ route('customer.payment.status') }}" aria-labelledby="payment-confirmation-title" aria-describedby="payment-confirmation-message payment-confirmation-preview">
        <h2 id="payment-confirmation-title">SUBMIT PAYMENT?</h2>
        <p id="payment-confirmation-message">Are you sure you want to submit your payment of ₱<span data-confirm-payment-amount>0.00</span>? Please make sure your reference number is correct before submitting.</p>
        <p id="payment-confirmation-preview">Your payment will be submitted to the owner for verification.</p>
        <div class="registration-confirmation-actions">
            <button type="button" data-confirm-payment-preview>Submit payment</button>
            <button type="button" data-cancel-payment-confirmation data-cancel-confirmation autofocus>Cancel</button>
        </div>
    </dialog>
@endsection
