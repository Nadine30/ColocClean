<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Marquer une notification comme lue.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->unreadNotifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        $url = $notification->data['url'] ?? route('colocations.index');

        return redirect($url);
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }
}
