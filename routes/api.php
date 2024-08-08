<?php

use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => "user"], function () {
    Route::post('login', [UserController::class, 'login']);
    Route::post('dashboard', [UserController::class, 'dashboard']);
    Route::post('device', [DeviceController::class, 'mine']);
    Route::post('group', [UserController::class, 'group']);
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
    Route::get('{id}/detail', [BroadcastController::class, 'detail']);
    Route::get('{id}/log', [BroadcastController::class, 'log']);
    Route::post('/', [UserController::class, 'broadcast']);
});

Route::group(['prefix' => 'notification'], function () {
    Route::post('read', [NotificationController::class, 'read']);
    Route::post('clear', [NotificationController::class, 'clear']);
    Route::post('/', [UserController::class, 'notification']);
});