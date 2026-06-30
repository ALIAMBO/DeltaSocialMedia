<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->unreadNotifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('success', 'Pemberitahuan telah ditanda sebagai dibaca.');
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua pemberitahuan telah ditanda sebagai dibaca.');
    }
}
