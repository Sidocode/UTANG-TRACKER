<dialog class="transaction-details-modal gcash-settings-modal" aria-labelledby="gcash-settings-title">
    <header class="transaction-modal-heading">
        <h2 id="gcash-settings-title">GCash Payment Settings</h2>
        <button type="button" data-close-gcash-settings aria-label="Close GCash settings"><img src="{{ asset('assets/icons/close.svg') }}" width="24" height="24" alt=""></button>
    </header>
    <form data-gcash-settings-form action="{{ route('owner.payment-settings.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="registration-fields">
            <label>GCash Account Name<input name="accountName" value="{{ $paymentSettings?->accountName }}" placeholder="Enter name" autocomplete="name" maxlength="100" required></label>
            <label>GCash Number<input name="mobileNumber" value="{{ $paymentSettings?->mobileNumber }}" type="tel" inputmode="numeric" autocomplete="tel-national" pattern="09[0-9]{9}" maxlength="11" title="Enter an 11-digit mobile number starting with 09" required></label>
            <label>GCash QR Code
                <span class="gcash-qr-upload">
                    <input name="qrImage" type="file" accept="image/png,image/jpeg,image/webp" aria-label="Upload GCash QR code" @required(!$paymentSettings)>
                    <span data-qr-placeholder @if($paymentSettings) hidden @endif>Upload here</span>
                    <img data-qr-preview @if($paymentSettings) src="{{ route('owner.payment-settings.qr', $paymentSettings->paymentSettingId) }}" data-saved-src="{{ route('owner.payment-settings.qr', $paymentSettings->paymentSettingId) }}" @else hidden @endif alt="Selected GCash QR code preview">
                </span>
            </label>
        </div>
        <div class="transaction-modal-actions">
            <button type="submit" class="customer-action customer-register"><span>Save</span></button>
            <button type="button" class="customer-action" data-close-gcash-settings><span>Cancel</span></button>
        </div>
        <p role="status" class="transaction-modal-feedback" data-gcash-settings-feedback hidden></p>
    </form>
</dialog>
