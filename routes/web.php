<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\GabutController;
use Illuminate\Support\Facades\Route;

// Route::get('/', [UserController::class, 'hourlyTasks']);
Route::get('form', [UserController::class, 'form']);
Route::post('upload', [UserController::class, 'upload'])->name('upl');
Route::get('page', [GabutController::class, 'page']);