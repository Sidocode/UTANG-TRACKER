<div class="transaction-details-body" data-edit-url="{{ route('owner.transactions.edit', $transaction->transactionId) }}">
    <header class="transaction-modal-heading"><div><h2 id="transaction-modal-title">TRANSACTION DETAILS</h2><p>View recorded customer transaction</p></div><button type="button" data-close-transaction aria-label="Close transaction details"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button></header>
    <dl class="transaction-modal-summary"><div><dt>Customer</dt><dd>{{ $transaction->firstName }} {{ $transaction->lastName }}</dd></div><div><dt>Date</dt><dd>{{ \Illuminate\Support\Carbon::parse($transaction->date)->format('M d, Y g:i A') }}</dd></div></dl>
    <div class="transaction-modal-items" tabindex="0" role="region" aria-label="Transaction items">
        <table><thead><tr><th>PRODUCT ITEM</th><th>QUANTITY</th><th>PRICE</th><th>SUBTOTAL</th></tr></thead><tbody>
        @forelse ($items as $item)
            <tr><td>{{ $item->itemName }}</td><td>{{ $item->quantity }}</td><td>₱{{ number_format($item->price, 2) }}</td><td>₱{{ number_format($item->subtotal, 2) }}</td></tr>
        @empty
            <tr><td colspan="4">No items recorded.</td></tr>
        @endforelse
        </tbody></table>
    </div>
    <section class="transaction-payment-summary" aria-label="Payment summary">
        <div class="transaction-payment-status"><x-status-badge :status="$status" /></div>
        <dl>
            <div><dt>Total amount</dt><dd>₱{{ number_format($transaction->totalAmount, 2) }}</dd></div>
            <div><dt>Amount paid</dt><dd>{{ $transaction->remaining === null ? '—' : '₱'.number_format(max(0, $transaction->debtAmount - $transaction->remaining), 2) }}</dd></div>
            <div class="transaction-remaining"><dt>Remaining balance</dt><dd>{{ $transaction->remaining === null ? '—' : '₱'.number_format($transaction->remaining, 2) }}</dd></div>
        </dl>
    </section>
    @if(!$canModify)<p class="transaction-editing-note">Editing unavailable: this transaction has recorded payments.</p>@endif
    <div class="transaction-details-actions transaction-modal-actions">
        @if($canModify)<button type="button" class="customer-action" data-transaction-preview="modify"><span>Modify</span></button>@endif
        <button type="button" class="customer-action customer-register" data-close-transaction><span>Close</span></button>
    </div>
    <p class="transaction-modal-feedback" role="status" data-transaction-feedback hidden></p>
    <template data-modify-confirmation>
        <header class="transaction-modal-heading">
            <h2 id="save-confirmation-title">Modify Transaction?</h2>
            <button type="button" data-cancel-save aria-label="Close confirmation"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button>
        </header>
        <p id="save-confirmation-description">Are you sure you want to modify this transaction?</p>
        <div class="transaction-modal-actions">
            <button type="button" class="customer-action customer-register" data-confirm-save><span>Yes</span></button>
            <button type="button" class="customer-action" data-cancel-save autofocus><span>No</span></button>
        </div>
    </template>
</div>

