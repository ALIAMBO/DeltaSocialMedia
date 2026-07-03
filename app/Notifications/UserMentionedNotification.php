<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserMentionedNotification extends Notification
{
    use Queueable;

    protected $mentioner;
    protected $post;

    public function __construct(User $mentioner, Post $post)
    {
        $this->mentioner = $mentioner;
        $this->post = $post;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'mentioner_id' => $this->mentioner->id,
            'mentioner_name' => $this->mentioner->name,
            'post_id' => $this->post->id,
            'message' => 'mentioned you in a post',
        ];
    }
}
