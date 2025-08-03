<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminLoginController;


Route::get('/stamp_correction_request/list', [AttendanceController::class, 'application_list'])->middleware('auth');


Route::middleware(['auth','verified', 'role:user'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'attendance_register_user']);

    Route::get('/get-current-status', [AttendanceController::class, 'getCurrentStatus']);

    Route::get('/attendances/list', [AttendanceController::class, 'getAttendances']);

    Route::get('/attendance/list', [AttendanceController::class, 'attendance_list_user']);

    Route::post('/save-attendance', [AttendanceController::class, 'saveAttendance']);

    Route::post('attendance/list', [AttendanceController::class, 'store']);

    Route::post('/user_attendance/update', [AttendanceController::class, 'update'])->name('user.attendance.update');
});


Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');

Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/attendance/list',[AttendanceController::class, 'attendance_list_admin']);

    Route::get('/api/attendance', [AttendanceController::class, 'getAttendance']);

    Route::get('/admin/attendance/{date}', [AttendanceController::class, 'attendance_for_date'])->name('attendance_for_date');

    Route::get('/admin/staff/list', [AttendanceController::class, 'staff_list_admin']);

    Route::get('/admin/attendance/staff/{id}', [AttendanceController::class, 'attendances_by_staff_admin']);

    Route::get('/stamp_correction_request/approve/{id}', [AttendanceController::class, 'showApplicationDetails'])->name('application.details');

    Route::post('/stamp_correction_request/approve/{id}', [AttendanceController::class, 'approve'])->name('user.attendance.approve');

    Route::post('/attendance/{id}', [AttendanceController::class, 'save'])->name('attendance.save');
});

Route::post('/logout', [AttendanceController::class, 'logout']);

Route::get('/attendance/{id}', [AttendanceController::class, 'attendance_detail'])->middleware('auth');