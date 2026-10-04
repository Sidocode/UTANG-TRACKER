@props(['name', 'label', 'options', 'value' => ''])
<div class="customer-dropdown" data-dropdown>
    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    <button type="button" class="customer-dropdown-trigger" aria-expanded="false" aria-controls="filter-{{ $name }}" aria-label="Filter by {{ $label }}" data-dropdown-trigger>
        <span>{{ $label }}<img src="{{ asset('assets/icons/arrow-dropDown.svg') }}" width="24" height="24" alt=""></span>
    </button>
    <div class="customer-dropdown-list" id="filter-{{ $name }}" role="group" aria-label="{{ $label }} options" hidden>
        @foreach ($options as $optionValue => $text)
            <button type="button" data-dropdown-value="{{ $optionValue }}" aria-pressed="{{ (string) $value === (string) $optionValue ? 'true' : 'false' }}" title="{{ (string) $value === (string) $optionValue ? 'Select again to clear this filter' : $text }}">{{ $text }}</button>
        @endforeach
    </div>
</div>
