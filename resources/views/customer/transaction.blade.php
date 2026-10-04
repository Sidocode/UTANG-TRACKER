@extends('layout.customer-dashboard')
@section('title', 'Transaction History')
@section('heading', 'TRANSACTION HISTORY')
@section('subtitle', '')
@section('screen', 'transactions')
@section('content')
    <section class="customer-transaction-list" aria-label="Transaction history, scroll for more" tabindex="0">
        @forelse ($transactions as $transaction)
            <article class="customer-transaction-card">
                <h2>TRANSACTION #{{ $transaction->transactionId }}</h2>
                <time datetime="{{ \Carbon\Carbon::parse($transaction->date)->toDateString() }}">{{ \Carbon\Carbon::parse($transaction->date)->format('F j, Y') }}</time>
                <div class="customer-transaction-total">
                    <div><span>Total</span><p>₱{{ number_format($transaction->totalAmount, 2) }}</p></div>
                    <a href="{{ route('customer.transaction.show', $transaction->transactionId) }}" data-customer-navigation aria-label="View details for transaction {{ $transaction->transactionId }}">View Details</a>
                </div>
            </article>
        @empty
            <p class="customer-transaction-empty">No transactions yet.</p>
        @endforelse
    </section>
@endsection
