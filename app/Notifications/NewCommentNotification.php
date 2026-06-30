<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification
{
    use Queueable;

    protected $commenter;
    protected $post;
    protected $comment;

    public function __construct(User $commenter, Post $post, Comment $comment)
    {
        $this->commenter = $commenter;
        $this->post = $post;
        $this->comment = $comment;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'commenter_id' => $this->commenter->id,
            'commenter_name' => $this->commenter->name,
            'post_id' => $this->post->id,
            'comment_id' => $this->comment->id,
            'comment_body' => $this->comment->body,
            'message' => 'mengulas pada post anda',
        ];
    }
}
