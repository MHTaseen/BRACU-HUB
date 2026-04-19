<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AcademicCalendarController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AcademicAssistantController;
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'faculty') {
            return redirect()->route('assignments.create');
        }
        return redirect()->route('student.tracker');
    })->name('dashboard');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Shared Routes
    Route::get('/calendar', [AcademicCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/concept-map', [App\Http\Controllers\ConceptMapController::class, 'index'])->name('concept-map.index');
    Route::post('/notifications/mark-read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.mark-read');

    Route::delete('/notifications/{id}/dismiss', function (string $id) {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();
        return response()->json(['success' => true]);
    })->name('notifications.dismiss');

    // Download material (shared between faculty and student)
    Route::get('/materials/{material}/download', [App\Http\Controllers\CourseMaterialController::class, 'download'])->name('materials.download');

    // Faculty Only Routes
    Route::middleware(['role:faculty'])->group(function () {
        Route::get('/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::post('/assignments/check-conflict', [AssignmentController::class, 'checkConflict'])->name('assignments.check_conflict');
        Route::get('/assignments/{assignment}/submissions', [App\Http\Controllers\AssignmentSubmissionController::class, 'indexForFaculty'])->name('faculty.submissions.index');
        Route::patch('/submissions/{submission}/grade', [App\Http\Controllers\AssignmentSubmissionController::class, 'grade'])->name('submissions.grade');

        Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

        // Course Management
        Route::get('/courses', [App\Http\Controllers\CourseController::class, 'index'])->name('courses.index');
        Route::get('/courses/create', [App\Http\Controllers\CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [App\Http\Controllers\CourseController::class, 'store'])->name('courses.store');

        // Section Management
        Route::get('/sections/create', [App\Http\Controllers\SectionController::class, 'create'])->name('sections.create');
        Route::post('/sections', [App\Http\Controllers\SectionController::class, 'store'])->name('sections.store');
        Route::get('/sections/{section}/manage', [App\Http\Controllers\SectionController::class, 'manage'])->name('sections.manage');
        Route::post('/sections/{section}/enroll', [App\Http\Controllers\SectionController::class, 'addStudent'])->name('sections.enroll');
        
        // Course Materials Management
        Route::post('/sections/{section}/materials', [App\Http\Controllers\CourseMaterialController::class, 'store'])->name('materials.store');
        Route::delete('/materials/{material}', [App\Http\Controllers\CourseMaterialController::class, 'destroy'])->name('materials.destroy');
    });

    // Student Only Routes
    Route::middleware(['role:student'])->group(function () {
        Route::get('/academic-tracker', [App\Http\Controllers\StudentDashboardController::class, 'index'])->name('student.tracker');
        Route::get('/submissions/{assignment}/create', [App\Http\Controllers\AssignmentSubmissionController::class, 'create'])->name('submissions.create');
        Route::post('/submissions/{assignment}', [App\Http\Controllers\AssignmentSubmissionController::class, 'store'])->name('submissions.store');
        Route::get('/submissions/{submission}/show', [App\Http\Controllers\AssignmentSubmissionController::class, 'show'])->name('submissions.show');
        Route::get('/revision-planner', [App\Http\Controllers\RevisionPlannerController::class, 'index'])->name('revision.index');
        Route::get('/assistant', [AcademicAssistantController::class, 'index'])->name('assistant.index');
        Route::post('/assistant/ask', [AcademicAssistantController::class, 'ask'])->name('assistant.ask');
        
        // Course Materials
        Route::get('/repository', [App\Http\Controllers\CourseMaterialController::class, 'repository'])->name('materials.repository');
    });
});
