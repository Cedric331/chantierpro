<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\RedirectResponse;

class MarkReadController extends Controller
{
    public function __invoke(Request $request, DatabaseNotification $notification): RedirectResponse
    {
        $user = $request->user();

        if ((string) $notification->notifiable_id !== (string) $user->id) {
            abort(403);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return back();
    }
}

