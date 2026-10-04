    <dialog class="customer-profile-edit-dialog" aria-labelledby="profile-edit-title">
        <h2 id="profile-edit-title">EDIT INFORMATION</h2>
        <form data-profile-edit-form data-url="{{ route(auth()->user()->role.'.profile.update') }}">
            @csrf
            <label>First Name<input data-profile-field="firstName" value="{{ $customer->firstName }}" autocomplete="given-name" maxlength="100" required></label>
            <label>Last Name<input data-profile-field="lastName" value="{{ $customer->lastName }}" autocomplete="family-name" maxlength="100" required></label>
            <label>Mobile Number<input data-profile-field="mobileNumber" value="{{ $customer->mobileNumber }}" type="tel" inputmode="numeric" autocomplete="tel-national" pattern="09[0-9]{9}" maxlength="11" title="Enter an 11-digit mobile number starting with 09" required></label>
            <div class="registration-confirmation-actions">
                <button type="submit">Save</button>
                <button type="button" data-cancel-profile-edit data-cancel-confirmation>Cancel</button>
            </div>
            <p class="customer-profile-edit-feedback" role="status" hidden></p>
        </form>
    </dialog>
    <dialog class="registration-confirmation" data-profile-save-confirmation aria-labelledby="profile-save-title" aria-describedby="profile-save-description">
        <h2 id="profile-save-title">SAVE CHANGES?</h2>
        <p id="profile-save-description">Are you sure you want to save your changes? Please make sure your information is correct before continuing.</p>
        <div class="registration-confirmation-actions">
            <button type="button" data-confirm-profile-save>Save changes</button>
            <button type="button" data-cancel-profile-save data-cancel-confirmation autofocus>Cancel</button>
        </div>
    </dialog>
    <dialog class="customer-profile-edit-dialog customer-password-dialog" aria-labelledby="password-edit-title">
        <h2 id="password-edit-title">CHANGE PASSWORD</h2>
        <form data-password-edit-form data-url="{{ route(auth()->user()->role.'.password.update') }}">
            @csrf
            <label>Current Password<input type="password" data-password-field="current" autocomplete="current-password" required></label>
            <label>New Password<input type="password" data-password-field="new" autocomplete="new-password" minlength="8" required></label>
            <label>Confirm New Password<input type="password" data-password-field="confirm" autocomplete="new-password" minlength="8" required></label>
            <div class="registration-confirmation-actions">
                <button type="submit">Save</button>
                <button type="button" data-cancel-password-edit data-cancel-confirmation>Cancel</button>
            </div>
            <p class="customer-profile-edit-feedback" role="status" hidden></p>
        </form>
    </dialog>
    <dialog class="registration-confirmation" data-password-confirmation aria-labelledby="password-confirmation-title" aria-describedby="password-confirmation-description">
        <h2 id="password-confirmation-title">CHANGE PASSWORD?</h2>
        <p id="password-confirmation-description">Are you sure you want to change your password?</p>
        <div class="registration-confirmation-actions">
            <button type="button" data-confirm-password>Change password</button>
            <button type="button" data-cancel-password-confirmation data-cancel-confirmation autofocus>Cancel</button>
        </div>
    </dialog>

