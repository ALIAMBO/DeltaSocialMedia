<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Show the inbox: list of conversations.
     */
    public function index()
    {
        $authId = Auth::id();

        // Get distinct conversation partners
        $conversations = User::whereIn('id', function ($query) use ($authId) {
            $query->select(DB::raw("CASE WHEN sender_id = {$authId} THEN receiver_id ELSE sender_id END"))
                ->from('messages')
                ->where('sender_id', $authId)
                ->orWhere('receiver_id', $authId);
        })
            ->with('profile')
            ->get()
            ->map(function ($user) use ($authId) {
                $user->last_message = Message::where(function ($q) use ($authId, $user) {
                    $q->where('sender_id', $authId)->where('receiver_id', $user->id);
                })->orWhere(function ($q) use ($authId, $user) {
                    $q->where('sender_id', $user->id)->where('receiver_id', $authId);
                })->latest()->first();
                return $user;
            })
            ->sortByDesc(fn($u) => optional($u->last_message)->created_at);

        return view('chat.index', compact('conversations'));
    }

    /**
     * Show conversation with a specific user.
     */
    public function show(User $user)
    {
        $authId = Auth::id();

        // Mark messages from that user as read
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $authId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where(function ($q) use ($authId, $user) {
            $q->where('sender_id', $authId)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($authId, $user) {
            $q->where('sender_id', $user->id)->where('receiver_id', $authId);
        })
            ->with(['sender.profile', 'receiver.profile'])
            ->oldest()
            ->get();

        return view('chat.show', compact('user', 'messages'));
    }

    /**
     * Send a message.
     */
    public function send(Request $request, User $user)
    {
        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $user->id,
            'body'        => $request->body,
        ]);

        return back();
    }
}
