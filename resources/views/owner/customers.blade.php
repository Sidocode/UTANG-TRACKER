@extends('layout.owner')

@section('title', 'Customers')
@section('heading', 'CUSTOMERS')
@section('subtitle', 'manage registered customers')

@section('content')
<section class="customers-screen" aria-label="Registered customers">
    <div class="customer-actions">
        <a class="customer-action" href="{{ route('owner.customers.pending') }}"><span>Pending registration ({{ $pendingRegistrationCount }})</span></a>
        <button class="customer-action customer-register" type="button" data-open-registration aria-haspopup="dialog"><span><img src="{{ asset('assets/icons/add.svg') }}" width="15" height="15" alt="">Register customer</span></button>
    </div>
    <form class="customer-filters" action="{{ route('owner.customers') }}" method="get" role="search">
        <div class="customer-search">
            <button type="submit" aria-label="Search customers"><img src="{{ asset('assets/icons/search.svg') }}" width="25" height="25" alt=""></button>
            <input type="search" name="search" aria-label="Search customers by name or mobile number" placeholder="Search" value="{{ $filters['search'] ?? '' }}" maxlength="100">
        </div>
        <div class="customer-filter-options">
            <x-owner.filter-dropdown name="debt" label="utang" :options="['paid' => 'paid', 'unpaid' => 'has unpaid debt']" :value="$filters['debt'] ?? ''" />
            <x-owner.filter-dropdown name="account" label="account" :options="['Active' => 'active', 'Deactivated' => 'deactivate']" :value="$filters['account'] ?? ''" />
        </div>
    </form>
    <p id="customer-search-status" class="customer-search-status" role="status" aria-live="polite"></p>
    <div class="table-scroll customers-table-scroll" tabindex="0" role="region" aria-label="Customers table; swipe to view more columns">
        <table class="customers-table" aria-label="Customers">
            <thead><tr><th scope="col">CUSTOMER</th><th scope="col">MOBILE NUMBER</th><th scope="col">CURRENT BALANCE</th><th scope="col">DEBT STATUS</th><th scope="col">STATUS</th></tr></thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr data-customer-row>
                        <td><a class="customer-detail-link" href="{{ route('owner.customers.show', $customer->userId) }}">{{ $customer->firstName }} {{ $customer->lastName }}</a></td>
                        <td>{{ $customer->mobileNumber }}</td>
                        <td><span class="money">₱{{ number_format($customer->balance, 2) }}</span></td>
                        <td><span class="{{ $customer->balance > 0 ? 'status-danger' : 'status-success' }}">{{ $customer->balance > 0 ? 'has unpaid debt' : ($customer->debt_count > 0 ? 'paid' : 'no debt') }}</span></td>
                        <td><span class="{{ $customer->status === 'Active' ? 'status-success' : 'status-danger' }}">{{ $customer->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No customers match your search.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@include('owner.register-customer')
@endsection
