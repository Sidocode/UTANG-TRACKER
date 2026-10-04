<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Ledger
{
    public static function cents(string|int|float $amount): int
    {
        $parts = explode('.', number_format((float) $amount, 2, '.', ''));

        return (int) $parts[0] * 100 + (int) $parts[1];
    }

    public static function money(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function audit(int $actor, string $table, string $action, string $description, ?int $payment = null): void
    {
        DB::table('audit_logs')->insert([
            'userId' => $actor, 'payment_id' => $payment, 'tableName' => $table,
            'action' => $action, 'description' => $description, 'created_at' => now(),
        ]);
    }

    public static function notify(int $user, string $title, string $message): void
    {
        DB::table('notifications')->insert([
            'userId' => $user, 'title' => $title, 'message' => $message, 'createdAt' => now(),
        ]);
    }

    public static function applyPayment(object $debt, int $amount): void
    {
        $remaining = self::cents($debt->remaining) - $amount;
        DB::table('debts')->where('debtId', $debt->debtId)->update([
            'remaining' => self::money($remaining), 'debtStatus' => $remaining === 0 ? 'Paid' : 'Partial',
        ]);
    }
}
