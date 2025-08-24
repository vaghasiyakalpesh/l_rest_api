<?php

use App\Http\Controllers\Api\V1\AuthenticationController;
use App\Http\Controllers\Api\V1\BookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', 'throttle:custom-booking-limit'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::apiResource('bookings', BookingController::class);
    });
});
Route::post('v1/register', [AuthenticationController::class, 'register'])->name('register');
Route::post('v1/login', [AuthenticationController::class, 'login'])->name('login');
Route::post('v1/revoke-token', [AuthenticationController::class, 'revokeToken'])->name('revoke.token');