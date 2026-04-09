<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GradingSystem;
use App\Models\School;
use App\Models\Student;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicSession;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

// =====================================================================
// SCHOOL SETTINGS CONTROLLER
// =====================================================================
class SettingsController extends Controller
{
    public function show(Request $request)
    {
        $school = School::with('gradingSystems')->findOrFail($request->user()->school_id);
        return response()->json($school);
    }

    public function update(Request $request)
    {
        $school = School::findOrFail($request->user()->school_id);

        $data = $request->validate([
            'name'            => 'sometimes|string|max:200',
            'email'           => 'sometimes|email',
            'phone'           => 'sometimes|string',
            'address'         => 'sometimes|string',
            'state'           => 'sometimes|string',
            'motto'           => 'nullable|string',
            'website'         => 'nullable|url',
            'proprietor'      => 'nullable|string',
            'principal'       => 'nullable|string',
            'type'            => 'sometimes|in:mixed,boys,girls',
            'level'           => 'sometimes|in:primary,secondary,both',
            'current_session' => 'sometimes|string',
            'current_term'    => 'sometimes|in:1st,2nd,3rd',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|max:2048']);
            if ($school->logo) Storage::disk('public')->delete($school->logo);
            $data['logo'] = $request->file('logo')->store('schools/logos', 'public');
        }

        // Handle stamp upload
        if ($request->hasFile('stamp')) {
            $request->validate(['stamp' => 'image|max:2048']);
            if ($school->stamp) Storage::disk('public')->delete($school->stamp);
            $data['stamp'] = $request->file('stamp')->store('schools/stamps', 'public');
        }

        $school->update($data);

        return response()->json([
            'message' => 'Settings updated successfully.',
            'school'  => $school->fresh()->load('gradingSystems'),
        ]);
    }
}

// =====================================================================
// GRADING SYSTEM CONTROLLER
// =====================================================================
class GradingController extends Controller
{
    public function index(Request $request)
    {
        $grades = GradingSystem::where('school_id', $request->user()->school_id)
            ->orderBy('sort_order')
            ->get();
        return response()->json($grades);
    }

    public function update(Request $request)
    {
        $request->validate([
            'grades'               => 'required|array|min:1',
            'grades.*.grade'       => 'required|string|max:10',
            'grades.*.min_score'   => 'required|integer|min:0|max:100',
            'grades.*.max_score'   => 'required|integer|min:0|max:100',
            'grades.*.remark'      => 'required|string',
            'grades.*.category'    => 'required|in:Pass,Fail',
        ]);

        $schoolId = $request->user()->school_id;

        DB::transaction(function () use ($request, $schoolId) {
            // Delete existing and replace
            GradingSystem::where('school_id', $schoolId)->delete();

            foreach ($request->grades as $i => $g) {
                GradingSystem::create([
                    'school_id'  => $schoolId,
                    'grade'      => $g['grade'],
                    'min_score'  => $g['min_score'],
                    'max_score'  => $g['max_score'],
                    'remark'     => $g['remark'],
                    'category'   => $g['category'],
                    'sort_order' => $i + 1,
                ]);
            }

            // Recalculate all existing results with new grading
            $this->recalculateAllResults($schoolId);
        });

        return response()->json([
            'message' => 'Grading system updated. All results recalculated.',
            'grades'  => GradingSystem::where('school_id', $schoolId)->orderBy('sort_order')->get(),
        ]);
    }

    public function recalculate(Request $request)
    {
        $schoolId = $request->user()->school_id;
        $count    = $this->recalculateAllResults($schoolId);
        return response()->json(['message' => "$count results recalculated."]);
    }

