<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __invoke(Request $request): View
    {

        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'date' => ['nullable', 'date_format:Y-m-d'], 'payment' => ['nullable', 'in:cash,gcash']]);
        $source = DB::table('audit_logs')->join('users', 'audit_logs.userId', '=', 'users.userId')
            ->leftJoin('payments', 'audit_logs.payment_id', '=', 'payments.paymentId')
            ->select('audit_logs.auditlogId', 'audit_logs.created_at as paymentDate', 'audit_logs.action as activity', 'audit_logs.description', 'users.firstName', 'users.lastName', 'payments.paymentMethod', 'payments.paymentAmount');
        $query = DB::query()->fromSub($source, 'records');
        if ($search = trim($filters['search'] ?? '')) {
            $query->where(function ($query) use ($search) {
                $query->whereRaw("LOWER(CONCAT(firstName, ' ', lastName)) LIKE ?", ['%'.mb_strtolower($search).'%'])
                    ->orWhereRaw('LOWER(activity) LIKE ?', ['%'.mb_strtolower($search).'%']);
            });
        }
        if ($filters['date'] ?? '') {
            $query->whereDate('paymentDate', $filters['date']);
        }
        if ($filters['payment'] ?? '') {
            $query->whereRaw('LOWER(paymentMethod) = ?', [$filters['payment']]);
        }

        return view('owner.audit-log', ['activities' => $query->orderByDesc('paymentDate')->orderByDesc('auditlogId')->get(), 'filters' => $filters]);
    }
}
