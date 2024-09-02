<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix("sessions")->group(function () {
    Route::get("/{session_id}", [AuthController::class, 'getSessionAndUser']);
    Route::post("/", [AuthController::class, 'setSession']);
    Route::patch("/{session_id}", [AuthController::class, 'updateSessionExpiration']);
    Route::delete("/{session_id}", [AuthController::class, 'deleteSession']);
    Route::delete("/{user_id}/sessions", [AuthController::class, 'deleteUserSessions']);
    Route::delete("/expired", [AuthController::class, 'deleteExpiredSessions']);
});

Route::prefix("users")->group(function () {
    Route::get("/{user_id}/sessions", [AuthController::class, 'getUserSessions']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/user', [AuthController::class, 'user']);
