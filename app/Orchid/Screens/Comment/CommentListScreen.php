<?php

namespace App\Orchid\Screens\Comment;

use App\Events\CommentDeleted;
use App\Events\CommentVisibilityChanged;
use App\Models\Comment;
use App\Notifications\CommentModerated;
use App\Orchid\Layouts\Comment\CommentListLayout;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;

class CommentListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'comments' => Comment::query()
                ->with(['post:id,title,slug', 'user:id,name'])
                ->filters()
                ->defaultSort('created_at', 'desc')
                ->paginate(5),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Kommentaaride haldamine';
    }

    public function description(): ?string
    {
        return 'Kõikide kommentaaride näitamine, peitmine ja kustutamine.';
    }

    public function permission(): ?iterable
    {
        return ['platform.comments'];
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            CommentListLayout::class,
        ];
    }
    public function toggleHidden(Request $request): void { // void - tähendab et ei tagasta midagi, pole return käsklust
        $comment = Comment::with(['post', 'user'])->findOrFail($request->get('comment'));

        $from = (bool) $comment->is_hidden;

        $comment->is_hidden = ! $comment->is_hidden;

        $comment->save();

        $to = (bool) $comment->is_hidden;

        // Audit log: viibility changed
        CommentVisibilityChanged::dispatch(
            $comment,
            $request->user(),
            $request->ip(),
            $from,
            $to
        );

        // teavitus kommentaari autorile
        if ($comment->user && $comment->post) {
            $action = $comment->is_hidden ? 'hidden' : 'unhidden';

            $comment->user->notify(new CommentModerated(
                post: $comment->post,
                action: $action,
                moderatorName: $request->user()?->name ?? 'Admin'
            ));
        }
    }

    public function remove(Request $request): void {
        $comment = Comment::with(['post', 'user'])->findOrFail($request->get('comment'));

        $user = $comment->user;
        $post = $comment->post;

        // Audit log: deleted (logime enne delete, et comment/post_id oleks olemas)
        CommentDeleted::dispatch(
            $comment,
            $request->user(),
            $request->ip(),
            mb_strlen((string) $comment->comment)
        );

        $comment->delete();

        // Teavitus kasutajale (database)
        if($user && $post) {
            $user->notify(new CommentModerated(
                post: $post,
                action: 'deleted',
                moderatorName: $request->user()?->name ?? 'Admin'
            ));

        }

    }
}
