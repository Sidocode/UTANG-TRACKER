@props(['status'])
<span @class(['status', 'status-success' => in_array($status, ['Paid', 'Verified']), 'status-danger' => in_array($status, ['Unpaid', 'Rejected']), 'status-pending' => in_array($status, ['Pending', 'Partial'])])>{{ $status }}</span>

