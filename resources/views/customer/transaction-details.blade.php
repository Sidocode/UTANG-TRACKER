@extends('layout.customer-dashboard')
@section('title', 'Transaction #'.$transaction->transactionId)
@section('heading', 'TRANSACTIONS #'.$transaction->transactionId)
@section('subtitle', '')
@section('screen', 'transaction-details')
@section('header-back')
    <a href="{{ route('customer.transaction') }}" data-customer-navigation class="customer-transaction-back" aria-label="Back to transaction history"><img src="{{ asset('assets/icons/customer-back.svg') }}" alt=""></a>
@endsection
@section('content')
    <section class="customer-transaction-details" aria-label="Transaction details">
        <dl class="customer-transaction-summary">
            <div><dt>Transaction Date</dt><dd>{{ \Carbon\Carbon::parse($transaction->date)->format('M j, Y') }}</dd></div>
            <div><dt>Customer Name</dt><dd>{{ $customer->firstName }} {{ $customer->lastName }}</dd></div>
            <div><dt>Total Amount</dt><dd>₱{{ number_format($transaction->totalAmount, 2) }}</dd></div>
        </dl>
        <h2 id="purchase-items-title">Purchase Items</h2>
        <div class="customer-purchase-items" tabindex="0" aria-labelledby="purchase-items-title">
            @forelse ($items as $item)
                <article class="customer-purchase-item">
                    <div><h3>{{ $item->itemName }}</h3><p>Qty: {{ $item->quantity }} · Price: ₱{{ number_format($item->price, 2) }}</p></div>
                    <span>₱{{ number_format($item->subtotal, 2) }}</span>
                </article>
            @empty
                <p>No purchase items recorded.</p>
            @endforelse
        </div>
        <div class="customer-purchase-total"><span>TOTAL</span><span>₱{{ number_format($transaction->totalAmount, 2) }}</span></div>
    </section>
@endsection
