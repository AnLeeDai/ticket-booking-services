<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'admin-middleware'])->group(function () {
    Route::get('users', fn() => response()->json(['message' => 'Welcome to Admin Dashboard']));
});
