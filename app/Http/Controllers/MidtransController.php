<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MidtransController extends Controller
{
    public static function getBaseUrl() {
        $mode = env('MIDTRANS_MODE');
        return env('MIDTRANS_BASE_URL_' . $mode);
    }
    public static function getKey($type = 'client') {
        $mode = env('MIDTRANS_MODE');
        $type = strtoupper($type);
        return env('MIDTRANS_' . $type . '_KEY_' . $mode);
    }

    public function notified(Request $request) {
        return "ok";
    }
}
