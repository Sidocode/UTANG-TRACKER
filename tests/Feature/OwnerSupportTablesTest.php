<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OwnerSupportTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_tables_exist_and_start_empty(): void
    {
        foreach ([
            'audit_logs' => ['auditlogId', 'userId', 'payment_id', 'tableName', 'action', 'description', 'created_at'],
            'notifications' => ['notificationId', 'userId', 'title', 'message', 'createdAt'],
            'payment_settings' => ['paymentSettingId', 'userId', 'accountName', 'mobileNumber', 'qrImagePath'],
        ] as $table => $columns) {
            $this->assertTrue(Schema::hasColumns($table, $columns));
            $this->assertDatabaseCount($table, 0);
        }
    }
}