    private function recalculateAllResults(int $schoolId): int
    {
        $school  = School::with('gradingSystems')->find($schoolId);
        $results = Result::where('school_id', $schoolId)->get();

        foreach ($results as $result) {
            $total = $result->ca_score + $result->exam_score;
            $g     = $school->getGradeForScore($total);
            $result->update([
                'waec_grade' => $g['grade'],
                'remark'     => $g['remark'],
                'grade'      => $g['category'] === 'Pass' ? 'Pass' : 'Fail',
            ]);
        }

        return $results->count();
    }
}

// =====================================================================
// ENHANCED RESULT CONTROLLER WITH POSITIONS & PDF
// =====================================================================
class EnhancedResultController extends Controller
{
    /**
     * Get graded result for a score using school's system
     */
    private function gradeScore(int $schoolId, int $score): array
    {
        $school = School::with('gradingSystems')->find($schoolId);
        if (!$school) return ['grade'=>'F9','remark'=>'Fail','category'=>'Fail'];
        return $school->getGradeForScore($score);
    }

    public function index(Request $request)
    {
        $query = Result::with(['student.user', 'subject', 'schoolClass'])
            ->where('school_id', $request->user()->school_id);

        if ($request->term_id)    $query->where('term_id', $request->term_id);
        if ($request->class_id)   $query->where('school_class_id', $request->class_id);
        if ($request->subject_id) $query->where('subject_id', $request->subject_id);
        if ($request->status === 'published') $query->where('is_published', true);
        if ($request->status === 'locked')    $query->where('is_locked', true);

        return response()->json($query->paginate(50));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'      => 'required|exists:students,id',
            'subject_id'      => 'required|exists:subjects,id',
            'school_class_id' => 'required|exists:school_classes,id',
            'term_id'         => 'required|exists:terms,id',
            'ca_score'        => 'required|numeric|min:0|max:30',
            'exam_score'      => 'required|numeric|min:0|max:70',
            'teacher_comment' => 'nullable|string',
        ]);

        $total   = $data['ca_score'] + $data['exam_score'];
        $graded  = $this->gradeScore($request->user()->school_id, $total);
        $session = AcademicSession::where('school_id', $request->user()->school_id)->where('is_current', true)->first();

        $result = Result::updateOrCreate(
            [
                'student_id'          => $data['student_id'],
                'subject_id'          => $data['subject_id'],
                'term_id'             => $data['term_id'],
                'academic_session_id' => $session?->id ?? 1,
            ],
            array_merge($data, [
                'school_id'           => $request->user()->school_id,
                'academic_session_id' => $session?->id ?? 1,
                'waec_grade'          => $graded['grade'],
                'grade'               => $graded['category'],
                'remark'              => $graded['remark'],
            ])
        );

