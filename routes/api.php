<?php

require __DIR__ . '/api/user.php';
require __DIR__ . '/api/admin.php';
require __DIR__ . '/api/staff.php';
require __DIR__ . '/api/public.php';

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;


Route::group(['prefix' => 'auth'], function () {
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

