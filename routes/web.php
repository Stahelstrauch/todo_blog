<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/', [PostController::class, 'index'])->name('home');

Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');

// Notificationite route
Route::middleware('auth')->group(function() {
    Route::post('/notifications/{id}/read', function (Request $request, string $id) {
        $n = $request->user()->notifications()->where('id', $id)->firstOrFail();
        $n->markAsRead();
        $url = data_get($n->data, 'public_url', route('home'));
        return redirect($url);
    })->name('notifications.read');

    Route::post('/notifications/read-all', function (Request $request){
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return back();
    })->name('notifications.readAll');

});

Route::post('/posts/{post:slug}/comments', [CommentController::class, 'store'])
    ->middleware('auth')
    ->name('comments.store');

Route::post('/posts/{post:slug}/reactions', [ReactionController::class, 'store'])
    ->middleware('auth')
    ->name('reactions.store');

