@extends('layout.owner')
@section('title', 'Notifications')
@section('heading', 'NOTIFICATIONS')
@section('subtitle', 'Gcash payment alert')
@section('content')
<section class="notifications-screen" aria-label="GCash payment notifications">
    @forelse ($notifications as $notification)
        @php
            $pending = ! $notification->readAt;
            $timestamp = \Illuminate\Support\Carbon::parse($notification->createdAt);
        @endphp
        <article @class(['notification-card', 'notification-card-new' => $pending]) data-owner-notification data-read-url="{{ route('owner.notifications.read', $notification->notificationId) }}" role="button" tabindex="0" aria-label="{{ $notification->title }}. Mark as read">
            <div class="notification-marker" aria-hidden="true">@if($pending)<img src="{{ asset('assets/icons/notification-dot.svg') }}" width="30" height="30" alt="">@endif</div>
            <div class="notification-copy">
                <h2>{{ $notification->title }}</h2>
                <p>{{ $notification->message }}</p>
                <time datetime="{{ $timestamp->toIso8601String() }}">{{ $timestamp->isToday() ? 'Today, '.$timestamp->format('g:i A') : $timestamp->format('M d, Y, g:i A') }}</time>
            </div>
        </article>
    @empty
        <p class="notifications-empty">No notifications yet.</p>
    @endforelse
</section>
@endsection