        return response()->json($result->load(['student.user', 'subject']), 201);
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'results'              => 'required|array',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.ca_score'   => 'required|numeric|min:0',
            'results.*.exam_score' => 'required|numeric|min:0',
        ]);

        $schoolId = $request->user()->school_id;
        $session  = AcademicSession::where('school_id', $schoolId)->where('is_current', true)->first();
        $saved    = 0;

        foreach ($request->results as $r) {
            $total  = $r['ca_score'] + $r['exam_score'];
            $graded = $this->gradeScore($schoolId, $total);

            Result::updateOrCreate(
                [
                    'student_id'          => $r['student_id'],
                    'subject_id'          => $r['subject_id'],
                    'term_id'             => $r['term_id'],
                    'academic_session_id' => $session?->id ?? 1,
                ],
                array_merge($r, [
                    'school_id'           => $schoolId,
                    'academic_session_id' => $session?->id ?? 1,
                    'waec_grade'          => $graded['grade'],
                    'grade'               => $graded['category'],
                    'remark'              => $graded['remark'],
                ])
            );
            $saved++;
        }

        return response()->json(['message' => "$saved results saved."]);
    }

    public function show($id)
    {
        return response()->json(Result::with(['student.user', 'subject'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $result = Result::findOrFail($id);

        if ($result->is_locked) {
            return response()->json(['message' => 'This result is locked and cannot be edited.'], 422);
        }

        $result->update($request->only(['ca_score', 'exam_score', 'teacher_comment']));

        // Recalculate grade
        $total  = $result->fresh()->ca_score + $result->fresh()->exam_score;
        $graded = $this->gradeScore($result->school_id, $total);
        $result->update(['waec_grade' => $graded['grade'], 'grade' => $graded['category'], 'remark' => $graded['remark']]);

        return response()->json($result->fresh()->load(['student.user', 'subject']));
    }

    /**
     * Calculate and save positions for a class/term
     */
    public function calculatePositions(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'term_id'  => 'required|exists:terms,id',
        ]);

        $classId  = $request->class_id;
        $termId   = $request->term_id;
        $schoolId = $request->user()->school_id;
        $session  = AcademicSession::where('school_id', $schoolId)->where('is_current', true)->first();

        // Get all students in this class
        $students = Student::where('school_class_id', $classId)->pluck('id');

        // Calculate total score per student
        $studentTotals = [];
        foreach ($students as $studentId) {
            $total = Result::where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('academic_session_id', $session?->id ?? 1)
                ->sum(DB::raw('ca_score + exam_score'));
            $studentTotals[$studentId] = $total;
        }

        // Sort descending for position
        arsort($studentTotals);

        // Assign class positions (handle ties)
        $position = 1;
        $prevScore = null;
        $prevPos   = 1;
        $rank      = 0;
        $classPositions = [];

        foreach ($studentTotals as $studentId => $total) {
            $rank++;
            if ($total === $prevScore) {
                $classPositions[$studentId] = $prevPos;
            } else {
                $classPositions[$studentId] = $rank;
                $prevPos   = $rank;
            }
            $prevScore = $total;
        }

        // Calculate subject positions within the class
        $subjects = Result::where('school_class_id', $classId)
            ->where('term_id', $termId)
            ->where('academic_session_id', $session?->id ?? 1)
            ->distinct()->pluck('subject_id');

        foreach ($subjects as $subjectId) {
            $subjectResults = Result::where('school_class_id', $classId)
                ->where('term_id', $termId)
                ->where('subject_id', $subjectId)
                ->where('academic_session_id', $session?->id ?? 1)
                ->orderByRaw('ca_score + exam_score DESC')
                ->get();

            $sPos = 1; $sPrev = null; $sRank = 0;
            foreach ($subjectResults as $res) {
                $sRank++;
                $score = $res->ca_score + $res->exam_score;
                if ($score === $sPrev) {
                    $res->update(['subject_position' => $sPos]);
                } else {
                    $res->update(['subject_position' => $sRank]);
                    $sPos = $sRank;
                }
                $sPrev = $score;
            }
        }

        // Save class positions to all results for these students
        $updated = 0;
        foreach ($classPositions as $studentId => $pos) {
            $count = Result::where('student_id', $studentId)
                ->where('term_id', $termId)
                ->where('academic_session_id', $session?->id ?? 1)
                ->update(['class_position' => $pos]);
            $updated += $count;
        }

        return response()->json([
            'message'  => 'Positions calculated successfully.',
            'students' => count($students),
            'updated'  => $updated,
        ]);
    }

    public function publish(Request $request)
    {
        $updated = Result::where('school_id', $request->user()->school_id)
            ->where('term_id', $request->term_id ?? 2)
            ->update(['is_published' => true]);
        return response()->json(['message' => "Results published. $updated records updated."]);
    }

    public function lock(Request $request)
    {
        $request->validate(['class_id' => 'required', 'term_id' => 'required']);
        Result::where('school_class_id', $request->class_id)
            ->where('term_id', $request->term_id)
            ->update(['is_locked' => true]);
        return response()->json(['message' => 'Results locked.']);
    }

    public function unlock(Request $request)
    {
        $request->validate(['class_id' => 'required', 'term_id' => 'required']);
        Result::where('school_class_id', $request->class_id)
            ->where('term_id', $request->term_id)
            ->update(['is_locked' => false]);
        return response()->json(['message' => 'Results unlocked.']);
    }

    public function approve(Request $request, $id)
    {
        $result = Result::findOrFail($id);
        $result->update(['is_approved' => true, 'approved_by' => $request->user()->id, 'approved_at' => now()]);
        return response()->json(['message' => 'Result approved.']);
    }

    public function byClass($classId)
    {
        return response()->json(
            Result::with(['student.user', 'subject'])
                ->where('school_class_id', $classId)
                ->get()
        );
    }

    public function byStudent($studentId)
    {
        return response()->json(
            Result::with(['subject'])
                ->where('student_id', $studentId)
                ->get()
        );
    }

    /**
     * Full report card data with positions, school info, student photo
     */
    public function reportCard($studentId)
    {
        $student = Student::with(['user', 'schoolClass', 'school'])->findOrFail($studentId);
        $school  = School::with('gradingSystems')->find($student->school_id);
        $session = AcademicSession::where('school_id', $student->school_id)->where('is_current', true)->first();
        $term    = Term::where('school_id', $student->school_id)->where('is_current', true)->first();

        $results = Result::with(['subject'])
            ->where('student_id', $studentId)
            ->where('term_id', $term?->id ?? 2)
            ->where('academic_session_id', $session?->id ?? 1)
            ->get();

        $totalScore  = $results->sum(fn($r) => $r->ca_score + $r->exam_score);
        $subjectCount = $results->count();
        $average     = $subjectCount ? round($totalScore / $subjectCount, 1) : 0;

        // Attendance
        $present = \App\Models\Attendance::where('student_id', $studentId)->where('status', 'present')->count();
        $totalDays = \App\Models\Attendance::where('student_id', $studentId)->count();

        // Get class size
        $classSize = Student::where('school_class_id', $student->school_class_id)->count();

        return response()->json([
            'student'       => $student,
            'school'        => $school,
            'results'       => $results,
            'session'       => $session?->name ?? '2025/2026',
            'term'          => ($term?->name ?? '2nd') . ' Term',
            'total_score'   => $totalScore,
            'average'       => $average,
            'class_position'=> $results->first()?->class_position ?? '—',
            'arm_position'  => $results->first()?->arm_position ?? '—',
            'class_size'    => $classSize,
            'days_present'  => $present,
            'total_days'    => $totalDays,
            'grading'       => $school?->gradingSystems ?? [],
        ]);
    }

    /**
     * Generate PDF result sheet using DOMPDF
     */
    public function downloadPdf($studentId)
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return response()->json(['message' => 'PDF generation requires dompdf. Run: composer require barryvdh/laravel-dompdf'], 422);
        }

        $data = json_decode($this->reportCard($studentId)->getContent(), true);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('results.report-card', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'result_' . ($data['student']['user']['last_name'] ?? 'student') . '_' . now()->format('Y') . '.pdf';

        return $pdf->download($filename);
    }
}

