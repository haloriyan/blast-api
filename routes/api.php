<?php

use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => "user"], function () {
    Route::post('login', [UserController::class, 'login']);
    Route::post('dashboard', [UserController::class, 'dashboard']);
    Route::post('onboarding', [UserController::class, 'onboarding']);
    Route::post('auth', [UserController::class, 'auth']);
    Route::post('update', [UserController::class, 'update']);
    Route::post('device', [DeviceController::class, 'mine']);
    Route::post('group', [UserController::class, 'group']);
    Route::post('upgrade', [UserController::class, 'upgrade']);
    Route::post('upgrade-history', [UserController::class, 'upgradeHistory']);
});

Route::group(['prefix' => "admin"], function () {
    Route::post('login', [AdminController::class, 'login']);
    Route::post('dashboard', [AdminController::class, 'dashboard']);
    Route::post('contact', [AdminController::class, 'contact']);
    Route::post('purchase', [AdminController::class, 'purchase']);
    Route::post('broadcast', [AdminController::class, 'broadcast']);
    Route::post('purchase/make-paid', [AdminController::class, 'makePurchasePaid']);
    Route::post('user', [AdminController::class, 'users']);

    Route::group(['prefix' => "admin"], function () {
        Route::post('store', [AdminController::class, 'adminStore']);
        Route::post('delete', [AdminController::class, 'adminDelete']);
        Route::post('change-pass', [AdminController::class, 'adminChangePass']);
        Route::post('/', [AdminController::class, 'admin']);
    });
    
    Route::group(['prefix' => "user/{id}"], function () {
        Route::post('contact', [AdminController::class, 'userContact']);
        Route::post('group', [AdminController::class, 'userGroup']);
        Route::post('device', [AdminController::class, 'userDevice']);
        Route::post('/', [AdminController::class, 'user']);
    });
});

Route::group(['prefix' => "contact"], function () {
    Route::post('store', [ContactController::class, 'store']);
    Route::post('delete', [ContactController::class, 'delete']);
    Route::post('update', [ContactController::class, 'update']);
    Route::post('import', [ContactController::class, 'import']);
    Route::post('/', [UserController::class, 'contact']);
});

Route::group(['prefix' => "group"], function () {
    Route::post('store', [GroupController::class, 'store']);
    Route::post('delete', [GroupController::class, 'delete']);

    Route::group(['prefix' => "{id}"], function () {
        Route::post('add-member', [GroupController::class, 'addMember']);
        Route::post('remove-member', [GroupController::class, 'removeMember']);
        Route::post('change-name', [GroupController::class, 'changeName']);
        Route::post('/', [GroupController::class, 'detail']);
    });
});

Route::group(['prefix' => "device"], function () {
    Route::post('connect', [DeviceController::class, 'connect']);
    Route::post('remove', [DeviceController::class, 'remove']);
});

Route::group(['prefix' => 'broadcast'], function () {
    Route::post('send', [BroadcastController::class, 'send']);
    Route::group(['prefix' => "{id}"], function () {
        Route::post('send-now', [BroadcastController::class, 'sendNow']);
        Route::get('detail', [BroadcastController::class, 'detail']);
        Route::get('log', [BroadcastController::class, 'log']);
    });
    Route::post('/', [UserController::class, 'broadcast']);
});

Route::group(['prefix' => 'notification'], function () {
    Route::post('read', [NotificationController::class, 'read']);
    Route::post('clear', [NotificationController::class, 'clear']);
    Route::post('/', [UserController::class, 'notification']);
});

Route::group(['prefix' => "midtrans"], function () {
    Route::post('notified', [MidtransController::class, 'notified']);
});