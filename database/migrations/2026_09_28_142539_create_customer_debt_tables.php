<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('id', 'userId');
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('mobileNumber', 20)->unique();
            $table->string('role')->default('customer');
            $table->string('status')->default('Active');
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transactionId');
            $table->foreignId('customerId')->constrained('users', 'userId')->restrictOnDelete();
            $table->dateTime('date');
            $table->decimal('totalAmount', 12, 2);
        });
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id('itemId');
            $table->foreignId('transactionId')->constrained('transactions', 'transactionId')->restrictOnDelete();
            $table->string('itemName');
            $table->unsignedInteger('quantity');
            $table->decimal('price', 12, 2);
            $table->decimal('subtotal', 12, 2);
        });
        Schema::create('debts', function (Blueprint $table) {
            $table->id('debtId');
            $table->foreignId('customerId')->constrained('users', 'userId')->restrictOnDelete();
            $table->foreignId('transactionId')->unique()->constrained('transactions', 'transactionId')->restrictOnDelete();
            $table->decimal('debtAmount', 12, 2);
            $table->decimal('remaining', 12, 2);
            $table->string('debtStatus');
            $table->date('dueDate')->nullable();
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id('paymentId');
            $table->foreignId('customerId')->constrained('users', 'userId')->restrictOnDelete();
            $table->foreignId('debtId')->constrained('debts', 'debtId')->restrictOnDelete();
            $table->decimal('paymentAmount', 12, 2);
            $table->string('paymentMethod');
            $table->string('paymentStatus');
            $table->dateTime('paymentDate');
            $table->string('referenceNumber')->nullable();
            $table->dateTime('verifiedAt')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('debts');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['mobileNumber']);
            $table->dropColumn(['firstName', 'lastName', 'mobileNumber', 'role', 'status']);
            $table->renameColumn('userId', 'id');
        });
    }
};
