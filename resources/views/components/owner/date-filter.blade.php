@props(['value' => '', 'placeholder' => 'mm/dd/yyyy', 'label' => 'Filter by transaction date'])
<div {{ $attributes->class(['transaction-date']) }}>
    <span class="transaction-date-display" aria-hidden="true"><span class="transaction-date-label">{{ $value ? \Illuminate\Support\Carbon::parse($value)->format('m/d/Y') : $placeholder }}</span><img src="{{ asset('assets/icons/calendar.svg') }}" width="25" height="25" alt=""></span>
    <input id="transaction-date" aria-label="{{ $label }}" name="date" type="date" value="{{ $value }}">
</div>
