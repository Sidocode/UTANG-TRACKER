@extends('layout.owner')
@section('title', 'Utang')
@section('heading', 'UTANG')
@section('subtitle', 'track outstanding customer debts')
@section('content')
<section class="utang-screen" aria-label="Customer debts">
    <form class="utang-toolbar" action="{{ route('owner.utang') }}" method="get" role="search">
        <div class="customer-search">
            <button type="submit" aria-label="Search debts"><img src="{{ asset('assets/icons/search.svg') }}" width="25" height="25" alt=""></button>
            <input type="search" name="search" aria-label="Search customer debts" placeholder="search customer" maxlength="100" value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="utang-filters">
            <div class="utang-customer-filter"><x-owner.filter-dropdown name="customer" label="customer" :options="$customers" :value="(string) ($filters['customer'] ?? '')" /></div>
            <x-owner.filter-dropdown name="status" label="status" :options="['paid' => 'paid', 'unpaid' => 'unpaid', 'partial' => 'partial']" :value="$filters['status'] ?? ''" />
        </div>
    </form>
    <p id="customer-search-status" class="customer-search-status" role="status" aria-live="polite"></p>
    <div class="table-scroll utang-table-scroll" tabindex="0" role="region" aria-label="Customer debts; swipe to view more columns">
        <table class="utang-table" aria-label="Customer debts">
            <thead><tr><th scope="col">CUSTOMER</th><th scope="col">TRANSACTION</th><th scope="col">ORIGINAL AMOUNT</th><th scope="col">AMOUNT PAID</th><th scope="col">REMAINING BALANCE</th><th scope="col">UTANG STATUS</th></tr></thead>
            <tbody>
                @forelse ($debts as $debt)
                    <tr data-detail-row>
                        <td>{{ $debt->firstName }} {{ $debt->lastName }}</td>
                        <td><a class="customer-detail-link" href="{{ route('owner.utang.show', $debt->debtId) }}">TXN-{{ str_pad($debt->transactionId, 3, '0', STR_PAD_LEFT) }}</a></td>
                        <td><span class="money">₱{{ number_format($debt->debtAmount, 2) }}</span></td>
                        <td><span class="money">₱{{ number_format($debt->amountPaid, 2) }}</span></td>
                        <td><span class="money">₱{{ number_format($debt->remaining, 2) }}</span></td>
                        <td><span class="transaction-status-{{ strtolower($debt->displayStatus) }}">{{ $debt->displayStatus }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No debts match your filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
