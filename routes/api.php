<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// test role and permission (employee, admin, customer)
Route::middleware(['auth:sanctum', 'role:admin'])->get('demo/admin', function () {
    return response()->json([
        'success' => true,
        'message' => 'Admin truy cập được',
    ]);
});

Route::middleware(['auth:sanctum', 'role:employee,admin'])->get('demo/employee', function () {
    return response()->json([
        'success' => true,
        'message' => 'Employee và admin truy cập được',
    ]);
});

Route::middleware(['auth:sanctum', 'role:customer'])->get('demo/customer', function () {
    return response()->json([
        'success' => true,
        'message' => 'Customer truy cập được',
    ]);
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('logout-all-devices', [AuthController::class, 'logoutAll'])->middleware('auth:sanctum');
    Route::post('logout/{deviceName}/device', [AuthController::class, 'logoutDevice'])->middleware('auth:sanctum');
    Route::get('devices', [AuthController::class, 'getAllUserLoginDevices'])->middleware('auth:sanctum');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('change-password', [AuthController::class, 'changePassword'])->middleware('auth:sanctum');
});
