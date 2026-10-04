<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BackendController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Owner\AuditLogController;
use App\Http\Controllers\Owner\CustomerController;
use App\Http\Controllers\Owner\CustomerDetailController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\NotificationController;
use App\Http\Controllers\Owner\PaymentController;
use App\Http\Controllers\Owner\PaymentVerificationController;
use App\Http\Controllers\Owner\PendingRegistrationController;
use App\Http\Controllers\Owner\TransactionController;
use App\Http\Controllers\Owner\TransactionDetailController;
use App\Http\Controllers\Owner\TransactionEntryController;
use App\Http\Controllers\Owner\UtangController;
use App\Http\Controllers\Owner\UtangDetailController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\EnsureAccountRole;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/owner/dashboard');
Route::middleware(EnsureAccountRole::class.':customer')->group(function () {
    Route::get('/customer/notifications/unread', [BackendController::class, 'unreadNotifications'])->name('customer.notifications.unread');
    Route::post('/customer/notifications/{notification}/read', [BackendController::class, 'readNotification'])->whereNumber('notification')->name('customer.notifications.read');
    Route::post('/customer/payment', [BackendController::class, 'payment'])->name('customer.payment.store');
    Route::get('/customer/payment-settings/{setting}/qr', [BackendController::class, 'qr'])->whereNumber('setting')->name('customer.payment.qr');
    Route::patch('/customer/profile', [ProfileController::class, 'update'])->name('customer.profile.update');
    Route::patch('/customer/password', [ProfileController::class, 'password'])->name('customer.password.update');
    Route::get('/customer/home', HomeController::class)->name('customer.home');
    Route::get('/customer/balance', HomeController::class)->name('customer.balance');
    Route::get('/customer/transactions', HomeController::class)->name('customer.transaction');
    Route::get('/customer/transactions/{transaction}', HomeController::class)->whereNumber('transaction')->name('customer.transaction.show');
    Route::get('/customer/payment', HomeController::class)->name('customer.payment');
    Route::get('/customer/payment/status', HomeController::class)->name('customer.payment.status');
    Route::get('/customer/profile', HomeController::class)->name('customer.profile');
    Route::get('/customer/notifications', HomeController::class)->name('customer.notifications');
});
Route::view('/register', 'auth.register')->name('customer.register');
Route::post('/register', [BackendController::class, 'register'])->middleware('throttle:10,1')->name('customer.register.store');
Route::get('/register/pending', [BackendController::class, 'registrationStatus'])->name('customer.registration.pending');
Route::view('/register/rejected', 'auth.registration-pending', ['rejected' => true])->name('customer.registration.rejected');
Route::view('/login', 'auth.login')->name('customer.login');
Route::post('/login', [LoginController::class, 'store'])->name('customer.login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::middleware(EnsureAccountRole::class.':owner')->group(function () {
    Route::get('/owner/notifications/unread', [BackendController::class, 'unreadNotifications'])->name('owner.notifications.unread');
    Route::post('/owner/notifications/{notification}/read', [BackendController::class, 'readNotification'])->whereNumber('notification')->name('owner.notifications.read');
    Route::patch('/owner/transactions/{transaction}', [BackendController::class, 'transaction'])->whereNumber('transaction')->name('owner.transactions.update');
    Route::get('/owner/transactions/{transaction}/edit', TransactionEntryController::class)->whereNumber('transaction')->name('owner.transactions.edit');
    Route::post('/owner/payment-settings', [BackendController::class, 'settings'])->name('owner.payment-settings.store');
    Route::get('/owner/payment-settings/{setting}/qr', [BackendController::class, 'qr'])->whereNumber('setting')->name('owner.payment-settings.qr');
    Route::post('/owner/customers', [BackendController::class, 'register'])->name('owner.customers.store');
    Route::post('/owner/customers/pending/{registration}/approve', [BackendController::class, 'approve'])->whereNumber('registration')->name('owner.customers.pending.approve');
    Route::post('/owner/customers/{customer}/deactivate', [BackendController::class, 'deactivate'])->whereNumber('customer')->name('owner.customers.deactivate');
    Route::post('/owner/customers/{customer}/activate', [BackendController::class, 'deactivate'])->whereNumber('customer')->name('owner.customers.activate');
    Route::post('/owner/transactions', [BackendController::class, 'transaction'])->name('owner.transactions.store');
    Route::post('/owner/payments', [BackendController::class, 'payment'])->name('owner.payments.store');
    Route::post('/owner/payments/{payment}/review', [BackendController::class, 'review'])->whereNumber('payment')->name('owner.payments.review');
    Route::patch('/owner/profile', [ProfileController::class, 'update'])->name('owner.profile.update');
    Route::patch('/owner/password', [ProfileController::class, 'password'])->name('owner.password.update');
    Route::get('/owner/profile', [ProfileController::class, 'show'])->name('owner.profile');
    Route::get('/owner/transactions', TransactionController::class)->name('owner.transactions');
    Route::get('/owner/dashboard', DashboardController::class)->name('owner.dashboard');
    Route::get('/owner/customers', CustomerController::class)->name('owner.customers');
    Route::get('/owner/customers/pending', PendingRegistrationController::class)->name('owner.customers.pending');
    Route::delete('/owner/customers/pending/{registration}', [PendingRegistrationController::class, 'reject'])->whereNumber('registration')->name('owner.customers.pending.reject');
    Route::get('/owner/customers/{customer}', CustomerDetailController::class)->whereNumber('customer')->name('owner.customers.show');

    Route::get('/owner/utang', UtangController::class)->name('owner.utang');

    Route::get('/owner/payments', PaymentController::class)->name('owner.payments');
    Route::get('/owner/payments/{payment}/verification', PaymentVerificationController::class)->whereNumber('payment')->name('owner.payments.verification');

    Route::get('/owner/notifications', NotificationController::class)->name('owner.notifications');

    Route::get('/owner/audit-log', AuditLogController::class)->name('owner.audit-log');
    Route::get('/owner/transactions/create', TransactionEntryController::class)->name('owner.transactions.create');
    Route::get('/owner/utang/{debt}', UtangDetailController::class)->whereNumber('debt')->name('owner.utang.show');

    Route::get('/owner/transactions/{transaction}/details', TransactionDetailController::class)->whereNumber('transaction')->name('owner.transactions.details');

});
