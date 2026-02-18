<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index() {
        $posts = Post::query()
            ->published() // scopePublished = Post mudelis
            ->withCount('comments')
            ->withAvg('reactions', 'value') // Uus rida
            ->latest('published_at')
            ->paginate(6);

        return view('welcome', compact('posts'));    
    }

    public function show(string $slug) {
        $post = Post::query()
            ->published() // scopePublished = Post mudelis
            ->where('slug', $slug)
            ->with(['myReaction'])
            ->withCount([
                'reactions as reaction_1_count' => fn ($q) => $q->where('value', 1),
                'reactions as reaction_2_count' => fn ($q) => $q->where('value', 2),
                'reactions as reaction_3_count' => fn ($q) => $q->where('value', 3),
                'reactions as reaction_total' => fn ($q) => $q,
            ])
            ->firstOrFail();
        return view('posts.show', compact('post'));
    }
}
