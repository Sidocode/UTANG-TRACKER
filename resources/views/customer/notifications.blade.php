@extends('layout.customer-dashboard')
@section('title', 'Notifications')
@section('heading', 'NOTIFICATIONS')
@section('subtitle', '')
@section('screen', 'notifications')
@section('content')
    <section class="customer-notification-list" aria-label="Notifications, scroll for more" tabindex="0">
        @forelse ($notifications as $notification)
            @php
                $date = \Carbon\Carbon::parse($notification->createdAt);
            @endphp
            <button type="button" @class(['customer-notification-card', 'is-read' => $notification->readAt]) data-notification-key="{{ $notification->notificationId }}" data-read="{{ $notification->readAt ? 'true' : 'false' }}" data-read-url="{{ route('customer.notifications.read', $notification->notificationId) }}" aria-label="{{ $notification->title }}. Mark as read">
                <span class="customer-notification-title">{{ $notification->title }}</span>
                <span class="customer-notification-message">{{ $notification->message }}</span>
                <time datetime="{{ $date->toIso8601String() }}">{{ $date->isToday() ? 'Today' : $date->format('M j, Y') }}, {{ $date->format('g:i A') }}</time>
            </button>
        @empty
            <p class="customer-transaction-empty">No notifications yet.</p>
        @endforelse
    </section>
@endsection
