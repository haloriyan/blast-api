<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function read(Request $request) {
        $data = UserNotification::where('id', $request->id);
        $data->update(['has_read' => true]);

        return response()->json(['ok']);
    }
    public function clear(Request $request) {
        $user = User::where('token', $request->token)->first();
        UserNotification::where('user_id', $user->id)->delete();
    }
}
