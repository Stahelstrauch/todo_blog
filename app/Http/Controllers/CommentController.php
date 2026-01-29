<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class CommentController extends Controller
{
    public function store(Request $request, Post $post) {
        // Kommentaarid sees või väljas
        if(! (bool) Setting::get('comments.enabled', true)) {
            abort(403, 'Kommenteerimine on välja lülitatud.');
        }

        // Globaalne piirang: 1 kommentaar kasutaja kohta x minuti tagant
        $minutes = max(1, (int) Setting::get('comments.user_cooldown_minutes', 5));
        $key ='comments:user:' . $request->user()->id;

        if(RateLimiter::tooManyAttempts($key, 1)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'comment' => "Saad uuesti kommenteerida umbes ($seconds) sek pärast.",
            ]);
        }
        RateLimiter::hit($key, $minutes * 60);

        //Valideerimine
        $data = $request->validate([
            'comment' => ['required', 'string', 'min:3']
        ]);

        //Salvesta (sinu tabeli struktuur)
        Comment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'comment' => $data['comment'],
            'is_hidden' => 0,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Kommentaar lisatud!');
    }
}
