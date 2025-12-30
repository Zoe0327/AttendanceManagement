<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 一般ユーザー
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;


/* 勤怠 */

Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('user.attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('user.attendance.start');
    Route::post('/attendance/break/start', [AttendanceController::class, 'startBreak'])->name('user.attendance.break.start');
    Route::post('/attendance/break/end', [AttendanceController::class, 'endBreak'])->name('user.attendance.break.end');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('user.attendance.end');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('user.attendance.list'); // 一覧
    Route::get('/attendance/{attendance}', [AttendanceController::class, 'show'])->name('user.attendance.show'); // 詳細
    Route::post('/attendance/detail/{attendance}/correction', [AttendanceController::class, 'storeCorrection'])->name('user.attendance.correction.store'); //勤怠修正申請
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])->name('stamp_correction_request.list');//申請一覧
});


/*
|--------------------------------------------------------------------------
| 管理者
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\CorrectionRequestController;

/* 管理者ログイン */

//Route::get('/admin/login', [AuthController::class, 'login'])->name('admin.attendance.login');

/* 管理者：勤怠 */
//Route::get('/admin/attendance', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
//Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
//Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staff'])->name('admin.attendance.staff');

/* 管理者：スタッフ */
//Route::get('/admin/staff', [StaffController::class, 'index'])->name('admin.staff.index');

/* 管理者：修正申請 */
//Route::get('/admin/request', [CorrectionRequestController::class, 'index'])->name('admin.request.index');
//Route::get(
//    '/admin/request/approve/{id}',
//    [CorrectionRequestController::class, 'approve']
//);
