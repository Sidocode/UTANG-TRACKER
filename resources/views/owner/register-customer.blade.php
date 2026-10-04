<dialog class="transaction-details-modal register-customer-modal" aria-labelledby="register-customer-title">
    <header class="transaction-modal-heading">
        <div><h2 id="register-customer-title">Register Customer</h2><p>create a customer account</p></div>
        <button type="button" data-close-registration aria-label="Close registration form"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button>
    </header>
    <form data-customer-registration action="{{ route('owner.customers.store') }}" method="post">
        @csrf
        <div class="registration-fields">
            <label for="register-first-name">First name<input id="register-first-name" name="firstName" autocomplete="given-name" maxlength="100" required></label>
            <label for="register-last-name">Last name<input id="register-last-name" name="lastName" autocomplete="family-name" maxlength="100" required></label>
            <label for="register-mobile">Mobile number<input id="register-mobile" name="mobileNumber" type="tel" inputmode="numeric" autocomplete="tel-national" pattern="09[0-9]{9}" maxlength="11" title="Enter an 11-digit mobile number starting with 09." required></label>
            <label for="register-password">Password<input id="register-password" name="password" type="password" autocomplete="new-password" minlength="8" required></label>
            <label for="register-confirm-password">Confirm password<input id="register-confirm-password" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required></label>
        </div>
        <div class="transaction-modal-actions">
            <button type="submit" class="customer-action customer-register"><span>Register Customer</span></button>
            <button type="button" class="customer-action" data-close-registration><span>Cancel</span></button>
        </div>
        <p class="transaction-modal-feedback" data-registration-form-feedback role="status" hidden></p>
    </form>
</dialog>
