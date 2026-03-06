<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CinemaController;
use App\Http\Controllers\ComboController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ShowtimeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// public
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

// private
Route::group(['prefix' => 'auth'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all-devices', [AuthController::class, 'logoutAll']);
        Route::post('logout/{tokenId}/device', [AuthController::class, 'logoutDevice']);
        Route::get('devices', [AuthController::class, 'devices']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });
});

Route::group(['prefix' => 'users'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
    });

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/', [UserController::class, 'index']);
    });
});

Route::group(['prefix' => 'categories'], function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{id}', [CategoryController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
    });
});

Route::group(['prefix' => 'movies'], function () {
    Route::get('/', [MovieController::class, 'index']);
    Route::get('/{id}', [MovieController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [MovieController::class, 'store']);
        Route::put('/{id}', [MovieController::class, 'update']);
        // Route::delete('/{id}', [MovieController::class, 'destroy']);
    });
});

Route::group(['prefix' => 'combos'], function () {
    Route::get('/', [ComboController::class, 'index']);
    Route::get('/{id}', [ComboController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [ComboController::class, 'store']);
        Route::put('/{id}', [ComboController::class, 'update']);
        Route::delete('/{id}', [ComboController::class, 'destroy']);
    });
});

Route::group(['prefix' => 'cinemas'], function () {
    Route::get('/', [CinemaController::class, 'index']);
    Route::get('/{id}', [CinemaController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [CinemaController::class, 'store']);
        Route::put('/{id}', [CinemaController::class, 'update']);
        Route::delete('/{id}', [CinemaController::class, 'destroy']);
    });
});

Route::group(['prefix' => 'showtimes'], function () {
    Route::get('/', [ShowtimeController::class, 'index']);
    Route::get('/{id}', [ShowtimeController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [ShowtimeController::class, 'store']);
        Route::put('/{id}', [ShowtimeController::class, 'update']);
        Route::delete('/{id}', [ShowtimeController::class, 'destroy']);
    });
});
