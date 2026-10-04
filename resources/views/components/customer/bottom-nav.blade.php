    <nav class="customer-bottom-nav" aria-label="Customer navigation">
        @foreach (['home' => 'Home', 'balance' => 'Balance', 'transaction' => 'Transaction', 'payment' => 'Payment', 'profile' => 'Profile'] as $icon => $label)
            @if (in_array($icon, ['home', 'balance', 'transaction', 'payment', 'profile']))
                @php
                    $active = request()->routeIs('customer.'.$icon, 'customer.'.$icon.'.*');
                    $image = $icon === 'home' && ! $active ? 'home-outline' : ($icon === 'balance' && $active ? 'balance-active' : $icon);
                    if ($icon === 'transaction' && $active) { $image = 'transaction-active'; }
                    if ($icon === 'payment' && $active) { $image = 'payment-active'; }
                    if ($icon === 'profile' && $active) { $image = 'profile-active'; }
                @endphp
                <a href="{{ route('customer.'.$icon) }}" @if ($active) aria-current="page" @endif><span><img src="{{ asset('assets/icons/customer-'.$image.'.svg') }}" alt=""></span>{{ $label }}</a>
            @else
                <button type="button" data-customer-preview="{{ $label }}"><span><img src="{{ asset('assets/icons/customer-'.$icon.'.svg') }}" alt=""></span>{{ $label }}</button>
            @endif
        @endforeach
    </nav>

