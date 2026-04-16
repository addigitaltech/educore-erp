<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ExamAttemptController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TimetableController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
    });

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Schools (Super Admin only)
    Route::prefix('schools')->group(function () {
        Route::get('/', [SchoolController::class, 'index']);
        Route::post('/', [SchoolController::class, 'store']);
        Route::get('/{id}', [SchoolController::class, 'show']);
        Route::put('/{id}', [SchoolController::class, 'update']);
        Route::delete('/{id}', [SchoolController::class, 'destroy']);
        Route::get('/{id}/stats', [SchoolController::class, 'stats']);
    });

    // Students
    Route::prefix('students')->group(function () {
        Route::get('/', [StudentController::class, 'index']);
        Route::post('/', [StudentController::class, 'store']);
        Route::get('/{id}', [StudentController::class, 'show']);
        Route::put('/{id}', [StudentController::class, 'update']);
        Route::delete('/{id}', [StudentController::class, 'destroy']);
        Route::get('/{id}/results', [StudentController::class, 'results']);
        Route::get('/{id}/attendance', [StudentController::class, 'attendance']);
        Route::get('/{id}/report-card', [StudentController::class, 'reportCard']);
    });

    // Teachers
    Route::prefix('teachers')->group(function () {
        Route::get('/', [TeacherController::class, 'index']);
        Route::post('/', [TeacherController::class, 'store']);
        Route::get('/{id}', [TeacherController::class, 'show']);
        Route::put('/{id}', [TeacherController::class, 'update']);
        Route::delete('/{id}', [TeacherController::class, 'destroy']);
        Route::get('/{id}/classes', [TeacherController::class, 'classes']);
    });

    // Classes
    Route::prefix('classes')->group(function () {
        Route::get('/', [ClassController::class, 'index']);
        Route::post('/', [ClassController::class, 'store']);
        Route::get('/{id}', [ClassController::class, 'show']);
        Route::put('/{id}', [ClassController::class, 'update']);
        Route::delete('/{id}', [ClassController::class, 'destroy']);
        Route::get('/{id}/students', [ClassController::class, 'students']);
        Route::get('/{id}/subjects', [ClassController::class, 'subjects']);
        Route::get('/{id}/timetable', [ClassController::class, 'timetable']);
    });

    // Subjects
    Route::prefix('subjects')->group(function () {
        Route::get('/', [SubjectController::class, 'index']);
        Route::post('/', [SubjectController::class, 'store']);
        Route::get('/{id}', [SubjectController::class, 'show']);
        Route::put('/{id}', [SubjectController::class, 'update']);
        Route::delete('/{id}', [SubjectController::class, 'destroy']);
    });

    // Exams
    Route::prefix('exams')->group(function () {
        Route::get('/', [ExamController::class, 'index']);
        Route::post('/', [ExamController::class, 'store']);
        Route::get('/{id}', [ExamController::class, 'show']);
        Route::put('/{id}', [ExamController::class, 'update']);
        Route::delete('/{id}', [ExamController::class, 'destroy']);
        Route::post('/{id}/publish', [ExamController::class, 'publish']);
        Route::get('/{id}/questions', [ExamController::class, 'questions']);
        Route::get('/{id}/results', [ExamController::class, 'results']);
        Route::get('/{id}/start', [ExamController::class, 'start']);
    });

    // Questions
    Route::prefix('questions')->group(function () {
        Route::get('/', [QuestionController::class, 'index']);
        Route::post('/', [QuestionController::class, 'store']);
        Route::post('/bulk', [QuestionController::class, 'bulkStore']);
        Route::get('/{id}', [QuestionController::class, 'show']);
        Route::put('/{id}', [QuestionController::class, 'update']);
        Route::delete('/{id}', [QuestionController::class, 'destroy']);
    });

    // Exam Attempts (CBT)
    Route::prefix('exam-attempts')->group(function () {
        Route::post('/start', [ExamAttemptController::class, 'start']);
        Route::post('/{id}/submit', [ExamAttemptController::class, 'submit']);
        Route::post('/{id}/save-answer', [ExamAttemptController::class, 'saveAnswer']);
        Route::get('/{id}', [ExamAttemptController::class, 'show']);
        Route::get('/student/history', [ExamAttemptController::class, 'studentHistory']);
    });

    // Results
    Route::prefix('results')->group(function () {
        Route::get('/', [ResultController::class, 'index']);
        Route::post('/', [ResultController::class, 'store']);
        Route::post('/bulk', [ResultController::class, 'bulkStore']);
        Route::get('/{id}', [ResultController::class, 'show']);
        Route::put('/{id}', [ResultController::class, 'update']);
        Route::post('/publish', [ResultController::class, 'publish']);
        Route::get('/class/{classId}', [ResultController::class, 'byClass']);
        Route::get('/student/{studentId}', [ResultController::class, 'byStudent']);
        Route::get('/report-card/{studentId}', [ResultController::class, 'reportCard']);
    });

    // Attendance
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index']);
        Route::post('/mark', [AttendanceController::class, 'mark']);
        Route::post('/bulk-mark', [AttendanceController::class, 'bulkMark']);
        Route::get('/class/{classId}', [AttendanceController::class, 'byClass']);
        Route::get('/student/{studentId}', [AttendanceController::class, 'byStudent']);
        Route::get('/summary', [AttendanceController::class, 'summary']);
    });

    // Finance
    Route::prefix('finance')->group(function () {
        Route::get('/invoices', [FinanceController::class, 'invoices']);
        Route::post('/invoices', [FinanceController::class, 'createInvoice']);
        Route::post('/invoices/bulk-generate', [FinanceController::class, 'bulkGenerate']);
        Route::get('/invoices/{id}', [FinanceController::class, 'showInvoice']);
        Route::get('/payments', [FinanceController::class, 'payments']);
        Route::post('/payments', [FinanceController::class, 'recordPayment']);
        Route::get('/summary', [FinanceController::class, 'summary']);
        Route::get('/report', [FinanceController::class, 'report']);
    });

    // Library
    Route::prefix('library')->group(function () {
        Route::get('/books', [LibraryController::class, 'books']);
        Route::post('/books', [LibraryController::class, 'addBook']);
        Route::get('/books/{id}', [LibraryController::class, 'showBook']);
        Route::put('/books/{id}', [LibraryController::class, 'updateBook']);
        Route::delete('/books/{id}', [LibraryController::class, 'deleteBook']);
        Route::get('/borrows', [LibraryController::class, 'borrows']);
        Route::post('/borrow', [LibraryController::class, 'borrowBook']);
        Route::post('/return/{id}', [LibraryController::class, 'returnBook']);
        Route::get('/overdue', [LibraryController::class, 'overdue']);
    });

    // Timetable
    Route::prefix('timetable')->group(function () {
        Route::get('/', [TimetableController::class, 'index']);
        Route::post('/', [TimetableController::class, 'store']);
        Route::put('/{id}', [TimetableController::class, 'update']);
        Route::delete('/{id}', [TimetableController::class, 'destroy']);
    });

    // Announcements
    Route::prefix('announcements')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index']);
        Route::post('/', [AnnouncementController::class, 'store']);
        Route::get('/{id}', [AnnouncementController::class, 'show']);
        Route::put('/{id}', [AnnouncementController::class, 'update']);
        Route::delete('/{id}', [AnnouncementController::class, 'destroy']);
    });

    // Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/academic', [ReportController::class, 'academic']);
        Route::get('/financial', [ReportController::class, 'financial']);
        Route::get('/attendance', [ReportController::class, 'attendance']);
        Route::get('/performance', [ReportController::class, 'performance']);
    });
});

