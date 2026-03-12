<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CinemaController;
use App\Http\Controllers\CinemaSaleController;
use App\Http\Controllers\ComboController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeRoleController;
use App\Http\Controllers\EmployeeSalaryController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\ShowtimeController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ====== AUTH (Public) ======
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

// ====== AUTH (Private) ======
Route::group(['prefix' => 'auth'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all-devices', [AuthController::class, 'logoutAll']);
        Route::post('logout/{tokenId}/device', [AuthController::class, 'logoutDevice']);
        Route::get('devices', [AuthController::class, 'devices']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
    });
});

// ====== USERS ======
Route::group(['prefix' => 'users'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
    });

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/', [UserController::class, 'index']);
    });
});

// ====== CATEGORIES ======
Route::group(['prefix' => 'categories'], function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{id}', [CategoryController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
    });
});

// ====== MOVIES ======
Route::group(['prefix' => 'movies'], function () {
    Route::get('/', [MovieController::class, 'index']);
    Route::get('/{id}', [MovieController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [MovieController::class, 'store']);
        Route::put('/{id}', [MovieController::class, 'update']);
    });
});

// ====== COMBOS ======
Route::group(['prefix' => 'combos'], function () {
    Route::get('/', [ComboController::class, 'index']);
    Route::get('/{id}', [ComboController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [ComboController::class, 'store']);
        Route::put('/{id}', [ComboController::class, 'update']);
        Route::delete('/{id}', [ComboController::class, 'destroy']);
    });
});

// ====== CINEMAS ======
Route::group(['prefix' => 'cinemas'], function () {
    Route::get('/', [CinemaController::class, 'index']);
    Route::get('/{id}', [CinemaController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [CinemaController::class, 'store']);
        Route::put('/{id}', [CinemaController::class, 'update']);
        Route::delete('/{id}', [CinemaController::class, 'destroy']);
    });
});

// ====== SHOWTIMES ======
Route::group(['prefix' => 'showtimes'], function () {
    Route::get('/', [ShowtimeController::class, 'index']);
    Route::get('/{id}', [ShowtimeController::class, 'show']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [ShowtimeController::class, 'store']);
        Route::put('/{id}', [ShowtimeController::class, 'update']);
        Route::delete('/{id}', [ShowtimeController::class, 'destroy']);
    });
});

// ====== SEATS ======
Route::group(['prefix' => 'seats'], function () {
    Route::get('/', [SeatController::class, 'index']);
    Route::get('/{id}', [SeatController::class, 'show']);
    Route::get('/showtime/{showtimeId}', [SeatController::class, 'getByShowtime']);

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/', [SeatController::class, 'store']);
        Route::post('/bulk', [SeatController::class, 'storeBulk']);
        Route::put('/{id}', [SeatController::class, 'update']);
        Route::delete('/{id}', [SeatController::class, 'destroy']);
    });
});

// ====== TICKETS (Đặt vé) ======
Route::group(['prefix' => 'tickets'], function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/my-tickets', [TicketController::class, 'myTickets']);
        Route::post('/book', [TicketController::class, 'book']);
        Route::post('/{id}/cancel', [TicketController::class, 'cancel']);
    });

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/{id}', [TicketController::class, 'show']);
        Route::post('/{id}/confirm-payment', [TicketController::class, 'confirmPayment']);
    });
});

// ====== PAYMENTS ======
Route::group(['prefix' => 'payments'], function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/{id}', [PaymentController::class, 'show']);
    });
});

// ====== EMPLOYEE ROLES ======
Route::group(['prefix' => 'employee-roles'], function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/', [EmployeeRoleController::class, 'index']);
        Route::get('/{id}', [EmployeeRoleController::class, 'show']);
        Route::post('/', [EmployeeRoleController::class, 'store']);
        Route::put('/{id}', [EmployeeRoleController::class, 'update']);
        Route::delete('/{id}', [EmployeeRoleController::class, 'destroy']);
    });
});

// ====== EMPLOYEES ======
Route::group(['prefix' => 'employees'], function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);
        Route::get('/{id}', [EmployeeController::class, 'show']);
        Route::post('/', [EmployeeController::class, 'store']);
        Route::put('/{id}', [EmployeeController::class, 'update']);
        Route::delete('/{id}', [EmployeeController::class, 'destroy']);
    });
});

// ====== EMPLOYEE SALARIES ======
Route::group(['prefix' => 'employee-salaries'], function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/', [EmployeeSalaryController::class, 'index']);
        Route::get('/{id}', [EmployeeSalaryController::class, 'show']);
        Route::post('/', [EmployeeSalaryController::class, 'store']);
        Route::put('/{id}', [EmployeeSalaryController::class, 'update']);
        Route::delete('/{id}', [EmployeeSalaryController::class, 'destroy']);
    });
});

// ====== CINEMA SALES ======
Route::group(['prefix' => 'cinema-sales'], function () {
    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::get('/', [CinemaSaleController::class, 'index']);
        Route::get('/{id}', [CinemaSaleController::class, 'show']);
        Route::post('/', [CinemaSaleController::class, 'store']);
        Route::put('/{id}', [CinemaSaleController::class, 'update']);
        Route::delete('/{id}', [CinemaSaleController::class, 'destroy']);
    });
});
