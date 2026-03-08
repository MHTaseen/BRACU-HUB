<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AcademicCalendarController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'faculty') {
            return redirect()->route('assignments.create');
        }
        return redirect()->route('attendance.student');
    })->name('dashboard');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Shared Routes
    Route::get('/calendar', [AcademicCalendarController::class, 'index'])->name('calendar.index');

    // Faculty Only Routes
    Route::middleware(['role:faculty'])->group(function () {
        Route::get('/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::post('/assignments/check-conflict', [AssignmentController::class, 'checkConflict'])->name('assignments.check_conflict');

        Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    });

    // Student Only Routes
    Route::middleware(['role:student'])->group(function () {
        Route::get('/my-attendance', [AttendanceController::class, 'showStudent'])->name('attendance.student');
    });
});