// =====================================================================
// STUDENT CONTROLLER (ENHANCED — passport upload, parent info, arm)
// =====================================================================
class EnhancedStudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['user', 'schoolClass'])
            ->where('school_id', $request->user()->school_id);

        if ($request->class_id) $query->where('school_class_id', $request->class_id);
        if ($request->arm)      $query->whereHas('schoolClass', fn($q) => $q->where('arm', $request->arm));
        if ($request->search) {
            $query->whereHas('user', fn($q) =>
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
            )->orWhere('admission_number', 'like', "%{$request->search}%");
        }

        $students = $query->paginate($request->per_page ?? 20);
        return response()->json([
            'data'  => $students->items(),
            'total' => $students->total(),
            'pages' => $students->lastPage(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'           => 'required|string|max:100',
            'last_name'            => 'required|string|max:100',
            'email'                => 'required|email|unique:users',
            'date_of_birth'        => 'required|date',
            'gender'               => 'required|in:male,female',
            'school_class_id'      => 'required|exists:school_classes,id',
            'admission_date'       => 'required|date',
            'address'              => 'nullable|string',
            'phone'                => 'nullable|string',
            'parent_name'          => 'nullable|string',
            'parent_phone'         => 'nullable|string',
            'parent_email'         => 'nullable|email',
            'parent_relationship'  => 'nullable|in:Father,Mother,Guardian,Other',
            'state_of_origin'      => 'nullable|string',
            'religion'             => 'nullable|string',
            'blood_group'          => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'medical_notes'        => 'nullable|string',
        ]);

        $user = \App\Models\User::create([
            'school_id'     => $request->user()->school_id,
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'email'         => $data['email'],
            'phone'         => $data['phone'] ?? null,
            'password'      => \Hash::make('password'),
            'role'          => 'student',
            'date_of_birth' => $data['date_of_birth'],
            'gender'        => $data['gender'],
            'address'       => $data['address'] ?? null,
        ]);

        // Passport photo
        $passportPath = null;
        if ($request->hasFile('passport_photo')) {
            $request->validate(['passport_photo' => 'image|max:2048']);
            $passportPath = $request->file('passport_photo')->store('students/passports', 'public');
        }

        $last     = Student::where('school_id', $request->user()->school_id)->latest()->first();
        $num      = $last ? (intval(substr($last->admission_number, -4)) + 1) : 1;
        $admNumber = 'EDU/' . date('Y') . '/' . str_pad($num, 4, '0', STR_PAD_LEFT);

        $student = Student::create([
            'user_id'              => $user->id,
            'school_id'            => $request->user()->school_id,
            'school_class_id'      => $data['school_class_id'],
            'admission_number'     => $admNumber,
            'admission_date'       => $data['admission_date'],
            'passport_photo'       => $passportPath,
            'parent_name'          => $data['parent_name'] ?? null,
            'parent_phone'         => $data['parent_phone'] ?? null,
            'parent_email'         => $data['parent_email'] ?? null,
            'parent_relationship'  => $data['parent_relationship'] ?? null,
            'state_of_origin'      => $data['state_of_origin'] ?? null,
            'religion'             => $data['religion'] ?? null,
            'blood_group'          => $data['blood_group'] ?? null,
            'medical_notes'        => $data['medical_notes'] ?? null,
        ]);

        return response()->json($student->load(['user', 'schoolClass']), 201);
    }

    public function show($id)
    {
        return response()->json(Student::with(['user', 'schoolClass.school'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $data = $request->validate([
            'first_name'          => 'sometimes|string',
            'last_name'           => 'sometimes|string',
            'school_class_id'     => 'sometimes|exists:school_classes,id',
            'address'             => 'sometimes|string',
            'phone'               => 'sometimes|string',
            'parent_name'         => 'nullable|string',
            'parent_phone'        => 'nullable|string',
            'parent_email'        => 'nullable|email',
            'parent_relationship' => 'nullable|string',
            'state_of_origin'     => 'nullable|string',
            'religion'            => 'nullable|string',
            'blood_group'         => 'nullable|string',
            'medical_notes'       => 'nullable|string',
        ]);

        // Passport upload update
        if ($request->hasFile('passport_photo')) {
            $request->validate(['passport_photo' => 'image|max:2048']);
            if ($student->passport_photo) \Storage::disk('public')->delete($student->passport_photo);
            $data['passport_photo'] = $request->file('passport_photo')->store('students/passports', 'public');
        }

        $student->user->update(array_intersect_key($data, array_flip(['first_name','last_name','address','phone'])));
        $student->update(array_diff_key($data, array_flip(['first_name','last_name','address','phone'])));

        return response()->json($student->fresh()->load(['user', 'schoolClass']));
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        if ($student->passport_photo) \Storage::disk('public')->delete($student->passport_photo);
        $student->user->delete();
        return response()->json(['message' => 'Student deleted.']);
    }

    public function uploadPassport(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $request->validate(['photo' => 'required|image|max:2048']);

        if ($student->passport_photo) \Storage::disk('public')->delete($student->passport_photo);
        $path = $request->file('photo')->store('students/passports', 'public');
        $student->update(['passport_photo' => $path]);

        return response()->json(['message' => 'Passport updated.', 'url' => url('storage/' . $path)]);
    }
}
