@php($hasUnread = auth()->check() && \Illuminate\Support\Facades\DB::table('notifications')->where('userId', auth()->id())->whereNull('readAt')->exists())
<span class="notification-unread-dot" data-unread-dot @if(!$hasUnread) hidden @endif><span class="sr-only">Unread notifications</span></span>
