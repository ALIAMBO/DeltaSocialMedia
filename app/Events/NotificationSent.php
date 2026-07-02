<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $data;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, array $data)
    {
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('notifications.' . $this->user->id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => (string) str_nanoid(16), // Generate dummy ID or we can just send it as-is
            'message' => $this->data['message'],
            'performer_name' => $this->data['performer_name'],
            'performer_avatar' => $this->data['performer_avatar'],
            'unread_count' => $this->user->unreadNotifications()->count(),
        ];
    }
}

if (!function_exists('str_nanoid')) {
    function str_nanoid($length = 16) {
        return bin2hex(random_bytes($length / 2));
    }
}
