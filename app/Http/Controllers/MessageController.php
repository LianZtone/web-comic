<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = collect();

        if (Schema::hasTable('notifications')) {
            $user = $request->user();
            $user->unreadNotifications->markAsRead();
            $notifications = $user->notifications()->latest()->limit(50)->get();
        }

        return view('messages.index', [
            'notifications' => $notifications,
        ]);
    }
}
