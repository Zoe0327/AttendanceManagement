<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 一般ユーザー
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;


/* 勤怠 */

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('user.attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('user.attendance.start');
    Route::post('/attendance/break/start', [AttendanceController::class, 'startBreak'])->name('user.attendance.break.start');
    Route::post('/attendance/break/end', [AttendanceController::class, 'endBreak'])->name('user.attendance.break.end');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('user.attendance.end');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('user.attendance.list'); // 一覧
    Route::get('/attendance/{attendance}', [AttendanceController::class, 'show'])->name('user.attendance.show'); // 詳細
    Route::post('/attendance/{attendance}/correction', [AttendanceController::class, 'storeCorrection'])->name('user.attendance.correction.store'); //勤怠修正申請
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])->name('user.request.index');// 一般ユーザーの打刻修正
});


/*
|--------------------------------------------------------------------------
| 管理者
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\CorrectionRequestController as AdminCorrectionRequestController;

/* 管理者ログイン */

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::middleware('admin.auth')->prefix('admin')->name('admin.')->group(function () {

    /* 勤怠一覧 */
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('attendance.list');

    /* 勤怠詳細 */
    Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'show'])
        ->name('attendance.show');
    Route::put('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])
        ->name('attendance.update');

    /* 申請一覧 */
    Route::get('/request/list', [AdminCorrectionRequestController::class, 'index'])
        ->name('request.index');

    /* 申請承認 */
    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}',[AdminCorrectionRequestController::class, 'show'])->name('request.show');
    Route::put('stamp_correction_request/approve/{id}', [AdminCorrectionRequestController::class, 'approve'])->name('request.approve');

    /* スタッフ一覧 */
    Route::get('/staff/list', [StaffController::class, 'index'])
        ->name('staff.list');

    /* スタッフ別勤怠一覧 */
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staff'])
        ->name('attendance.staff');
    });


