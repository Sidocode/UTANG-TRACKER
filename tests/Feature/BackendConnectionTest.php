<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoCustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class BackendConnectionTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed(DemoCustomerSeeder::class);
        $owner = User::factory()->create(['firstName' => 'Test', 'lastName' => 'Owner', 'mobileNumber' => '09999999999']);
        $owner->forceFill(['role' => 'owner', 'status' => 'Active'])->save();

        return $owner;
    }

    public function test_settings_are_saved_and_existing_qr_is_preserved(): void
    {
        Storage::fake('local');
        $owner = $this->owner();
        $image = UploadedFile::fake()->createWithContent('qr.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9xkAAAAASUVORK5CYII='));
        $this->actingAs($owner)->postJson('/owner/payment-settings', ['accountName' => 'Test Owner', 'mobileNumber' => '09999999999', 'qrImage' => $image])->assertOk();
        $setting = DB::table('payment_settings')->first();
        Storage::disk('local')->assertExists($setting->qrImagePath);
        $this->postJson('/owner/payment-settings', ['accountName' => 'Updated Owner', 'mobileNumber' => '09999999999'])->assertOk();
        $this->assertDatabaseHas('payment_settings', ['accountName' => 'Updated Owner', 'qrImagePath' => $setting->qrImagePath]);
        $this->postJson('/owner/payment-settings', ['accountName' => 'Invalid', 'mobileNumber' => 'bad', 'qrImage' => UploadedFile::fake()->create('bad.svg')])->assertUnprocessable();
        $this->assertDatabaseCount('payment_settings', 1);
        $this->assertDatabaseCount('audit_logs', 2);
    }

    public function test_registration_requires_approval_and_can_be_approved_only_once(): void
    {
        $owner = $this->owner();
        $this->postJson('/register', ['firstName' => 'New', 'lastName' => 'Customer', 'mobileNumber' => '09111111111', 'password' => 'NewCustomer123!', 'password_confirmation' => 'NewCustomer123!'])->assertOk();
        $row = DB::table('registration_requests')->first();
        $this->assertTrue(Hash::check('NewCustomer123!', $row->password));
        $this->assertDatabaseMissing('users', ['mobileNumber' => '09111111111']);
        $this->actingAs($owner)->postJson('/owner/customers/pending/'.$row->requestId.'/approve')->assertOk();
        $this->postJson('/owner/customers/pending/'.$row->requestId.'/approve')->assertOk();
        $this->assertSame(1, DB::table('users')->where('mobileNumber', '09111111111')->count());
        $this->assertDatabaseHas('registration_requests', ['requestId' => $row->requestId, 'status' => 'Approved']);
    }

    public function test_transaction_totals_are_computed_and_retries_do_not_duplicate_debts(): void
    {
        $owner = $this->owner();
        $customer = User::where('mobileNumber', '09000000001')->first();
        $payload = ['customer' => $customer->userId, 'submissionKey' => (string) Str::uuid(), 'totalAmount' => '0.01', 'items' => [['name' => 'Rice', 'quantity' => 3, 'price' => '12.35'], ['name' => 'Soap', 'quantity' => 2, 'price' => '10.10']]];
        $this->actingAs($owner)->postJson('/owner/transactions', $payload)->assertOk();
        $this->postJson('/owner/transactions', $payload)->assertOk();
        $this->assertDatabaseHas('transactions', ['submissionKey' => $payload['submissionKey'], 'totalAmount' => '57.25']);
        $this->assertDatabaseCount('transactions', 11);
        $this->assertDatabaseCount('debts', 11);
        $this->assertDatabaseCount('audit_logs', 1);
    }

    public function test_pending_payment_changes_balance_only_when_verified_once(): void
    {
        $owner = $this->owner();
        $customer = User::where('mobileNumber', '09000000001')->first();
        DB::table('payment_settings')->insert(['userId' => $owner->userId, 'accountName' => 'Owner', 'mobileNumber' => '09999999999', 'qrImagePath' => 'qr.png']);
        $debt = DB::table('debts')->where('customerId', $customer->userId)->first();
        $payload = ['debt' => $debt->debtId, 'amount' => '125.50', 'referenceNumber' => '602997199999', 'submissionKey' => (string) Str::uuid()];
        $this->actingAs($customer)->postJson('/customer/payment', $payload)->assertOk();
        $this->postJson('/customer/payment', $payload)->assertOk();
        $payment = DB::table('payments')->where('referenceNumber', '602997199999')->first();
        $this->assertDatabaseHas('notifications', ['userId' => $owner->userId, 'title' => 'GCash payment needs verification', 'message' => 'Ana Santos submitted a GCash payment of ₱125.50. Payment #'.$payment->paymentId.' needs your verification.']);
        $this->assertDatabaseHas('debts', ['debtId' => $debt->debtId, 'remaining' => '500.00']);
        $this->actingAs($owner)->postJson('/owner/payments/'.$payment->paymentId.'/review', ['action' => 'verify'])->assertOk();
        $this->postJson('/owner/payments/'.$payment->paymentId.'/review', ['action' => 'verify'])->assertOk();
        $this->assertDatabaseHas('debts', ['debtId' => $debt->debtId, 'remaining' => '374.50', 'debtStatus' => 'Partial']);
        $this->assertDatabaseHas('payments', ['paymentId' => $payment->paymentId, 'paymentStatus' => 'Verified']);
        $this->assertDatabaseHas('notifications', ['userId' => $customer->userId, 'title' => 'GCash payment verified', 'message' => 'The owner verified your GCash payment of ₱125.50. Your remaining transaction balance is ₱374.50.']);
        $this->assertDatabaseCount('payments', 7);
        $this->assertDatabaseCount('audit_logs', 2);
    }

    public function test_cash_payment_rejects_overpayment_and_foreign_debts(): void
    {
        $owner = $this->owner();
        $customer = User::where('mobileNumber', '09000000001')->first();
        $debt = DB::table('debts')->where('customerId', $customer->userId)->first();
        $payload = ['customer' => $customer->userId, 'debt' => $debt->debtId, 'amount' => '501.00', 'submissionKey' => (string) Str::uuid()];
        $this->actingAs($owner)->postJson('/owner/payments', $payload)->assertUnprocessable();
        $payload['amount'] = '500.00';
        $this->postJson('/owner/payments', $payload)->assertOk();
        $this->assertDatabaseHas('debts', ['debtId' => $debt->debtId, 'remaining' => '0.00', 'debtStatus' => 'Paid']);
        $this->assertDatabaseHas('payments', ['submissionKey' => $payload['submissionKey'], 'paymentMethod' => 'Cash', 'paymentStatus' => 'Verified', 'referenceNumber' => null]);
        $this->postJson('/owner/payments', $payload + ['method' => 'GCash', 'referenceNumber' => '123456789'])->assertOk();
        $this->assertDatabaseCount('payments', 7);
        $other = User::where('mobileNumber', '09000000002')->first();
        $this->actingAs($other)->postJson('/customer/payment', ['debt' => $debt->debtId, 'amount' => '1.00', 'referenceNumber' => '123456789', 'submissionKey' => (string) Str::uuid()])->assertNotFound();
        $this->postJson('/owner/payments', $payload)->assertForbidden();
    }

    public function test_rejecting_payment_preserves_balance_and_deactivation_blocks_login(): void
    {
        $owner = $this->owner();
        $payment = DB::table('payments')->where('paymentStatus', 'Pending')->first();
        $this->actingAs($owner)->postJson('/owner/payments/'.$payment->paymentId.'/review', ['action' => 'reject'])->assertOk();
        $this->assertDatabaseHas('debts', ['debtId' => $payment->debtId, 'remaining' => '750.00']);
        $this->postJson('/owner/customers/'.$payment->customerId.'/deactivate')->assertOk();
        $this->assertDatabaseHas('users', ['userId' => $payment->customerId, 'status' => 'Inactive']);
        $this->assertDatabaseCount('debts', 10);
        $this->assertDatabaseCount('payments', 6);
        $this->postJson('/owner/customers/'.$payment->customerId.'/activate')->assertOk();
        $this->assertDatabaseHas('users', ['userId' => $payment->customerId, 'status' => 'Active']);
    }

    public function test_transactions_can_be_edited_only_before_payment_history_exists(): void
    {
        $owner = $this->owner();
        $unpaid = DB::table('transactions')->where('customerId', User::where('mobileNumber', '09000000003')->value('userId'))->first();
        $this->actingAs($owner)->patchJson('/owner/transactions/'.$unpaid->transactionId, ['customer' => $unpaid->customerId, 'items' => [['name' => 'Rice', 'quantity' => 2, 'price' => '40.25']]])->assertOk();
        $this->assertDatabaseHas('debts', ['transactionId' => $unpaid->transactionId, 'debtAmount' => '80.50', 'remaining' => '80.50']);
        $paid = DB::table('payments')->first();
        $transaction = DB::table('debts')->where('debtId', $paid->debtId)->value('transactionId');
        $this->patchJson('/owner/transactions/'.$transaction, ['customer' => $paid->customerId, 'items' => [['name' => 'Rice', 'quantity' => 1, 'price' => '1.00']]])->assertUnprocessable();
    }

    public function test_notification_reads_are_persisted_and_scoped_to_recipient(): void
    {
        $owner = $this->owner();
        $id = DB::table('notifications')->insertGetId(['userId' => $owner->userId, 'title' => 'Test', 'message' => 'Test message', 'createdAt' => now()], 'notificationId');
        $this->actingAs(User::where('role', 'customer')->first())->postJson('/customer/notifications/'.$id.'/read')->assertNotFound();
        $this->getJson('/customer/notifications/unread')->assertOk()->assertJson(['unread' => 0]);
        $this->actingAs($owner)->getJson('/owner/notifications/unread')->assertOk()->assertJson(['unread' => 1]);
        $this->actingAs($owner)->postJson('/owner/notifications/'.$id.'/read')->assertOk();
        $this->getJson('/owner/notifications/unread')->assertOk()->assertJson(['unread' => 0]);
        $this->assertNotNull(DB::table('notifications')->where('notificationId', $id)->value('readAt'));
    }

    public function test_payment_status_is_private_and_registration_status_uses_the_session(): void
    {
        $this->withoutVite();
        $owner = $this->owner();
        $payment = DB::table('payments')->first();
        $other = User::where('role', 'customer')->where('userId', '!=', $payment->customerId)->first();
        $this->actingAs($other)->get('/customer/payment/status?payment='.$payment->paymentId)->assertNotFound();
        $this->actingAs(User::find($payment->customerId))->get('/customer/payment/status?payment='.$payment->paymentId)->assertOk()->assertSee($payment->referenceNumber ?: 'GCash');
        $this->get('/register/pending')->assertOk()->assertSee('Submit the registration form first.');
    }

    public function test_expired_json_sessions_return_an_error_instead_of_login_html(): void
    {
        $this->postJson('/owner/payments', [])->assertUnauthorized()->assertJsonStructure(['message']);
        $this->postJson('/customer/payment', [])->assertUnauthorized()->assertJsonStructure(['message']);
        $this->get('/owner/payments')->assertRedirect('/login');
        $this->assertDatabaseCount('payments', 0);
    }
}
