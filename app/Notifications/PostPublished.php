<?php

namespace App\Notifications;

use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostPublished extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public readonly Post $post,
        public readonly string $publisherName
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
        return [
            'type' => 'post_published',
            'title' => 'Uus postutus avaldatud',
            'message' => $this->post->title . ' (avaldas: ' . $this->publisherName . ')',
            'post_id' => $this->post->id,
            'post_slug' => $this->post->slug,
            'public_url' => route('posts.show', $this->post),
        ];
    }
}
