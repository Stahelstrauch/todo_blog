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
            ->latest('published_at')
            ->paginate(5);

        return view('welcome', compact('posts'));    
    }

    public function show(string $slug) {
        $post = Post::query()
            ->published() // scopePublished = Post mudelis
            ->where('slug', $slug)
            // TODO myreaction
            ->firstOrFail();
        return view('posts.show', compact('post'));
    }
}
