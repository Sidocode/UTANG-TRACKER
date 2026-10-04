<dialog class="transaction-details-modal transaction-save-confirmation account-confirmation" aria-labelledby="account-confirm-title" aria-describedby="account-confirm-description">
    <header class="transaction-modal-heading">
        <h2 id="account-confirm-title"></h2>
        <button type="button" data-account-dismiss aria-label="Close confirmation"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button>
    </header>
    <p id="account-confirm-description"></p>
    <div class="transaction-modal-actions">
        <button type="button" class="customer-action customer-register" data-account-proceed><span></span></button>
        <button type="button" class="customer-action" data-account-dismiss><span>Cancel</span></button>
    </div>
    <p class="transaction-modal-feedback" data-account-feedback role="status" hidden></p>
</dialog>
