<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // 1. Add arm (A/B/C) as string to school_classes (was integer)
        Schema::table('school_classes', function (Blueprint $table) {
            $table->string('arm', 10)->default('A')->change(); // A, B, C, D
        });

        // 2. Add passport photo + parent details to students table
        Schema::table('students', function (Blueprint $table) {
            $table->string('passport_photo')->nullable()->after('admission_date');
            $table->string('parent_name')->nullable()->after('passport_photo');
            $table->string('parent_phone')->nullable()->after('parent_name');
            $table->string('parent_email')->nullable()->after('parent_phone');
            $table->string('parent_relationship')->nullable()->after('parent_email'); // Father/Mother/Guardian
            $table->string('state_of_origin')->nullable()->after('parent_relationship');
            $table->string('religion')->nullable()->after('state_of_origin');
            $table->string('blood_group')->nullable()->after('religion');
        });

        // 3. Add missing fields to results table
        Schema::table('results', function (Blueprint $table) {
            $table->integer('class_position')->nullable()->after('position');
            $table->integer('arm_position')->nullable()->after('class_position');
            $table->integer('subject_position')->nullable()->after('arm_position');
            $table->boolean('is_locked')->default(false)->after('is_published');
            $table->boolean('is_approved')->default(false)->after('is_locked');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('is_approved');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('waec_grade')->nullable()->after('grade'); // A1, B2, B3...
        });

        // 4. Grading system table (dynamic, school-configurable)
        Schema::create('grading_systems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('grade', 10);         // A1, B2, B3, C4, C5, C6, D7, E8, F9
            $table->integer('min_score');
            $table->integer('max_score');
            $table->string('remark');             // Excellent, Very Good, Good, Credit, etc.
            $table->string('category', 20);       // Pass, Fail
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['school_id', 'grade']);
        });

        // 5. School settings extended table (for logo, motto, etc.)
        Schema::table('schools', function (Blueprint $table) {
            $table->string('motto')->nullable()->after('address');
            $table->string('website')->nullable()->after('motto');
            $table->string('proprietor')->nullable()->after('website');
            $table->string('principal')->nullable()->after('proprietor');
            $table->string('stamp')->nullable()->after('logo'); // school stamp image
        });

        // 6. Sessions & terms — add artisan fields
        Schema::table('academic_sessions', function (Blueprint $table) {
            $table->string('label')->nullable()->after('name'); // "2025/2026 Academic Session"
        });

        // 7. Add position tracking per-class to results (for quick queries)
        // Already added class_position and arm_position above
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_systems');

        Schema::table('results', function (Blueprint $table) {
            $table->dropColumn(['class_position','arm_position','subject_position','is_locked','is_approved','approved_by','approved_at','waec_grade']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['passport_photo','parent_name','parent_phone','parent_email','parent_relationship','state_of_origin','religion','blood_group']);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['motto','website','proprietor','principal','stamp']);
        });
    }
};
