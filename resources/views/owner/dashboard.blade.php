@extends('layout.owner')
@section('content')
<section class="dashboard-stats" aria-label="Business overview">
    <div class="stat"><h2>TOTAL CUSTOMERS</h2><p class="stat-value">{{ $stats['customers'] }}</p><p class="stat-caption">{{ $stats['active'] }} active</p></div>
    <div class="stat"><h2>TOTAL OUTSTANDING DEBT</h2><p class="stat-value money">₱{{ number_format($stats['outstanding']) }}</p><p class="stat-caption">across {{ $stats['debtors'] }} customers</p></div>
    <div class="stat"><h2>TOTAL PAYMENTS RECEIVED</h2><p class="stat-value money">₱{{ number_format($stats['received']) }}</p><p class="stat-caption">all time</p></div>
    <div class="stat"><h2>UNPAID DEBTS</h2><p class="stat-value">{{ $stats['unpaid'] }}</p><p class="stat-caption">requires follow-up</p></div>
    <div class="stat"><h2>GCASH PENDING</h2><p class="stat-value">{{ $stats['pending'] }}</p><p class="stat-caption">awaiting review</p></div>
</section>
<div class="dashboard-sections" tabindex="0" aria-label="Recent business activity">
    <section class="activity-section" aria-labelledby="transactions-title">
        <h2 id="transactions-title">RECENT TRANSACTIONS</h2>
        <div class="table-scroll" tabindex="0" role="region" aria-label="Recent transactions">
            <table><thead><tr><th>DATE</th><th>CUSTOMER</th><th>TOTAL AMOUNT</th><th>STATUS</th></tr></thead><tbody>
            @forelse ($transactions as $transaction)
                @php($status = $transaction->remaining === null ? '—' : ($transaction->remaining <= 0 ? 'Paid' : ($transaction->remaining < $transaction->debtAmount ? 'Partial' : 'Unpaid')))
                <tr data-transaction-details="{{ route('owner.transactions.details', $transaction->transactionId) }}"><td>{{ \Illuminate\Support\Carbon::parse($transaction->date)->format('M d, Y') }}</td><td>{{ $transaction->firstName }} {{ $transaction->lastName }}</td><td>₱{{ number_format($transaction->totalAmount, 2) }}</td><td><x-status-badge :status="$status" /></td></tr>
            @empty
                <tr><td colspan="4" class="empty-state">No transactions yet.</td></tr>
            @endforelse
            </tbody></table>
        </div>
    </section>
    <section class="activity-section" aria-labelledby="payments-title">
        <h2 id="payments-title">RECENT PAYMENTS</h2>
        <div class="table-scroll" tabindex="0" role="region" aria-label="Recent payments">
            <table class="payments-table"><thead><tr><th>DATE</th><th>CUSTOMER</th><th>AMOUNT</th><th>METHOD</th><th>STATUS</th></tr></thead><tbody>
            @forelse ($payments as $payment)
                <tr><td>{{ \Illuminate\Support\Carbon::parse($payment->paymentDate)->format('M d, Y') }}</td><td>{{ $payment->firstName }} {{ $payment->lastName }}</td><td>₱{{ number_format($payment->paymentAmount, 2) }}</td><td>{{ $payment->paymentMethod }}</td><td><x-status-badge :status="$payment->paymentStatus" /></td></tr>
            @empty
                <tr><td colspan="5" class="empty-state">No payments yet.</td></tr>
            @endforelse
            </tbody></table>
        </div>
    </section>
    <section class="activity-section" aria-labelledby="gcash-title">
        <h2 id="gcash-title">RECENT GCASH PAYMENTS REQUIRING VERIFICATION</h2>
        <div class="table-scroll" tabindex="0" role="region" aria-label="GCash payments requiring verification">
            <table><thead><tr><th>CUSTOMER</th><th>AMOUNT</th><th>DATE TIME</th><th>REFERENCE NO.</th></tr></thead><tbody>
            @forelse ($pendingPayments as $payment)
                <tr data-detail-row><td><a class="customer-detail-link" href="{{ route('owner.payments.verification', $payment->paymentId) }}">{{ $payment->firstName }} {{ $payment->lastName }}</a></td><td>₱{{ number_format($payment->paymentAmount, 2) }}</td><td>{{ \Illuminate\Support\Carbon::parse($payment->paymentDate)->format('M d, Y g:i A') }}</td><td>{{ $payment->referenceNumber ?? '—' }}</td></tr>
            @empty
                <tr><td colspan="4" class="empty-state">No GCash payments awaiting verification.</td></tr>
            @endforelse
            </tbody></table>
        </div>
    </section>
</div>
@endsection

