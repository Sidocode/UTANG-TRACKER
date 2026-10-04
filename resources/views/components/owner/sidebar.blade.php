<aside class="owner-sidebar" aria-label="Owner sidebar">
    <a class="brand" href="{{ route('owner.dashboard') }}" aria-label="UTANG Tracker dashboard">
        <img src="{{ asset('assets/logo/utangTracker-logo.svg') }}" alt="UTANG Tracker" width="156" height="112">
    </a>
    <nav aria-label="Owner navigation">
        @foreach (['dashboard' => 'DASHBOARD', 'customers' => 'CUSTOMERS', 'transactions' => 'TRANSACTIONS', 'utang' => 'UTANG', 'payments' => 'PAYMENTS', 'audit-log' => 'AUDIT LOG'] as $name => $label)
            <a @class(['nav-item', 'is-active' => request()->routeIs('owner.'.$name, 'owner.'.$name.'.*')]) href="{{ route('owner.'.$name) }}" @if(request()->routeIs('owner.'.$name, 'owner.'.$name.'.*')) aria-current="page" @endif><span>{{ $label }}</span></a>
        @endforeach
    </nav>
    <button class="nav-item logout" type="button" data-account-confirm="logout"><span>LOG OUT</span></button>
</aside>
