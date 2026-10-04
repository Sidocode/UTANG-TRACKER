@extends('layout.owner')
@section('title', 'Transactions')
@section('heading', 'TRANSACTIONS')
@section('subtitle', 'all recorded customer transactions')
@section('content')
<section class="transactions-screen" aria-label="Customer transactions">
    <form class="transaction-toolbar" action="{{ route('owner.transactions') }}" method="get" role="search">
        <div class="customer-search">
            <button type="submit" aria-label="Search transactions"><img src="{{ asset('assets/icons/search.svg') }}" width="25" height="25" alt=""></button>
            <input type="search" name="search" aria-label="Search customer transactions" placeholder="search customer" maxlength="100" value="{{ $filters['search'] ?? '' }}">
        </div>
        <x-owner.date-filter :value="$filters['date'] ?? ''" />
        <x-owner.filter-dropdown name="status" label="status" :options="['paid' => 'paid', 'unpaid' => 'unpaid', 'partial' => 'partial']" :value="$filters['status'] ?? ''" />
        <a class="customer-action customer-register enter-transaction" href="{{ route('owner.transactions.create') }}"><span><img src="{{ asset('assets/icons/add.svg') }}" width="15" height="15" alt="">Enter transaction</span></a>
    </form>
    <p id="customer-search-status" class="customer-search-status" role="status" aria-live="polite"></p>
    <div class="table-scroll transactions-table-scroll" tabindex="0" role="region" aria-label="Transactions; swipe to view more columns">
        <table class="transactions-table" aria-label="Transactions">
            <thead><tr><th scope="col">TRANSACTION</th><th scope="col">DATE</th><th scope="col">CUSTOMER</th><th scope="col">TOTAL AMOUNT</th><th scope="col">STATUS</th></tr></thead>
            <tbody>
                @forelse ($transactions as $transaction)
                    <tr data-transaction-details="{{ route('owner.transactions.details', $transaction->transactionId) }}">
                        <td>TXN-{{ str_pad($transaction->transactionId, 3, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($transaction->date)->format('M d, Y') }}</td>
                        <td>{{ $transaction->firstName }} {{ $transaction->lastName }}</td>
                        <td><span class="money">₱{{ number_format($transaction->totalAmount, 2) }}</span></td>
                        <td><span class="transaction-status-{{ strtolower($transaction->paymentStatus) }}">{{ $transaction->paymentStatus }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No transactions match your filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
