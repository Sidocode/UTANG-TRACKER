@extends('layout.customer-dashboard')
@section('title', 'Home')
@section('content')
    <section class="customer-balance-summary" aria-labelledby="current-balance-title">
        <h2 id="current-balance-title">CURRENT BALANCE</h2>
        <p class="customer-balance-amount">₱{{ number_format($balance, 2) }}</p>
        <div class="customer-summary-cards">
            @foreach (['TOTAL DEBT' => $totalDebt, 'REMAINING BALANCE' => $balance, 'RECENT PAYMENT' => $recentPayment] as $label => $amount)
                <div><h3>{{ $label }}</h3><p>₱{{ number_format($amount, 2) }}</p></div>
            @endforeach
        </div>
    </section>
    <section class="customer-recent-activity" aria-labelledby="recent-activity-title">
        <h2 id="recent-activity-title">RECENT ACTIVITY</h2>
        <div class="customer-activity-list" tabindex="0" aria-label="Recent activity, scroll for more">
            @forelse ($activity as $item)
                <article class="customer-activity-card">
                    <div><p>₱{{ number_format($item['amount'], 2) }} - {{ $item['type'] }}</p><small>{{ $item['description'] }}</small></div>
                    <div class="customer-activity-meta"><p class="customer-status-{{ strtolower($item['status']) }}">{{ $item['status'] }}</p><small>{{ \Carbon\Carbon::parse($item['date'])->isToday() ? 'Today' : \Carbon\Carbon::parse($item['date'])->format('M j, Y') }}</small></div>
                </article>
            @empty
                <p>No recent activity.</p>
            @endforelse
        </div>
    </section>
@endsection
