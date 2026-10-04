@props(['customer'])
    <header class="customer-dashboard-header">
        <div class="customer-dashboard-header-inner">
            <div class="customer-header-copy">@yield('header-back')<div><h1>@yield('heading', 'Hello, '.$customer->firstName)</h1><p>@yield('subtitle', 'welcome back')</p></div></div>
            <a href="{{ route('customer.notifications') }}" class="customer-notification-link unread-notification-icon" aria-label="Notifications" data-unread-url="{{ route('customer.notifications.unread') }}"><img src="{{ asset('assets/icons/customer-notification.svg') }}" alt=""><x-unread-notification-dot /></a>
        </div>
    </header>

