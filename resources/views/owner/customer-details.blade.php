@extends('layout.owner')
@section('title', 'Customer Details')
@section('heading', 'CUSTOMER DETAILS')
@section('subtitle', $customer->firstName.' '.$customer->lastName)
@section('content')
<section class="customer-details" aria-label="Customer details">
    <a class="customer-back" href="{{ route('owner.customers') }}"><img src="{{ asset('assets/icons/arrow-back.svg') }}" width="20" height="19.25" alt="">back to customers</a>
    <div class="customer-summary">
        <section class="customer-info-card">
            <h2>CUSTOMER INFORMATION</h2>
            <dl>
                <div><dt>Name</dt><dd>{{ $customer->firstName }} {{ $customer->lastName }}</dd></div>
                <div><dt>Mobile Number</dt><dd>{{ $customer->mobileNumber }}</dd></div>
                <div><dt>Account Status</dt><dd class="{{ $customer->status === 'Active' ? 'status-success' : 'status-danger' }}">{{ strtoupper($customer->status) }}</dd></div>
            </dl>
            <div class="customer-account-actions">
                @foreach (['Deactivate' => $customer->status !== 'Active', 'Activate' => $customer->status === 'Active'] as $action => $disabled)
                    <button class="customer-action" type="button" @disabled($disabled) data-account-confirm="{{ strtolower($action) }}" data-customer-name="{{ $customer->firstName }} {{ $customer->lastName }}" data-account-url="{{ route('owner.customers.'.strtolower($action), $customer->userId) }}"><span>{{ $action }} account</span></button>
                @endforeach
            </div>
        </section>
        <section class="customer-finance-card">
            <h2>FINANCIAL SUMMARY</h2>
            <dl>
                <div><dt>Current Balance</dt><dd class="money">₱{{ number_format($balance, 2) }}</dd></div>
                <div><dt>Total Outstanding Utang</dt><dd class="money">₱{{ number_format($balance, 2) }}</dd></div>
                <div><dt>Total Payments</dt><dd class="money">₱{{ number_format($totalPayments, 2) }}</dd></div>
            </dl>
        </section>
    </div>
    <nav class="customer-detail-tabs" aria-label="Customer history">
        @foreach (['overview' => 'OVERVIEW', 'transactions' => 'TRANSACTIONS', 'utang' => 'UTANG HISTORY', 'payments' => 'PAYMENT HISTORY'] as $key => $label)
            <a href="{{ route('owner.customers.show', ['customer' => $customer->userId, 'tab' => $key]) }}" @if($tab === $key) aria-current="page" @endif>{{ $label }}</a>
        @endforeach
    </nav>
    @php
        $headings = match ($tab) {
            'transactions' => ['TRANSACTION', 'DATE', 'TOTAL AMOUNT', 'AMOUNT PAID', 'REMAINING BALANCE', 'STATUS'],
            'utang' => ['TRANSACTION', 'ORIGINAL AMOUNT', 'AMOUNT PAID', 'REMAINING BALANCE', 'STATUS'],
            'payments' => ['DATE', 'AMOUNT', 'PAYMENT METHOD', 'REFERENCE NO.', 'STATUS'],
            default => ['DATE', 'TYPE', 'AMOUNT', 'STATUS'],
        };
        $records = match ($tab) { 'transactions' => $transactions, 'utang' => $debts, 'payments' => $payments, default => $activity };
    @endphp
    <div class="table-scroll customer-history-scroll" tabindex="0" role="region" aria-label="Customer history table; swipe to view more columns">
        <table class="customer-history-table customer-history-{{ $tab }}" aria-label="{{ $tab }} history">
            <thead><tr>@foreach ($headings as $heading)<th scope="col">{{ $heading }}</th>@endforeach</tr></thead>
            <tbody>
                @forelse ($records as $record)
                    <tr>
                        @if($tab === 'utang')
                            <td>TXN-{{ str_pad($record->transactionId, 3, '0', STR_PAD_LEFT) }}</td>
                            <td class="money">₱{{ number_format($record->debtAmount, 2) }}</td>
                            <td class="money">₱{{ number_format($record->amountPaid, 2) }}</td>
                            <td class="money">₱{{ number_format($record->remaining, 2) }}</td>
                            <td><x-status-badge :status="$record->displayStatus" /></td>
                        @elseif($tab === 'payments')
                            <td>{{ \Carbon\Carbon::parse($record->paymentDate)->format('M d, Y') }}</td>
                            <td class="money">₱{{ number_format($record->paymentAmount, 2) }}</td>
                            <td>{{ $record->paymentMethod }}</td>
                            <td>{{ $record->referenceNumber ?: '—' }}</td>
                            <td><x-status-badge :status="$record->paymentStatus" /></td>
                        @elseif($tab === 'transactions')
                            <td>TXN-{{ str_pad($record->transactionId, 3, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                            <td class="money">₱{{ number_format($record->totalAmount, 2) }}</td>
                            <td class="money">₱{{ number_format($record->amountPaid, 2) }}</td>
                            <td class="money">₱{{ number_format($record->remaining, 2) }}</td>
                            <td><x-status-badge :status="$record->displayStatus" /></td>
                        @else
                            <td>{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                            <td>{{ $record->type }}</td>
                            <td class="money">₱{{ number_format($record->amount, 2) }}</td>
                            <td><x-status-badge :status="$record->status" /></td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ count($headings) }}" class="empty-state">No records for this customer yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
