@extends('layout.owner')
@section('title', 'Audit Log')
@section('heading', 'AUDIT LOG')
@section('subtitle', 'payment related activity history')
@section('content')
<section class="transactions-screen audit-screen" aria-label="Payment activity history">
    <form class="transaction-toolbar" action="{{ route('owner.audit-log') }}" method="get" role="search">
        <div class="customer-search">
            <button type="submit" aria-label="Search activity"><img src="{{ asset('assets/icons/search.svg') }}" width="25" height="25" alt=""></button>
            <input type="search" name="search" aria-label="Search user or action" placeholder="search" maxlength="100" value="{{ $filters['search'] ?? '' }}">
        </div>
        <x-owner.date-filter class="audit-date" :value="$filters['date'] ?? ''" placeholder="date" label="Filter by activity date" />
        <x-owner.filter-dropdown name="payment" label="payment" :options="['cash' => 'Cash', 'gcash' => 'GCash']" :value="$filters['payment'] ?? ''" />
    </form>
    <p id="customer-search-status" class="customer-search-status" role="status" aria-live="polite"></p>
    <div class="table-scroll transactions-table-scroll" tabindex="0" role="region" aria-label="Audit log; swipe to view more columns">
        <table class="audit-table" aria-label="Audit log">
            <thead><tr><th scope="col">DATE / TIME</th><th scope="col">USER</th><th scope="col">ACTION</th><th scope="col">MODE OF PAYMENT</th><th scope="col">AMOUNT</th></tr></thead>
            <tbody>
                @forelse ($activities as $activity)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($activity->paymentDate)->format('M d, Y g:i A') }}</td>
                        <td>{{ $activity->firstName }} {{ $activity->lastName }}</td>
                        <td>{{ $activity->activity }}</td>
                        <td>{{ $activity->paymentMethod ?? '—' }}</td>
                        <td><span class="money">{{ $activity->paymentAmount === null ? '—' : '₱'.number_format($activity->paymentAmount, 2) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No activity matches your filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
