<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Todo;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardStats {
    public const REACTION_LABELS = [
        1 => 'Kurb',
        2 => 'Meeldib',
        3 => 'Süda',
    ];

    // Statistika konkreetsele kasutajale (mitte admin)

    public function forUser(User $user): array {
        $commentQuery = Comment::query()->where('user_id', $user->id);
        $reactionQuery = Reaction::query()->where('user_id', $user->id);

        $commentsTotal = (clone $commentQuery)->count();
        $commentsHidden = (clone $commentQuery)->where('is_hidden', true)->count();
        $commentsVisible = $commentsTotal - $commentsHidden;

        $firstCommentAt = (clone $commentQuery)->oldest('created_at')->value('created_at');
        $lastCommentAt = (clone $commentQuery)->latest('created_at')->value('created_at');

        $reactionsTotal = (clone $reactionQuery)->count();
        $firstReactionAt = (clone $reactionQuery)->oldest('created_at')->value('created_at');
        $lastReactionAt = (clone $reactionQuery)->latest('created_at')->value('created_at');

        $reactionByValue = (clone $reactionQuery)
            ->selectRaw('value, COUNT(*) as cnt')
            ->groupBy('value')
            ->pluck('cnt', 'value')
            ->toArray();

        // Viimased kommentaarid (koos postitusega)
        $recentComments = Comment::query()
            ->with(['post:id,title,slug'])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit(5)
            ->get();

        // Viimased reaktsioonid (koos postitusega)
        $recentReactions = Reaction::query()
            ->with(['post:id,title,slug'])
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit(5)
            ->get();    
            
            


        return [
            'comments' => [
                'total' => $commentsTotal,
                'visible' => $commentsVisible,
                'hidden' => $commentsHidden,
                'first_at' => $this->asCarbon($firstCommentAt),
                'last_at' => $this->asCarbon($lastCommentAt),
                'recent' => $recentComments,

            ],
            'reactions' => [
                'total' => $reactionsTotal,
                'by_value' => $this->normalizeReactionCounts($reactionByValue),
                'first_at' => $this->asCarbon($firstReactionAt),
                'last_at' => $this->asCarbon($lastReactionAt),
                'recent' => $recentReactions,
                'labels' => self::REACTION_LABELS,
            ]
        ];
    }
    // Admin ülevaade kogu saidi kohta
    public function forAdmin(): array {
        $postsTotal = Post::query()->count();
        $postsDraft = Post::query()->whereNull('published_at')->count();

        $postsScheduled = Post::query()
            ->whereNotNull('published_at')
            ->where('published_at', '>', now())
            ->count();

        $postsPublished = Post::query()
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->count();
            
        $commentsTotal = Comment::query()->count();
        $commentsHidden = Comment::query()->where('is_hidden', true)->count();
        $commentsVisible = $commentsTotal -$commentsHidden;
        $lastCommentAt = Comment::query()->latest('created_at')->value('created_at');
        
        $reactionsTotal = Reaction::query()->count();
        $reactionByValue = Reaction::query()
            ->selectRaw('value, COUNT(*) as cnt')
            ->groupBy('value')
            ->pluck('cnt', 'value')
            ->toArray();

        $lastReactionAt = Reaction::query()->latest('created_at')->value('created_at');

        $todosTotal = Todo::query()->count();
        $todosDone = Todo::query()->where('is_done', true)->count();
        $todosNotDone =$todosTotal - $todosDone;
        $todosDueAt = Todo::query()->latest('due_at')->value('due_at');
        $todosCreatedAt = Todo::query()->latest('created_at')->value('created_at');
        
        return [
            'posts' => [
                'total' => $postsTotal,
                'draft' => $postsDraft,
                'scheduled' => $postsScheduled,
                'published' => $postsPublished,
            ],
            'comments' => [
                'total' => $commentsTotal,
                'visible' => $commentsVisible,
                'hidden' => $commentsHidden,
                'last_at' => $this->asCarbon($lastCommentAt),
            ],
            'reactions' => [
                'total' => $reactionsTotal,
                'by_value' => $this->normalizeReactionCounts($reactionByValue),
                'last_at' => $this->asCarbon($lastReactionAt),
                'labels' => self::REACTION_LABELS,
            ],
            'todos' => [
                'total' => $todosTotal,
                'done' => $todosDone,
                'not_done' => $todosNotDone,
                'due_at' => $this->asCarbon($todosDueAt),
                'created_at' => $this->asCarbon($todosCreatedAt),
            ]
        ];
    }

    private function normalizeReactionCounts(array $counts):array {
        // Tagame et kõik 1...3 võtmed oleks olemas
        $out = [1 => 0, 2 => 0, 3 => 0];
        foreach($counts as $value => $cnt) {
            $value = (int)$value;
            if(isset($out[$value])) {
                $out[$value] = (int)$cnt;

            }
        }

        return $out;
    }
    private function asCarbon($value): ?Carbon {
        if(! $value) {
            return null;
        }
        return $value instanceof Carbon ? $value : Carbon::parse($value);
    }


}
