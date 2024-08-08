<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    public function mine(Request $request) {
        $user = User::where('token', $request->token)->first();
        $devices = UserDevice::where('user_id', $user->id)->get();

        return response()->json([
            'devices' => $devices,
        ]);
    }
    public function connect(Request $request) {
        $user = User::where('token', $request->token)->first();
        
        $saveData = UserDevice::create([
            'user_id' => $user->id,
            'label' => $request->label,
            'client_id' => $request->client_id,
            'number' => $request->number,
        ]);

        return response()->json([
            'ok'
        ]);
    }
    public function remove(Request $request) {
        $dev = UserDevice::where('id', $request->id);
        $rem = $dev->delete();

        return response()->json([
            'ok'
        ]);
    }
}
