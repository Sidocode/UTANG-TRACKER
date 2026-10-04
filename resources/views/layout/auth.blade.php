<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Create Account') — UTANG Tracker</title>
    @vite(['resources/css/app.css', 'resources/js/customer-signup.js'])
</head>
<body class="customer-registration-page">
    <main class="customer-registration" style="--customer-background: url('{{ asset('assets/image/customerBg.webp') }}?v=layers')">
        <img class="customer-registration-hanging" src="{{ asset('assets/image/hanging.webp') }}" width="423" height="214" alt="" aria-hidden="true">
        <img class="customer-registration-lady" src="{{ asset('assets/image/lady.webp') }}" width="363" height="278" alt="" aria-hidden="true">
        <header class="customer-registration-brand">
            @yield('customer-heading')
            @unless (View::hasSection('customer-heading'))
                <p>UTANG-TRACKER</p>
                <span>Customer Debt Tracking</span>
            @endunless
        </header>
        @yield('content')
    </main>
</body>
</html>
