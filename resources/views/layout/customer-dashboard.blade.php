<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Home') — UTANG Tracker</title>
    @vite(['resources/css/app.css', 'resources/js/customer-dashboard.js'])
</head>
<body class="customer-dashboard-page" data-customer-screen="@yield('screen', 'summary')" style="--customer-background: url('{{ asset('assets/image/customerBg.webp') }}?v=layers')">
    <x-customer.header :customer="$customer" />
    <main class="customer-dashboard-main">@yield('content')</main>
    <x-customer.bottom-nav />
    <dialog class="customer-feature-preview" aria-labelledby="customer-preview-heading">
        <h2 id="customer-preview-heading"></h2><p>This screen will be connected next.</p>
        <form method="dialog"><button>Close</button></form>
    </dialog>
</body>
</html>

