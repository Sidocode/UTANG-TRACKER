@extends('layout.customer-dashboard')
@section('title', 'Balance')
@section('heading', 'BALANCE')
@section('subtitle', 'your current standing')
@section('content')
    <section class="customer-balance-summary" aria-labelledby="current-balance-title">
        <h2 id="current-balance-title">CURRENT BALANCE</h2>
        <p class="customer-balance-amount">₱{{ number_format($balance, 2) }}</p>
        <div class="customer-summary-cards customer-balance-cards">
            @foreach (['TOTAL DEBT' => $totalDebt, 'REMAINING BALANCE' => $balance] as $label => $amount)
                <div><h3>{{ $label }}</h3><p>₱{{ number_format($amount, 2) }}</p></div>
            @endforeach
        </div>
    </section>
    <section class="customer-recent-activity" aria-labelledby="balance-activity-title">
        <h2 id="balance-activity-title">BALANCE ACTIVITY</h2>
        <div class="customer-activity-list customer-balance-activity" tabindex="0" aria-label="Balance activity, scroll for more">
            @forelse ($activity as $item)
                <article class="customer-activity-card">
                    <div><p>{{ $item['amount'] < 0 ? '-' : '' }}₱{{ number_format(abs($item['amount']), 2) }}</p><small>{{ $item['description'] }}</small></div>
                    <time datetime="{{ \Carbon\Carbon::parse($item['date'])->toDateString() }}">{{ \Carbon\Carbon::parse($item['date'])->format('M j, Y') }}</time>
                </article>
            @empty
                <p>No balance activity yet.</p>
            @endforelse
        </div>
    </section>
@endsection
