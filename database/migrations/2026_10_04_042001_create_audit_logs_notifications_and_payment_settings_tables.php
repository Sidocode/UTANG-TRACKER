<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('auditlogId');
            $table->foreignId('userId')->constrained('users', 'userId')->restrictOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments', 'paymentId')->restrictOnDelete();
            $table->string('tableName', 100);
            $table->string('action', 100);
            $table->text('description');
            $table->dateTime('created_at')->index();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notificationId');
            $table->foreignId('userId')->constrained('users', 'userId')->restrictOnDelete();
            $table->string('title');
            $table->text('message');
            $table->dateTime('createdAt')->index();
        });

        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id('paymentSettingId');
            $table->foreignId('userId')->unique()->constrained('users', 'userId')->restrictOnDelete();
            $table->string('accountName', 100);
            $table->string('mobileNumber', 20);
            $table->string('qrImagePath');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('audit_logs');
    }
};
