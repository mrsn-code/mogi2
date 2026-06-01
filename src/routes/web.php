<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\StaffController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
});

Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::post('/email/verification-notification', function (Request $request) {
    session()->get('unauthenticated_user')->sendEmailVerificationNotification();
    session()->put('resent', true);
    return back()->with('message', 'Verification link sent!');
})->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

// Route::get('/', function() {
//     return view('welcome');
// });

Route::middleware('auth')->group(function () {
    Route::get('/', [AttendanceController::class, 'index']);
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
        ->name('attendance.clockIn');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])
        ->name('attendance.breakIn');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])
        ->name('attendance.breakOut');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
        ->name('attendance.clockOut');
    
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    Route::get('/attendance/details/{id}', [AttendanceController::class, 'details'])
        ->name('attendance.details');
    Route::patch('/attendance/details/{id}', [AttendanceController::class, 'update'])
        ->name('attendance.update');

    // 修正申請の登録
    Route::post('/attendance/details/{id}/request', [AttendanceRequestController::class, 'store'])
        ->name('attendance.request.store');
    // ユーザー側の申請一覧
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])
        ->name('attendance.request.index');
    Route::get('/stamp_correction_request/{id}', [AttendanceRequestController::class, 'show'])
        ->name('attendance.request.show');

    Route::post('/stamp_correction_request/{id}/approve', [AttendanceRequestController::class, 'approve'])
        ->name('attendance.request.approve');
});

Route::get('/admin/login', [AdminLoginController::class, 'create'])
    ->middleware('guest')
    ->name('admin.login');

Route::post('/admin/login', [AdminLoginController::class, 'store'])
    ->middleware('guest')
    ->name('admin.login.store');

Route::post('/admin/logout', [AdminLoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('admin.logout');

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
            ->name('attendance.list');
        Route::get('/attendance/details/{id}', [AdminAttendanceController::class, 'details'])
            ->name('attendance.details');
        
        Route::get('/staff/list', [StaffController::class, 'index'])
            ->name('staff.list');
        Route::get('/attendance/staff/{id}', [StaffController::class, 'attendanceList'])
            ->name('attendance.staff');

        Route::get('/attendance/details/{id}', [AdminAttendanceController::class, 'details'])
            ->name('attendance.details');
    });

