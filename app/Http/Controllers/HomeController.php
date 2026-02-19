<?php

namespace App\Http\Controllers;

use App\Services\DashboardStats;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request, DashboardStats $stats)
    {
        $user = $request->user();

        $isAdmin = method_exists($user, 'hasAccess') && $user->hasAccess('platform.index');


        return view('home', [
            'isAdmin' => $isAdmin,
            'userStats' => $stats->forUser($user),
            'adminStats' => $isAdmin ? $stats->forAdmin() : null,
        ]);
    }
}
