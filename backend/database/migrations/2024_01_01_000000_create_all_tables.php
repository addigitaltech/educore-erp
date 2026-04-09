<?php
// ============================================================
// MASTER MIGRATION FILE
// Run: php artisan migrate
// All tables are created in order with proper foreign keys
// ============================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ---- 001: Schools ----
return new class extends Migration {
    public function up(): void
    {
        // Schools
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Nigeria');
            $table->string('logo')->nullable();
            $table->enum('type', ['mixed', 'boys', 'girls'])->default('mixed');
            $table->enum('level', ['primary', 'secondary', 'both'])->default('secondary');
            $table->enum('plan', ['starter', 'professional', 'enterprise'])->default('starter');
            $table->enum('status', ['active', 'inactive', 'suspended', 'trial'])->default('trial');
            $table->date('subscription_expires_at')->nullable();
            $table->string('current_session')->default('2025/2026');
            $table->enum('current_term', ['1st', '2nd', '3rd'])->default('2nd');
            $table->timestamps();
            $table->softDeletes();
        });

        // Users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->enum('role', ['superadmin', 'admin', 'teacher', 'student', 'parent', 'accountant', 'librarian']);
            $table->string('avatar')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('address')->nullable();
            $table->string('state')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['school_id', 'role']);
        });

        // Personal Access Tokens (Sanctum)
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Academic Sessions
        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. 2025/2026
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        // Terms
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->enum('name', ['1st', '2nd', '3rd']);
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
        });

        // Classes
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // JSS 1A, SS 2B
            $table->string('level'); // JSS, SS
            $table->integer('arm')->default(1);
            $table->foreignId('form_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('capacity')->default(45);
            $table->timestamps();
        });

        // Subjects
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('category')->nullable(); // Science, Arts, Commercial
            $table->integer('ca_weight')->default(30);
            $table->integer('exam_weight')->default(70);
            $table->timestamps();
        });

        // Class Subjects (pivot)
        Schema::create('class_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unique(['school_class_id', 'subject_id']);
        });

        // Students
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained()->nullOnDelete();
            $table->string('admission_number')->unique();
            $table->date('admission_date');
            $table->foreignId('parent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('medical_notes')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'school_class_id']);
        });

        // Teachers
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('staff_id')->unique();
            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();
            $table->date('date_joined');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract'])->default('full_time');
            $table->timestamps();
        });

        // Exams
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->integer('total_marks')->default(100);
            $table->integer('pass_mark')->default(50);
            $table->integer('num_questions')->default(40);
            $table->boolean('randomize_questions')->default(true);
            $table->boolean('randomize_options')->default(true);
            $table->boolean('show_results_immediately')->default(true);
            $table->boolean('allow_review')->default(false);
            $table->boolean('anti_cheat')->default(true);
            $table->enum('status', ['draft', 'published', 'ongoing', 'completed', 'cancelled'])->default('draft');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Questions
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['mcq', 'true_false', 'essay', 'fill_blank']);
            $table->text('question_text');
            $table->string('image')->nullable();
            $table->json('options')->nullable(); // For MCQ: [{text, is_correct}]
            $table->text('correct_answer')->nullable(); // For fill_blank/essay
            $table->text('explanation')->nullable();
            $table->integer('marks')->default(2);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Exam Attempts
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->integer('score')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->string('grade')->nullable();
            $table->boolean('is_submitted')->default(false);
            $table->integer('time_spent_seconds')->nullable();
            $table->json('answers')->nullable(); // {question_id: answer}
            $table->enum('status', ['ongoing', 'submitted', 'graded', 'absent'])->default('ongoing');
            $table->timestamps();
            $table->unique(['exam_id', 'student_id']);
        });

        // Results
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->integer('ca_score')->default(0); // Continuous Assessment
            $table->integer('exam_score')->default(0);
            $table->integer('total_score')->virtualAs('ca_score + exam_score');
            $table->string('grade')->nullable();
            $table->string('remark')->nullable();
            $table->integer('position')->nullable();
            $table->text('teacher_comment')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->unique(['student_id', 'subject_id', 'term_id', 'academic_session_id'], 'unique_result');
        });

        // Attendance
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marked_by')->constrained('users');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->string('remark')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'date']);
            $table->index(['school_id', 'date']);
        });

        // Fee Categories
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Tuition, Development Levy, PTA
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Fee Schedules
        Schema::create('fee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->string('class_level')->nullable(); // JSS, SS, or null for all
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });

        // Invoices
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->virtualAs('total_amount - amount_paid');
            $table->enum('status', ['unpaid', 'partial', 'paid', 'overdue'])->default('unpaid');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->constrained('users');
            $table->string('reference_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['cash', 'bank_transfer', 'card', 'paystack', 'flutterwave', 'cheque'])->default('cash');
            $table->date('payment_date');
            $table->string('bank_name')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'reversed'])->default('confirmed');
            $table->timestamps();
        });

        // Books
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('author');
            $table->string('isbn')->nullable();
            $table->string('publisher')->nullable();
            $table->year('year_published')->nullable();
            $table->string('category');
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->string('cover_image')->nullable();
            $table->text('description')->nullable();
            $table->string('shelf_number')->nullable();
            $table->timestamps();
        });

        // Borrow Records
        Schema::create('borrow_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issued_by')->constrained('users');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->decimal('fine_amount', 8, 2)->default(0);
            $table->boolean('fine_paid')->default(false);
            $table->enum('status', ['active', 'returned', 'overdue', 'lost'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Timetable
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users');
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('period_number');
            $table->string('room')->nullable();
            $table->timestamps();
        });

        // Announcements
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->string('title');
            $table->text('body');
            $table->enum('audience', ['all', 'students', 'teachers', 'parents', 'staff'])->default('all');
            $table->enum('priority', ['normal', 'important', 'urgent'])->default('normal');
            $table->boolean('is_published')->default(true);
            $table->date('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Activity Log
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('timetables');
        Schema::dropIfExists('borrow_records');
        Schema::dropIfExists('books');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('fee_schedules');
        Schema::dropIfExists('fee_categories');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('results');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('students');
        Schema::dropIfExists('class_subjects');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('school_classes');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('academic_sessions');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('schools');
    }
};
