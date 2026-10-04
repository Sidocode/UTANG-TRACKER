<header class="owner-header">
    <div><h1>@yield('heading', 'DASHBOARD')</h1><p>@yield('subtitle', 'over view of business activity')</p></div>
    <div class="header-actions">
        <a class="notification-button unread-notification-icon" href="{{ route('owner.notifications') }}" aria-label="Notifications" data-unread-url="{{ route('owner.notifications.unread') }}">
            <img src="{{ asset('assets/icons/notification.svg') }}" width="25" height="25" alt="">
            <x-unread-notification-dot />
        </a>
        <a class="profile-button" href="{{ route('owner.profile') }}" aria-label="Owner profile">
            <img src="{{ asset('assets/icons/profile-owner.svg') }}" width="24" height="24" alt=""><span>Owner</span>
        </a>
    </div>
</header>
