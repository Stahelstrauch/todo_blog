<?php

namespace App\Providers;

use App\Events\CommentCreated;
use App\Events\CommentCreateRejected;
use App\Events\CommentDeleted;
use App\Events\CommentVisibilityChanged;
use App\Listeners\WriteCommentAuditLog;
use App\Models\Comment;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(CommentCreated::class, WriteCommentAuditLog::class);
        Event::listen(CommentCreateRejected::class, WriteCommentAuditLog::class);
        Event::listen(CommentVisibilityChanged::class, WriteCommentAuditLog::class);
        Event::listen(CommentDeleted::class, WriteCommentAuditLog::class);
    }
}
