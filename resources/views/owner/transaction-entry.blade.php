@extends('layout.owner')
@section('title', 'Enter Transaction')
@section('heading', 'TRANSACTIONS')
@section('subtitle', 'Record a new customer transaction')
@section('content')
<section class="transaction-entry-screen" aria-label="Enter transaction">
    <a class="back-to-customers" href="{{ route('owner.transactions') }}"><img src="{{ asset('assets/icons/arrow-back.svg') }}" width="20" height="19.25" alt=""><span>back</span></a>
    <form id="transaction-entry-form" action="{{ $transaction ? route('owner.transactions.update', $transaction->transactionId) : route('owner.transactions.store') }}" data-save-method="{{ $transaction ? 'PATCH' : 'POST' }}">
        @csrf
        <div class="transaction-entry-card">
            <label class="entry-customer-label" for="entry-customer">CUSTOMER</label>
            <div class="entry-customer-select">
                <select id="entry-customer" required aria-label="Select customer">
                    <option value="">Select customer</option>
                    @foreach($customers as $customer)<option value="{{ $customer->userId }}" @selected($transaction && $transaction->customerId === $customer->userId)>{{ $customer->firstName }} {{ $customer->lastName }}</option>@endforeach
                </select>
                <img src="{{ asset('assets/icons/arrow-dropDown.svg') }}" width="24" height="24" alt="">
            </div>
            <h2>TRANSACTION ITEMS</h2>
            <div class="entry-items-scroll">
                <div class="entry-item-head" aria-hidden="true"><span>PRODUCT / ITEM</span><span>QUANTITY</span><span>PRICE</span><span>SUB TOTAL</span><span></span></div>
                <div id="entry-items"></div>
            </div>
            <button type="button" class="customer-action customer-register" id="entry-add"><span><img src="{{ asset('assets/icons/add.svg') }}" width="15" height="15" alt="">Add new item</span></button>
        </div>
        <div class="entry-footer">
            <div class="entry-actions"><a class="customer-action" href="{{ route('owner.transactions') }}"><span>Cancel</span></a><button class="customer-action customer-register" type="submit"><span>Save Transaction</span></button></div>
            <div class="entry-total"><span>TOTAL TRANSACTION AMOUNT</span><output id="entry-total">₱0.00</output></div>
        </div>
        <p id="entry-feedback" role="status" aria-live="polite"></p>
    </form>
    <script type="application/json" id="entry-existing-items">@json($items)</script>
    <dialog class="transaction-details-modal transaction-save-confirmation" id="entry-confirmation" aria-labelledby="entry-confirm-title">
        <header class="transaction-modal-heading"><h2 id="entry-confirm-title">Confirm Transaction?</h2><button type="button" data-entry-cancel aria-label="Close confirmation"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button></header>
        <p data-entry-confirm-description></p>
        <div class="transaction-modal-actions"><button type="button" class="customer-action customer-register" data-entry-save><span>Confirm Transaction</span></button><button type="button" class="customer-action" data-entry-cancel autofocus><span>Cancel</span></button></div>
        <p role="status" data-entry-save-feedback hidden></p>
    </dialog>
    <template id="entry-item-template">
        <div class="entry-item-row">
            <input type="text" data-item-name maxlength="150" aria-label="Product or item" autocomplete="off">
            <div class="entry-quantity"><input type="number" data-item-quantity value="0" min="0" max="9999" step="1" aria-label="Quantity" inputmode="numeric"><div class="entry-stepper"><img src="{{ asset('assets/icons/item-increase-decrease.svg') }}" width="16" height="16" alt=""><button type="button" data-step="1" aria-label="Increase quantity"></button><button type="button" data-step="-1" aria-label="Decrease quantity"></button></div></div>
            <input type="number" data-item-price min="0" max="999999.99" step="0.01" placeholder="₱0.00" aria-label="Unit price" inputmode="decimal">
            <output data-item-subtotal aria-label="Item subtotal">₱0.00</output>
            <button type="button" class="entry-remove" aria-label="Remove item"><img src="{{ asset('assets/icons/item-close.svg') }}" width="30" height="30" alt=""></button>
        </div>
    </template>
</section>
@endsection
