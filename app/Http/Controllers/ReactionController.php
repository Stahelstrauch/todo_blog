<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use App\Models\Setting;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function store(Request $request, Post $post) {
        if(! (bool) Setting::get('reactions.enabled', 1)) {
            return back()->withErrors(['reaction' => 'Hetkel ei saa reageerida!']);
        }

        $data = $request->validate([
            'value' => ['required', 'integer', 'in:1,2,3'],

        ]);

        $allowChange = (bool) Setting::get('reactions.allow_change', 1);
        $cooldownMin = (int) Setting::get('reactions.cooldown_minutes', 60);

        $reaction = Reaction::where('post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if(! $reaction) {
            Reaction::create([
                'post_id' => $post->id,
                'user_id' => $request->user()->id,
                'value' => $data['value'],
            ]);

            return back()->with('success', 'Reaktsioon lisatud!');

        }    

        // Kui sama väärtus, siis ei muuda midagi ja ei kontrolli cooldowni
        if((int) $reaction->value === (int) $data['value']) {
            return back(); // või back()->with('info', 'Juba valitud')

        }
        if(! $allowChange) {
            return back()->withErrors(['reaction' => 'Reageerida saab ainult korra!']);
        }

        if($cooldownMin > 0) {
            $minutesSince = $reaction->updated_at->diffInMinutes(now());
            if($minutesSince < $cooldownMin) {
                $left = $cooldownMin - $minutesSince;
                return back()->withErrors(['reaction' => "Reaktsiooni saab muuta pärast {$left} minutit."]);
            }
        }

        $reaction->value = (int) $data['value'];
        $reaction->save();

        return back()->with('success', 'Reaktsioon uuendatud!');

    }
}
