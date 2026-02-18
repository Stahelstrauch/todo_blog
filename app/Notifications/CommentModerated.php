<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommentModerated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Post $post,
        public readonly string $action, // hidden, unhidden, deleted 
        public readonly string $moderatorName
    )
    {
        
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $actionText = match ($this->action) {
            'hidden' => 'peideti',
            'unhidden' => 'toodi tagasi nähtavaks',
            'deleted' => 'kustutati',
            default => 'muudeti'
        };
        return [
            'type' => 'comment_moderated',
            'title' => 'Kommentaar on modereeritud',
            'message' => "Kommentaar postitusele {$this->post->title} {$actionText}.",
            'post_id' => $this->post->id,
            'post_slug' => $this->post->slug,
            'public_url' => route('posts.show', $this->post),
        ];
    }
}
