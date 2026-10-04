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
        foreach (['payments', 'transactions'] as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->uuid('submissionKey')->nullable()->unique();
            });
        }
        Schema::table('payments', function (Blueprint $table): void {
            $table->unique('referenceNumber');
        });
        Schema::table('notifications', function (Blueprint $table): void {
            $table->dateTime('readAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table): void {
            $table->dropColumn('readAt');
        });
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropUnique(['referenceNumber']);
        });
        foreach (['payments', 'transactions'] as $name) {
            Schema::table($name, function (Blueprint $table): void {
                $table->dropUnique(['submissionKey']);
                $table->dropColumn('submissionKey');
            });
        }
    }
};
