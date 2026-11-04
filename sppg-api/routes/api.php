
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\LeaveRequestController;
use App\Http\Controllers\Api\V1\DashboardController;

use App\Http\Controllers\Api\V1\Admin\EmployeeApprovalController;
use App\Http\Controllers\Api\V1\Admin\EmployeeController;
use App\Http\Controllers\Api\V1\Admin\WorkScheduleController;
use App\Http\Controllers\Api\V1\Admin\AcademicPeriodController;
use App\Http\Controllers\Api\V1\Admin\SchoolLocationController;
use App\Http\Controllers\Api\V1\Admin\LeaveRequestController as AdminLeaveRequestController;


// Endpoint user
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'password.changed']);


// AUTHENTICATION
Route::prefix('v1/auth')->group(function () {
    // Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});


// ADMIN
Route::prefix('v1/admin')
    ->middleware(['auth:sanctum', 'password.changed'])
    ->group(function () {

        // Employee management
        Route::post('/employees', [EmployeeController::class, 'store']);
        
        Route::get('/employees/pending', [EmployeeApprovalController::class, 'index']);
        Route::post('/employees/{userId}/approve', [EmployeeApprovalController::class, 'approve']);
        Route::post('/employees/{userId}/reject', [EmployeeApprovalController::class, 'reject']);

        Route::get('/employees', [EmployeeController::class, 'index']);
        Route::get('/employees/{userId}', [EmployeeController::class, 'show']);
        Route::put('/employees/{userId}', [EmployeeController::class, 'update']);
        Route::patch('/employees/{userId}/deactivate', [EmployeeController::class, 'deactivate']);
        Route::patch('/employees/{userId}/activate', [EmployeeController::class, 'activate']);

        // Work schedules
        Route::get('/work-schedules', [WorkScheduleController::class, 'index']);
        Route::post('/work-schedules', [WorkScheduleController::class, 'store']);
        Route::get('/work-schedules/{id}', [WorkScheduleController::class, 'show']);
        Route::put('/work-schedules/{id}', [WorkScheduleController::class, 'update']);
        Route::patch('/work-schedules/{id}/activate', [WorkScheduleController::class, 'activate']);
        Route::patch('/work-schedules/{id}/deactivate', [WorkScheduleController::class, 'deactivate']);

        // Academic periods
        Route::get('/academic-periods', [AcademicPeriodController::class, 'index']);
        Route::post('/academic-periods', [AcademicPeriodController::class, 'store']);
        Route::get('/academic-periods/{id}', [AcademicPeriodController::class, 'show']);
        Route::put('/academic-periods/{id}', [AcademicPeriodController::class, 'update']);
        Route::patch('/academic-periods/{id}/activate', [AcademicPeriodController::class, 'activate']);
        Route::patch('/academic-periods/{id}/deactivate', [AcademicPeriodController::class, 'deactivate']);

        // School location
        Route::get('/school-location', [SchoolLocationController::class, 'show']);
        Route::put('/school-location', [SchoolLocationController::class, 'update']);

        // Admin leave requests
        Route::prefix('leave-requests')->group(function () {
            Route::get('/', [AdminLeaveRequestController::class, 'index']);
            Route::get('/{id}', [AdminLeaveRequestController::class, 'show']);
            Route::patch('/{id}/approve', [AdminLeaveRequestController::class, 'approve']);
            Route::patch('/{id}/reject', [AdminLeaveRequestController::class, 'reject']);
        });

    });


// ATTENDANCE (EMPLOYEE)
Route::prefix('v1/attendance')
    ->middleware(['auth:sanctum', 'password.changed'])
    ->group(function () {

        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::get('/history', [AttendanceController::class, 'history']);

    });


// LEAVE REQUESTS (EMPLOYEE)
Route::prefix('v1/leave-requests')
    ->middleware(['auth:sanctum', 'password.changed'])
    ->group(function () {

        Route::post('/', [LeaveRequestController::class, 'store']);
        Route::get('/', [LeaveRequestController::class, 'index']);
        Route::get('/{id}', [LeaveRequestController::class, 'show']);
        Route::get('/{id}/attachment', [LeaveRequestController::class, 'downloadAttachment']);

    });


// DASHBOARD (EMPLOYEE)
Route::middleware(['auth:sanctum', 'password.changed'])->group(function () {
    // Dashboard utama employee
    Route::get('/v1/dashboard', [DashboardController::class, 'index']);

    // Statistik presensi bulanan employee
    Route::get(
        '/v1/dashboard/monthly-attendance',
        [DashboardController::class, 'monthlyAttendance']
    );
});
