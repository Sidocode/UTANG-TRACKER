@extends('layout.owner')
@section('title', 'Utang Details')
@section('heading', 'UTANG')
@section('subtitle', 'track outstanding customer debts')
@section('content')
<section class="utang-details-screen" aria-label="Debt details">
    <a class="back-to-customers" href="{{ route('owner.utang') }}"><img src="{{ asset('assets/icons/arrow-back.svg') }}" width="20" height="19.25" alt=""><span>back</span></a>
    <div class="utang-detail-content">
        <section class="utang-detail-summary" aria-label="Debt summary">
            <dl>
                <div><dt>Customer name</dt><dd>{{ $debt->firstName }} {{ $debt->lastName }}</dd></div>
                <div><dt>Transaction</dt><dd>TXN-{{ str_pad($debt->transactionId, 3, '0', STR_PAD_LEFT) }}</dd></div>
                <div><dt>Original debt amount</dt><dd class="money">₱{{ number_format($debt->debtAmount, 2) }}</dd></div>
                <div><dt>Payment made</dt><dd class="money">₱{{ number_format($amountPaid, 2) }}</dd></div>
                <div><dt>Remaining balance</dt><dd class="money">₱{{ number_format($debt->remaining, 2) }}</dd></div>
                <div><dt>Debt status</dt><dd><x-status-badge :status="$status" /></dd></div>
            </dl>
            <button class="customer-action customer-register" type="button" @disabled($status === 'Paid') data-preview="Mark as fully paid" data-message="This is a UI preview. Recording the final payment and updating the balance will be connected when the backend is implemented."><span>Mark as fully paid</span></button>
        </section>
        <h2 class="utang-history-heading">PAYMENTS HISTORY</h2>
        <div class="table-scroll utang-detail-history" tabindex="0" role="region" aria-label="Payments for this debt; swipe to see all columns">
            <table aria-label="Debt payment history">
                <thead><tr><th scope="col">DATE</th><th scope="col">AMOUNT</th><th scope="col">METHOD</th><th scope="col">STATUS</th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr><td>{{ \Carbon\Carbon::parse($payment->paymentDate)->format('M d, Y') }}</td><td class="money">₱{{ number_format($payment->paymentAmount, 2) }}</td><td>{{ $payment->paymentMethod }}</td><td><x-status-badge :status="$payment->paymentStatus" /></td></tr>
                    @empty
                        <tr><td colspan="4" class="empty-state">No payments recorded for this debt.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection
