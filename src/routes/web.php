<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminLoginController;


Route::middleware(['auth','verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'attendance_register_user']);
    Route::get('/attendance/list', [AttendanceController::class, 'attendance_list_user']);
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'application_list_user']);
    Route::get('/attendance/{id}', [AttendanceController::class, 'attendance_detail_user']);
    Route::get('/d', [AttendanceController::class, 'd']);
    Route::post('/save-attendance', [AttendanceController::class, 'saveAttendance']);
    Route::get('/get-current-status', [AttendanceController::class, 'getCurrentStatus']);
    Route::get('/attendances/list', [AttendanceController::class, 'getAttendances']);
});


Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');



Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', function () {
        return view('attendance_list_admin');
    });
});
