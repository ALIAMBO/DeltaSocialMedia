<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRepliedToCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $replier;
    public $parentComment;
    public $reply;

    public function __construct(User $replier, Comment $parentComment, Comment $reply)
    {
        $this->replier = $replier;
        $this->parentComment = $parentComment;
        $this->reply = $reply;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message'          => 'replied to your comment: "' . \Illuminate\Support\Str::limit($this->reply->body, 30) . '"',
            'performer_id'     => $this->replier->id,
            'performer_name'   => $this->replier->name,
            'performer_avatar' => $this->replier->profile?->avatar_url ?? asset('images/default-avatar.png'),
            'post_id'          => $this->parentComment->post_id,
        ];
    }
}
