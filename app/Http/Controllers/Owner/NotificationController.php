<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __invoke(Request $request): View
    {

        $notifications = DB::table('notifications')->where('userId', $request->user()->userId)
            ->orderByDesc('createdAt')->orderByDesc('notificationId')->get();

        return view('owner.notifications', compact('notifications'));
    }
}
