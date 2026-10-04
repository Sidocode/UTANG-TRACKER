@extends('layout.owner')
@section('title', 'Payments')
@section('heading', 'PAYMENTS')
@section('subtitle', 'Cash and Gcash payment records')
@section('content')
<section class="payment-screen" aria-label="Payment records">
    <form class="payment-toolbar" action="{{ route('owner.payments') }}" method="get" role="search">
        <div class="customer-search">
            <button type="submit" aria-label="Search payments"><img src="{{ asset('assets/icons/search.svg') }}" width="25" height="25" alt=""></button>
            <input type="search" name="search" aria-label="Search customer payments" placeholder="search customer" maxlength="100" value="{{ $filters['search'] ?? '' }}">
        </div>
        <x-owner.filter-dropdown name="payment" label="payment" :options="['cash' => 'Cash', 'gcash' => 'GCash']" :value="$filters['payment'] ?? ''" />
        <x-owner.filter-dropdown name="status" label="status" :options="['pending' => 'pending', 'verified' => 'verified', 'rejected' => 'rejected']" :value="$filters['status'] ?? ''" />
        <button class="customer-action customer-register record-payment" type="button" data-open-payment aria-haspopup="dialog"><span><img src="{{ asset('assets/icons/add.svg') }}" width="15" height="15" alt="">Record payment</span></button>
    </form>
    <p id="customer-search-status" class="customer-search-status" role="status" aria-live="polite"></p>
    <div class="table-scroll payment-records-scroll" tabindex="0" role="region" aria-label="Payment records; swipe to view more columns">
        <table class="payment-records-table" aria-label="Payment records">
            <thead><tr><th scope="col">DATE</th><th scope="col">CUSTOMER</th><th scope="col">AMOUNT</th><th scope="col">MODE OF PAYMENT</th><th scope="col">REFERENCE NO.</th><th scope="col">STATUS</th></tr></thead>
            <tbody>
                @forelse ($payments as $payment)
                    @php($canVerify = strtolower($payment->paymentMethod) === 'gcash' && $payment->paymentStatus === 'Pending')
                    <tr @if($canVerify) data-detail-row @endif>
                        <td>{{ \Illuminate\Support\Carbon::parse($payment->paymentDate)->format('M d, Y') }}</td>
                        <td>@if($canVerify)<a class="customer-detail-link" href="{{ route('owner.payments.verification', $payment->paymentId) }}">{{ $payment->firstName }} {{ $payment->lastName }}</a>@else{{ $payment->firstName }} {{ $payment->lastName }}@endif</td>
                        <td><span class="money">₱{{ number_format($payment->paymentAmount, 2) }}</span></td>
                        <td>{{ strtolower($payment->paymentMethod) === 'gcash' ? 'GCash' : $payment->paymentMethod }}</td>
                        <td>{{ $payment->referenceNumber ?? '—' }}</td>
                        <td><span class="payment-status-{{ strtolower($payment->paymentStatus) }}">{{ $payment->paymentStatus }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-state">No payments match your filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="payment-footer"><button class="customer-action customer-register" type="button" data-open-gcash-settings aria-haspopup="dialog"><span>GCash Settings</span></button></div>
</section>
@include('owner.record-payment')
@include('owner.gcash-settings')
@endsection


