<?php

namespace App\Listeners;

use App\Events\CommentCreated;
use App\Events\CommentCreateRejected;
use App\Events\CommentDeleted;
use App\Events\CommentVisibilityChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class WriteCommentAuditLog
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
            $payload = match (true) {
                $event instanceof CommentCreated => [
                    'event' => 'comment_created',
                    'comment_id' => $event->comment->id,
                    'post_id' => $event->comment->post_id,
                    'user_id' =>$event->comment->user->id,
                    'ip' => $event->ip,
                    'meta' => [
                        'length' => $event->length,

                    ],
                ],
                $event instanceof CommentCreateRejected => [
                    'event' => 'comment_create_rejected',
                    'post_id' => $event->post->id,
                    'user_id' => $event->actor->id,
                    'ip' => $event->ip,
                    'meta' => array_merge([
                        'reason' => $event->reason,

                    ], $event->meta),
                ],
                $event instanceof CommentVisibilityChanged => [
                    'event' => 'comment_visibility_changed',
                    'comment_id' => $event->comment->id,
                    'post_id' => $event->comment->post_id,
                    'user_id' =>$event->comment->user->id,
                    'admin_id' => $event->actor->id,
                    'ip' => $event->ip,
                    'meta' => [
                        'from' => $event->from,
                        'to' => $event->to,

                    ],
                ],
                $event instanceof CommentDeleted => [
                    'event' => 'comment_deleted',
                    'comment_id' => $event->comment->id,
                    'post_id' => $event->comment->post_id,
                    'user_id' =>$event->comment->user->id,
                    'admin_id' => $event->actor->id,
                    'ip' => $event->ip,
                    'meta' => [
                        'length' => $event->length,

                    ],
                ],

                default  => null,

            };

            if($payload === null) {
                return;

            }
            Log::channel('comment_audit')->info('comment_audit', $payload);
    }
}
