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
        Schema::create('registration_requests', function (Blueprint $table) {
            $table->id('requestId');
            $table->string('firstName');
            $table->string('lastName');
            $table->string('mobileNumber', 20)->unique();
            $table->string('password');
            $table->string('status')->default('Pending')->index();
            $table->dateTime('submittedAt');
            $table->dateTime('reviewedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_requests');
    }
};
