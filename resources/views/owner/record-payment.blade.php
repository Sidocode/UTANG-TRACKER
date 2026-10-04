<dialog class="transaction-details-modal record-payment-modal" aria-labelledby="record-payment-title" style="--payment-dropdown-arrow: url('{{ asset('assets/icons/arrow-dropDown.svg') }}')">
    <header class="transaction-modal-heading"><div><h2 id="record-payment-title">Payment Confirmation</h2><p>record a customer’s payment</p></div><button type="button" data-close-payment aria-label="Close payment form"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button></header>
    <form data-payment-form action="{{ route('owner.payments.store') }}" method="post">
        @csrf
        <div class="registration-fields">
            <label>Customer<select name="customer" required><option value="">select customer</option>@foreach ($paymentDebts->unique('customerId') as $debt)<option value="{{ $debt->customerId }}">{{ $debt->firstName }} {{ $debt->lastName }}</option>@endforeach</select></label>
            <label>Debt / Transaction<select name="debt" required disabled><option value="">select transaction</option></select></label>
            <label>Payment Amount<input name="amount" type="number" min="0.01" step="0.01" inputmode="decimal" placeholder="₱0.00" required></label>
        </div>
        <div class="transaction-modal-actions"><button type="submit" class="customer-action customer-register"><span>confirm payment</span></button><button type="button" class="customer-action" data-close-payment><span>Cancel</span></button></div>
        <p role="status" class="transaction-modal-feedback" data-payment-feedback hidden></p>
    </form>
    <template data-payment-debts>@foreach ($paymentDebts as $debt)<option value="{{ $debt->debtId }}" data-customer="{{ $debt->customerId }}" data-remaining="{{ $debt->remaining }}">TXN-{{ str_pad($debt->transactionId, 3, '0', STR_PAD_LEFT) }} — ₱{{ number_format($debt->remaining, 2) }} remaining</option>@endforeach</template>
</dialog>

<dialog class="transaction-details-modal transaction-save-confirmation payment-confirmation-dialog" aria-labelledby="payment-confirm-title" aria-describedby="payment-confirm-description">
    <header class="transaction-modal-heading">
        <h2 id="payment-confirm-title">Confirm Cash Payment?</h2>
        <button type="button" data-dismiss-payment-confirmation aria-label="Close payment confirmation"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button>
    </header>
    <p id="payment-confirm-description" data-payment-confirm-description></p>
    <div class="transaction-modal-actions">
        <button type="button" class="customer-action customer-register" data-confirm-payment><span>confirm payment</span></button>
        <button type="button" class="customer-action" data-dismiss-payment-confirmation><span>Cancel</span></button>
    </div>
</dialog>
