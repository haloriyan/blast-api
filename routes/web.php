<?php

use App\Http\Controllers\BroadcastController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BroadcastController::class, 'tes']);
