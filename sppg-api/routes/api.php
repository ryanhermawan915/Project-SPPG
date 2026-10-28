<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Admin\EmployeeApprovalController;
use App\Http\Controllers\Api\V1\Admin\EmployeeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1/auth')->group(function () {
    // Endpoint publik
    Route::post('/register', [
        AuthController::class,
        'register',
    ]);

    Route::post('/login', [
        AuthController::class,
        'login',
    ]);

    // Endpoint yang hanya bisa digunakan setelah login
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [
            AuthController::class,
            'me',
        ]);

        Route::post('/logout', [
            AuthController::class,
            'logout',
        ]);
    });
});

Route::prefix('v1/admin')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get(
            '/employees/pending',
            [EmployeeApprovalController::class, 'index']
        );

        Route::post(
            '/employees/{userId}/approve',
            [EmployeeApprovalController::class, 'approve']
        );

        Route::post(
            '/employees/{userId}/reject',
            [EmployeeApprovalController::class, 'reject']
        );

        Route::get(
        '/employees',
        [EmployeeController::class, 'index']
        );

        Route::get(
        '/employees/{userId}',
        [EmployeeController::class, 'show']
        );

        Route::put(
        '/employees/{userId}',
        [EmployeeController::class, 'update']
        );

    });

    