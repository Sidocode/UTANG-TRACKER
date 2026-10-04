<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — UTANG Tracker</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to main content</a>
    <div class="owner-shell" style="--owner-background: url('{{ asset('assets/image/ownerBG.webp') }}?v=a21310ec549d')">
        <x-owner.sidebar />
        <main id="main-content" class="owner-main">
            <x-owner.header />
            @yield('content')
        </main>
    </div>
    @include('owner.account-confirmation')
    <form id="owner-logout-form" method="POST" action="{{ route('logout') }}" hidden>
        @csrf
    </form>
    <dialog id="preview-dialog" aria-labelledby="preview-title">
        <h2 id="preview-title">Coming next</h2>
        <p id="preview-message"></p>
        <form method="dialog"><button class="dialog-close">Close</button></form>
    </dialog>
</body>
</html>