// =====================================================================
// NEW ROUTES — Settings, Grading, Enhanced Results, Student Passport
// =====================================================================
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\GradingController;
use App\Http\Controllers\Api\EnhancedResultController;
use App\Http\Controllers\Api\EnhancedStudentController;
use App\Http\Controllers\Api\MaterialController;

Route::middleware('auth:sanctum')->group(function () {

    // School Settings
    Route::get('/settings', [SettingsController::class, 'show']);
    Route::post('/settings', [SettingsController::class, 'update']);

    // Grading System
    Route::get('/grading', [GradingController::class, 'index']);
    Route::post('/grading', [GradingController::class, 'update']);
    Route::post('/grading/recalculate', [GradingController::class, 'recalculate']);

    // Enhanced Results
    Route::prefix('v2/results')->group(function () {
        Route::get('/', [EnhancedResultController::class, 'index']);
        Route::post('/', [EnhancedResultController::class, 'store']);
        Route::post('/bulk', [EnhancedResultController::class, 'bulkStore']);
        Route::get('/{id}', [EnhancedResultController::class, 'show']);
        Route::put('/{id}', [EnhancedResultController::class, 'update']);
        Route::post('/publish', [EnhancedResultController::class, 'publish']);
        Route::post('/lock', [EnhancedResultController::class, 'lock']);
        Route::post('/unlock', [EnhancedResultController::class, 'unlock']);
        Route::post('/{id}/approve', [EnhancedResultController::class, 'approve']);
        Route::post('/calculate-positions', [EnhancedResultController::class, 'calculatePositions']);
        Route::get('/class/{classId}', [EnhancedResultController::class, 'byClass']);
        Route::get('/student/{studentId}', [EnhancedResultController::class, 'byStudent']);
        Route::get('/report-card/{studentId}', [EnhancedResultController::class, 'reportCard']);
        Route::get('/pdf/{studentId}', [EnhancedResultController::class, 'downloadPdf']);
    });

    // Enhanced Students (with passport upload)
    Route::prefix('v2/students')->group(function () {
        Route::get('/', [EnhancedStudentController::class, 'index']);
        Route::post('/', [EnhancedStudentController::class, 'store']);
        Route::get('/{id}', [EnhancedStudentController::class, 'show']);
        Route::post('/{id}', [EnhancedStudentController::class, 'update']); // POST for multipart
        Route::delete('/{id}', [EnhancedStudentController::class, 'destroy']);
        Route::post('/{id}/passport', [EnhancedStudentController::class, 'uploadPassport']);
    });

    // Materials
    Route::prefix('materials')->group(function () {
        Route::get('/', [MaterialController::class, 'index']);
        Route::post('/', [MaterialController::class, 'store']);
        Route::get('/my-uploads', [MaterialController::class, 'myUploads']);
        Route::get('/{id}', [MaterialController::class, 'show']);
        Route::put('/{id}', [MaterialController::class, 'update']);
        Route::delete('/{id}', [MaterialController::class, 'destroy']);
    });

    //creating admin password 
    use App\Models\User;
use Illuminate\Support\Facades\Hash;

Route::get('/create-admin', function () {
    User::updateOrCreate(
        ['email' => 'admin@school.com'],
        [
            'name' => 'Super Admin',
            'password' => Hash::make('Admin@123')
        ]
    );

    return response()->json([
        'message' => 'Admin account created successfully'
    ]);
});
});
