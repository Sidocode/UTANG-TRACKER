# UTANG Tracker backend integration

## Outcome and existing state

Connect the existing owner and customer interface to the local `utang_tracker` database for alpha testing. Preserve the Figma-based layouts and existing sample data. Do not reset, reseed, or deploy the database as part of this work.

Customer login/logout already work. Nine business tables exist. Most write flows still only display preview feedback. Owner pages currently permit anonymous local access. Owner notifications and audit pages currently derive activity from payments instead of their dedicated tables.

## Approach

Extend the existing Laravel controllers, Blade views, and JavaScript; do not introduce a separate API application or replace the interface. Use role-protected web routes and CSRF protection. Put shared payment and balance operations in a service so owner and customer entry points follow the same rules. JSON responses support existing dialogs; normal form submissions receive redirects with validation errors.

An alternative separate API would add authentication and deployment complexity without helping the current application. Leaving individual preview flows mixed with live records would make alpha-test results misleading.

## Accounts and authorization

- Create the initial owner through an interactive Artisan command, using details and a hidden password entered by the user. Do not invent personal credentials or publicly expose owner setup.
- Require active owner authentication for every owner route, including AJAX details and mutations. Remove anonymous local-owner access. Redirect guests to login; reject the wrong role.
- Continue selecting customer records through the authenticated user. Enforce account status on every request.
- Keep login throttling, regenerate sessions on login, and invalidate sessions on logout. Add real owner logout.
- Password recovery requires a delivery/recovery policy that has not been supplied. Keep its unavailability explicit; do not implement an insecure reset bypass.

## Registration and customer management

- Customer registration validates names, unique Philippine mobile number, and confirmed password; stores a hashed password in a Pending request, not an active account.
- Pending status uses the server-side session's request identity, not a public mobile-number lookup or browser-only preview values. It reflects approval or deletion without exposing other applicants.
- Owner approval locks the pending request and atomically creates one active customer. Preserve the request with Approved status and reviewedAt. Repeating approval cannot create another account.
- Owner rejection deletes the pending request as previously requested, without copying applicant personal details into an audit record.
- Owner registration creates an active customer directly. Check duplicates across both users and pending requests.
- Deactivation marks the customer Inactive; preserve debts, payments, and transaction history and block further account access.

## Transactions

- Save customer, items, quantities, prices, date, and debt together in one database transaction. Calculate subtotals and totals on the server with integer cent arithmetic. Never trust totals posted by the browser.
- Validate an active customer, at least one item, positive integer quantities, positive two-decimal prices, and bounded totals.
- Preserve the existing confirmation dialogs. Saving an already recorded details view must never insert a duplicate transaction.
- Modify opens an editable transaction with current items. Allow edits only before any payments have been recorded, including pending or rejected submissions; explain this restriction in the UI. Lock affected records and recheck this condition on save. Corrections to transactions with payment history need a separate adjustment policy.
- Protect against duplicate submissions with a server-checked operation token, not only a disabled button.

## Payments and balances

- Customer GCash submissions select only their own outstanding debt, require a numeric reference number and positive two-decimal amount, and create Pending payments. Pending and rejected payments do not reduce balances.
- Display the owner's saved GCash name, number, and QR. Keep Not set until configured, and prevent submissions while recipient settings are missing.
- Owner-recorded cash payments are immediately Verified. Owner-recorded GCash payments also require a reference number and explicit receipt confirmation.
- Verify/reject only Pending GCash payments. Lock payment/debt records, recheck ownership and status, and reject overpayments against the current remaining balance.
- Subtract a payment exactly once when verified. Remaining zero means Paid; a positive balance below original amount means Partial; otherwise unpaid. Preserve existing stored status conventions where required by current filters.
- Add a uniqueness constraint for non-null GCash references after checking current data for duplicates. Reject repeated references and duplicate operation tokens.
- Show actual submitted payment amount, reference, date, and current status on the customer payment-status screen, scoped to the logged-in user.

## Profile and GCash settings

- Persist validated customer name/mobile updates; enforce mobile uniqueness across accounts and pending requests.
- Require the current password and a matching new-password confirmation. Hash the new password and invalidate other sessions after a password change.
- Save owner payment settings through the existing form. Validate phone/name and actual PNG/JPEG/WebP image content, limit upload size to 5 MB, and use generated storage paths. Preserve the existing QR when no replacement is uploaded.
- Publish QR images through Laravel storage or a controlled serving route; never accept a client-supplied filesystem path.

## Notifications and audit history

- Write audit events for meaningful owner/payment actions alongside their database changes. Read the audit screen from audit_logs and preserve its filters and `6:44 PM` time formatting.
- Write notifications for new registrations and submitted GCash payments to active owners; send customer notifications for transaction creation and payment decisions.
- Read notification screens from notifications. Add nullable readAt so read state persists for the authenticated recipient, with a scoped mark-read endpoint.
- Do not fabricate historical audit events for seeded records. Newly connected operations create actual history; empty-state messages remain supported.

## Errors and verification

- Display field validation and actionable server errors inside existing forms/dialogs without clearing entered values. Only show success or navigate to a success page after the server confirms persistence.
- Test guest/wrong-role/inactive access, cross-customer IDs, hashing, duplicate registration/approval/submissions, server-computed totals, pending/rejected balances, verified partial/full balances, repeated verification, overpayment, profile/password changes, QR validation, and persisted audit/notification records.
- Run tests using the isolated test database, never reset the local sample database. Run PHP formatting, frontend build, Blade compilation, and browser smoke checks for the registration-to-payment workflow.

## Completion boundary

The integration is ready for local alpha testing when a newly registered customer can be approved, log in, receive an owner-entered debt, submit GCash, and see the balance change only after owner verification, with corresponding history and notifications. Deployment remains a separate step after alpha testing. Password recovery and financial adjustments after payments are explicitly outside this initial integration until their policies are defined.
