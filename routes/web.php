<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AcademicCalendarController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AcademicAssistantController;

Route::get('/setup-database', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    return 'Database migrated and seeded successfully!';
});

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
    Route::get('/study-rooms', [App\Http\Controllers\StudyRoomController::class, 'index'])->name('study-rooms.index');
    Route::get('/study-rooms/create', [App\Http\Controllers\StudyRoomController::class, 'create'])->name('study-rooms.create');
    Route::post('/study-rooms', [App\Http\Controllers\StudyRoomController::class, 'store'])->name('study-rooms.store');
    Route::get('/study-rooms/{studyRoom}', [App\Http\Controllers\StudyRoomController::class, 'show'])->name('study-rooms.show');
    Route::post('/study-rooms/{studyRoom}/leave', [App\Http\Controllers\StudyRoomController::class, 'leave'])->name('study-rooms.leave');
    Route::post('/study-rooms/{studyRoom}/archive', [App\Http\Controllers\StudyRoomController::class, 'archive'])->name('study-rooms.archive');
    Route::post('/study-rooms/{studyRoom}/notes', [App\Http\Controllers\StudyRoomController::class, 'updateNotes'])->name('study-rooms.update-notes');
    Route::post('/study-rooms/{studyRoom}/whiteboard', [App\Http\Controllers\StudyRoomController::class, 'updateWhiteboard'])->name('study-rooms.update-whiteboard');
    Route::get('/study-rooms/{studyRoom}/updates', [App\Http\Controllers\StudyRoomController::class, 'getUpdates'])->name('study-rooms.get-updates');
    Route::post('/study-rooms/{studyRoom}/message', [App\Http\Controllers\StudyRoomController::class, 'sendMessage'])->name('study-rooms.send-message');
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
    Route::get('/submissions/{submission}/download', [App\Http\Controllers\AssignmentSubmissionController::class, 'download'])->name('submissions.download');

    // Faculty Only Routes
    Route::middleware(['role:faculty'])->group(function () {
        Route::get('/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::post('/assignments/check-conflict', [AssignmentController::class, 'checkConflict'])->name('assignments.check_conflict');
        Route::get('/assignments/{assignment}/submissions', [App\Http\Controllers\AssignmentSubmissionController::class, 'indexForFaculty'])->name('faculty.submissions.index');
        Route::post('/submissions/{submission}/marks', [App\Http\Controllers\AssignmentSubmissionController::class, 'updateMarks'])->name('faculty.submissions.marks');
        Route::post('/assignments/{assignment}/send-reminder', [AssignmentController::class, 'sendManualReminder'])->name('assignments.send_reminder');
        Route::patch('/submissions/{submission}/grade', [App\Http\Controllers\AssignmentSubmissionController::class, 'grade'])->name('submissions.grade');

        Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

        // Quiz Grading (Sadman — Module 2 Revision Planner feature)
        Route::get('/quiz-grades', [App\Http\Controllers\QuizGradeController::class, 'index'])->name('quiz.grades.index');
        Route::post('/assignments/{assignment}/grade', [App\Http\Controllers\QuizGradeController::class, 'store'])->name('quiz.grades.store');

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
        Route::get('/wiki-summary', [App\Http\Controllers\RevisionPlannerController::class, 'wikiSummary'])->name('wiki.summary');
        Route::get('/assistant', [AcademicAssistantController::class, 'index'])->name('assistant.index');
        Route::get('/peer-suggestions', [App\Http\Controllers\PeerSuggestionController::class, 'index'])->name('peer.suggestions');
        Route::post('/assistant/ask', [AcademicAssistantController::class, 'ask'])->name('assistant.ask');
        
        // Course Materials
        Route::get('/repository', [App\Http\Controllers\CourseMaterialController::class, 'repository'])->name('materials.repository');
    });
});
