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
            ->get();

        // Fetch latest messages for all conversation partners in a single query
        $latestMessages = Message::whereIn('id', function ($query) use ($authId) {
            $query->select(DB::raw('MAX(id)'))
                ->from('messages')
                ->where('sender_id', $authId)
                ->orWhere('receiver_id', $authId)
                ->groupBy(DB::raw('CASE WHEN sender_id = ' . $authId . ' THEN receiver_id ELSE sender_id END'));
        })->get()->keyBy(function ($msg) use ($authId) {
            return $msg->sender_id === $authId ? $msg->receiver_id : $msg->sender_id;
        });

        $conversations = $conversations->map(function ($user) use ($latestMessages) {
            $user->last_message = $latestMessages->get($user->id);
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

    /**
     * API: Get inbox conversations list.
     */
    public function apiGetConversations()
    {
        $authId = Auth::id();
        $conversations = User::whereIn('id', function ($query) use ($authId) {
            $query->select(DB::raw("CASE WHEN sender_id = {$authId} THEN receiver_id ELSE sender_id END"))
                ->from('messages')
                ->where('sender_id', $authId)
                ->orWhere('receiver_id', $authId);
        })
        ->with('profile')
        ->get();

        $partnerIds = $conversations->pluck('id')->toArray();

        // Fetch latest messages for all conversation partners in a single query
        $latestMessages = Message::whereIn('id', function ($query) use ($authId) {
            $query->select(DB::raw('MAX(id)'))
                ->from('messages')
                ->where('sender_id', $authId)
                ->orWhere('receiver_id', $authId)
                ->groupBy(DB::raw('CASE WHEN sender_id = ' . $authId . ' THEN receiver_id ELSE sender_id END'));
        })->get()->keyBy(function ($msg) use ($authId) {
            return $msg->sender_id === $authId ? $msg->receiver_id : $msg->sender_id;
        });

        // Fetch unread counts for all conversation partners in a single query
        $unreadCounts = Message::where('receiver_id', $authId)
            ->where('is_read', false)
            ->whereIn('sender_id', $partnerIds)
            ->groupBy('sender_id')
            ->select('sender_id', DB::raw('count(*) as count'))
            ->pluck('count', 'sender_id');

        $conversations = $conversations->map(function ($user) use ($latestMessages, $unreadCounts) {
            $user->last_message = $latestMessages->get($user->id);
            $user->unread_count = $unreadCounts->get($user->id, 0);
            $user->avatar = $user->profile?->avatar_url ?? asset('images/default-avatar.png');
            return $user;
        })
        ->sortByDesc(fn($u) => optional($u->last_message)->created_at)
        ->values();

        return response()->json($conversations);
    }

    /**
     * API: Get followed users as contact list.
     */
    public function apiGetContacts()
    {
        $user = Auth::user();
        $contacts = User::whereIn('id', $user->following()->pluck('following_id'))
            ->with('profile')
            ->get()
            ->map(function ($u) {
                $u->avatar = $u->profile?->avatar_url ?? asset('images/default-avatar.png');
                return $u;
            });

        return response()->json($contacts);
    }

    /**
     * API: Get messages history with a user, marking them as read.
     */
    public function apiGetMessages(User $user)
    {
        $authId = Auth::id();
        
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $authId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        $messages = Message::where(function ($q) use ($authId, $user) {
            $q->where('sender_id', $authId)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($authId, $user) {
            $q->where('sender_id', $user->id)->where('receiver_id', $authId);
        })
        ->oldest()
        ->get()
        ->map(function ($msg) use ($authId) {
            $msg->is_sent_by_me = $msg->sender_id === $authId;
            $msg->time = $msg->created_at->diffForHumans();
            return $msg;
        });

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->profile?->avatar_url ?? asset('images/default-avatar.png'),
            ],
            'messages' => $messages
        ]);
    }

    /**
     * API: Send a message to a user.
     */
    public function apiSendMessage(Request $request, User $user)
    {
        $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $msg = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $user->id,
            'body'        => $request->body,
        ]);

        $msg->is_sent_by_me = true;
        $msg->time = $msg->created_at->diffForHumans();

        return response()->json($msg);
    }
}
