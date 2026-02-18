<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Comment extends Model
{
    Use AsSource, Filterable;

    
    protected $fillable = [
        'post_id',
        'user_id',
        'comment',
        'ip_address',
        'is_hidden',
    ];

    protected $casts = [
        'is_hidden' => 'boolean'
    ];

    public function post() {
        return $this->belongsTo(Post::class);
    }

    public function user() {
        return $this->belongsTo(User::class);    
    }

    public function comments() {
        return $this->hasMany(Comment::class);
    }


    // Avalikud kommentaarid
    public function scopeVisible($query) {
        return $query->where('is_hidden', false);
    }
}
