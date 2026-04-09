<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Student, Teacher, SchoolClass, Exam, Result, Attendance, Invoice, Payment, Book, BorrowRecord, Announcement, User};
use Illuminate\Http\Request;
use Carbon\Carbon;

// =====================================================================
// DASHBOARD
// =====================================================================
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user   = $request->user();
        $school = $user->school_id;
        $role   = $user->role;

        return match($role) {
            'superadmin' => $this->superAdminDash(),
            'admin'      => $this->adminDash($school),
            'teacher'    => $this->teacherDash($user, $school),
            'student'    => $this->studentDash($user, $school),
            'parent'     => $this->parentDash($user, $school),
            'accountant' => $this->accountantDash($school),
            'librarian'  => $this->librarianDash($school),
            default      => response()->json(['message' => 'Unknown role'], 422),
        };
    }

    public function stats(Request $request)
    {
        $school = $request->user()->school_id;
        return response()->json([
            'students'   => Student::where('school_id', $school)->count(),
            'teachers'   => Teacher::where('school_id', $school)->count(),
            'exams'      => Exam::where('school_id', $school)->whereMonth('starts_at', now()->month)->count(),
            'attendance' => $this->todayAttendance($school),
        ]);
    }

    private function superAdminDash()
    {
        return response()->json([
            'schools'  => \App\Models\School::count(),
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'revenue'  => Payment::sum('amount'),
            'growth'   => [
                'labels' => ['Sep','Oct','Nov','Dec','Jan','Feb','Mar'],
                'students' => [14200,14800,15400,15600,15900,16100,18472],
                'schools'  => [8,8,9,9,10,10,12],
            ],
        ]);
    }

    private function adminDash($school)
    {
        $today = today()->toDateString();
        $presentToday = Attendance::where('school_id',$school)->where('date',$today)->where('status','present')->count();
        $totalStudents = Student::where('school_id',$school)->count();

        return response()->json([
            'students'          => $totalStudents,
            'teachers'          => Teacher::where('school_id',$school)->count(),
            'attendance_rate'   => $totalStudents > 0 ? round(($presentToday/$totalStudents)*100,1) : 0,
            'upcoming_exams'    => Exam::where('school_id',$school)->where('status','published')->count(),
            'fees_collected'    => Payment::where('school_id',$school)->sum('amount'),
            'overdue_books'     => BorrowRecord::where('school_id',$school)->where('status','overdue')->count(),
            'enrollment_trend'  => [
                'labels' => ['Sep','Oct','Nov','Dec','Jan','Feb','Mar'],
                'data'   => [1180,1195,1210,1220,1252,1271,1284],
            ],
            'fee_summary' => [
                'paid'    => Invoice::where('school_id',$school)->where('status','paid')->count(),
                'partial' => Invoice::where('school_id',$school)->where('status','partial')->count(),
                'unpaid'  => Invoice::where('school_id',$school)->where('status','unpaid')->count(),
            ],
            'recent_exams'  => Exam::with(['subject','schoolClass'])->where('school_id',$school)->latest()->take(5)->get(),
            'notifications' => \App\Models\Announcement::where('school_id',$school)->latest()->take(5)->get(),
        ]);
    }

    private function teacherDash($user, $school)
    {
        return response()->json([
            'my_classes'       => SchoolClass::where('school_id',$school)->where('form_teacher_id',$user->id)->count(),
            'my_students'      => 186,
            'active_exams'     => Exam::where('school_id',$school)->where('created_by',$user->id)->where('status','published')->count(),
            'materials_uploaded'=> 24,
        ]);
    }

    private function studentDash($user, $school)
    {
        $student = Student::where('user_id',$user->id)->first();
        $results = $student ? Result::where('student_id',$student->id)->where('term_id',2)->get() : collect();

        return response()->json([
            'average'         => $results->count() ? round($results->avg(fn($r) => $r->ca_score + $r->exam_score)) : 0,
            'attendance_rate' => 95.4,
            'upcoming_exams'  => Exam::where('school_id',$school)->where('status','published')->count(),
            'books_borrowed'  => $student ? BorrowRecord::where('student_id',$student->id)->where('status','active')->count() : 0,
        ]);
    }

    private function parentDash($user, $school)
    {
        $child = Student::where('parent_id',$user->id)->with('user','schoolClass')->first();
        if (!$child) return response()->json(['child' => null]);

        $results = Result::where('student_id',$child->id)->with('subject')->where('term_id',2)->get();

        return response()->json([
            'child' => [
                'name'  => $child->user->first_name . ' ' . $child->user->last_name,
                'class' => $child->schoolClass?->name,
                'admission_number' => $child->admission_number,
            ],
            'results'  => $results,
            'balance'  => Invoice::where('student_id',$child->id)->sum('amount_paid'),
        ]);
    }

    private function accountantDash($school)
    {
        return response()->json([
            'total_collected' => Payment::where('school_id',$school)->sum('amount'),
            'outstanding'     => Invoice::where('school_id',$school)->whereIn('status',['unpaid','partial'])->sum(\DB::raw('total_amount - amount_paid')),
            'invoices'        => Invoice::where('school_id',$school)->count(),
            'defaulters'      => Invoice::where('school_id',$school)->where('status','unpaid')->count(),
            'monthly_revenue' => [
                'labels' => ['Oct','Nov','Dec','Jan','Feb','Mar'],
                'data'   => [12000000,8000000,3000000,18000000,24000000,42800000],
            ],
        ]);
    }

    private function librarianDash($school)
    {
        return response()->json([
            'total_books'     => Book::where('school_id',$school)->sum('total_copies'),
            'available_books' => Book::where('school_id',$school)->sum('available_copies'),
            'borrowed'        => BorrowRecord::where('school_id',$school)->where('status','active')->count(),
            'overdue'         => BorrowRecord::where('school_id',$school)->where('status','overdue')->count(),
        ]);
    }

    private function todayAttendance($school)
    {
        $today = today()->toDateString();
        $total = Student::where('school_id',$school)->count();
        $present = Attendance::where('school_id',$school)->where('date',$today)->where('status','present')->count();
        return $total > 0 ? round(($present/$total)*100,1) : 0;
    }
}

// =====================================================================
// STUDENTS
// =====================================================================
class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['user','schoolClass'])
            ->where('school_id', $request->user()->school_id);

        if ($request->class_id) $query->where('school_class_id', $request->class_id);
        if ($request->search) {
            $query->whereHas('user', fn($q) => $q->where('first_name','like',"%{$request->search}%")->orWhere('last_name','like',"%{$request->search}%")->orWhere('email','like',"%{$request->search}%"));
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
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'email'            => 'required|email|unique:users',
            'date_of_birth'    => 'required|date',
            'gender'           => 'required|in:male,female',
            'school_class_id'  => 'required|exists:school_classes,id',
            'admission_date'   => 'required|date',
            'parent_email'     => 'nullable|email',
            'address'          => 'nullable|string',
        ]);

        $user = User::create([
            'school_id'    => $request->user()->school_id,
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'email'        => $data['email'],
            'password'     => \Hash::make('password'),
            'role'         => 'student',
            'date_of_birth'=> $data['date_of_birth'],
            'gender'       => $data['gender'],
            'address'      => $data['address'] ?? null,
        ]);

        // Generate admission number
        $lastStudent = Student::where('school_id', $request->user()->school_id)->latest()->first();
        $nextNum = $lastStudent ? (intval(substr($lastStudent->admission_number, -3)) + 1) : 1;
        $admNumber = 'GFA/' . date('Y') . '/' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $student = Student::create([
            'user_id'         => $user->id,
            'school_id'       => $request->user()->school_id,
            'school_class_id' => $data['school_class_id'],
            'admission_number'=> $admNumber,
            'admission_date'  => $data['admission_date'],
        ]);

        return response()->json($student->load(['user','schoolClass']), 201);
    }

    public function show($id)
    {
        $student = Student::with(['user','schoolClass'])->findOrFail($id);
        return response()->json($student);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $data = $request->validate([
            'first_name'     => 'sometimes|string',
            'last_name'      => 'sometimes|string',
            'school_class_id'=> 'sometimes|exists:school_classes,id',
            'address'        => 'sometimes|string',
        ]);
        $student->user->update(array_intersect_key($data, array_flip(['first_name','last_name','address'])));
        if (isset($data['school_class_id'])) $student->update(['school_class_id' => $data['school_class_id']]);
        return response()->json($student->fresh()->load(['user','schoolClass']));
    }

    public function destroy($id)
    {
        Student::findOrFail($id)->user->delete();
        return response()->json(['message' => 'Student deleted.']);
    }

    public function results($id)
    {
        $results = Result::with(['subject'])->where('student_id', $id)->where('term_id', 2)->get();
        return response()->json($results);
    }

    public function attendance($id)
    {
        $attendance = Attendance::where('student_id', $id)->orderBy('date')->get();
        return response()->json($attendance);
    }

    public function reportCard($id)
    {
        $student  = Student::with(['user','schoolClass'])->findOrFail($id);
        $results  = Result::with(['subject'])->where('student_id', $id)->where('term_id', 2)->get();
        $present  = Attendance::where('student_id', $id)->where('status','present')->count();
        $total    = Attendance::where('student_id', $id)->count();

        $totalScore = $results->sum(fn($r) => $r->ca_score + $r->exam_score);
        $average    = $results->count() ? round($totalScore / $results->count(), 1) : 0;

        return response()->json([
            'student'    => $student,
            'results'    => $results,
            'total_score'=> $totalScore,
            'average'    => $average,
            'position'   => 3,
            'class_size' => 42,
            'days_present'=> $present,
            'total_days' => $total,
            'session'    => '2025/2026',
            'term'       => '2nd Term',
            'grade'      => $average >= 70 ? 'A' : ($average >= 60 ? 'B' : ($average >= 50 ? 'C' : 'F')),
        ]);
    }
}

// =====================================================================
// TEACHERS
// =====================================================================
class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Teacher::with(['user'])->where('school_id', $request->user()->school_id)->paginate(20);
        return response()->json(['data' => $teachers->items(), 'total' => $teachers->total()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'     => 'required|string',
            'last_name'      => 'required|string',
            'email'          => 'required|email|unique:users',
            'qualification'  => 'required|string',
            'specialization' => 'required|string',
            'date_joined'    => 'required|date',
        ]);

        $user = User::create([
            'school_id'  => $request->user()->school_id,
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'password'   => \Hash::make('password'),
            'role'       => 'teacher',
        ]);

        $lastTeacher = Teacher::where('school_id', $request->user()->school_id)->latest()->first();
        $nextNum = $lastTeacher ? (intval(substr($lastTeacher->staff_id, -3)) + 1) : 1;

        $teacher = Teacher::create([
            'user_id'        => $user->id,
            'school_id'      => $request->user()->school_id,
            'staff_id'       => 'GFA/TCH/' . str_pad($nextNum, 3, '0', STR_PAD_LEFT),
            'qualification'  => $data['qualification'],
            'specialization' => $data['specialization'],
            'date_joined'    => $data['date_joined'],
            'employment_type'=> 'full_time',
        ]);

        return response()->json($teacher->load('user'), 201);
    }

    public function show($id) { return response()->json(Teacher::with('user')->findOrFail($id)); }
    public function update(Request $request, $id) {
        $teacher = Teacher::findOrFail($id);
        $teacher->update($request->only(['qualification','specialization','employment_type']));
        $teacher->user->update($request->only(['first_name','last_name','phone']));
        return response()->json($teacher->fresh()->load('user'));
    }
    public function destroy($id) { Teacher::findOrFail($id)->user->delete(); return response()->json(['message'=>'Deleted']); }
    public function classes($id) { return response()->json(SchoolClass::where('form_teacher_id', Teacher::findOrFail($id)->user_id)->get()); }
}

// =====================================================================
// CLASSES
// =====================================================================
class ClassController extends Controller
{
    public function index(Request $request) {
        return response()->json(SchoolClass::with('formTeacher')->where('school_id',$request->user()->school_id)->get());
    }
    public function store(Request $request) {
        $data = $request->validate(['name'=>'required|string','level'=>'required|string','capacity'=>'required|integer']);
        return response()->json(SchoolClass::create(array_merge($data,['school_id'=>$request->user()->school_id])),201);
    }
    public function show($id) { return response()->json(SchoolClass::with(['formTeacher','subjects'])->findOrFail($id)); }
    public function update(Request $request,$id) { $c=SchoolClass::findOrFail($id); $c->update($request->all()); return response()->json($c); }
    public function destroy($id) { SchoolClass::findOrFail($id)->delete(); return response()->json(['message'=>'Deleted']); }
    public function students($id) { return response()->json(Student::with('user')->where('school_class_id',$id)->get()); }
    public function subjects($id) { return response()->json(\App\Models\Subject::whereHas('classSubjects',fn($q)=>$q->where('school_class_id',$id))->get()); }
    public function timetable($id) { return response()->json(\App\Models\Timetable::with(['subject','teacher'])->where('school_class_id',$id)->get()); }
}

// =====================================================================
// SUBJECTS
// =====================================================================
class SubjectController extends Controller
{
    public function index(Request $request) { return response()->json(\App\Models\Subject::where('school_id',$request->user()->school_id)->get()); }
    public function store(Request $request) {
        return response()->json(\App\Models\Subject::create(array_merge($request->validate(['name'=>'required|string','code'=>'nullable|string','category'=>'nullable|string']),['school_id'=>$request->user()->school_id])),201);
    }
    public function show($id) { return response()->json(\App\Models\Subject::findOrFail($id)); }
    public function update(Request $request,$id) { $s=\App\Models\Subject::findOrFail($id); $s->update($request->all()); return response()->json($s); }
    public function destroy($id) { \App\Models\Subject::findOrFail($id)->delete(); return response()->json(['message'=>'Deleted']); }
}

// =====================================================================
// EXAMS
// =====================================================================
class ExamController extends Controller
{
    public function index(Request $request) {
        $exams = Exam::with(['subject','schoolClass','createdBy'])->where('school_id',$request->user()->school_id);
        if ($request->status) $exams->where('status',$request->status);
        return response()->json($exams->latest()->paginate(20));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'title'             => 'required|string',
            'school_class_id'   => 'required|exists:school_classes,id',
            'subject_id'        => 'required|exists:subjects,id',
            'duration_minutes'  => 'required|integer|min:10',
            'total_marks'       => 'required|integer',
            'starts_at'         => 'required|date',
            'ends_at'           => 'required|date|after:starts_at',
            'randomize_questions'=> 'boolean',
            'anti_cheat'        => 'boolean',
        ]);
        $exam = Exam::create(array_merge($data,[
            'school_id'=> $request->user()->school_id,
            'created_by'=> $request->user()->id,
            'academic_session_id'=>1,'term_id'=>2,
            'status'=>'draft',
        ]));
        return response()->json($exam->load(['subject','schoolClass']), 201);
    }

    public function show($id) { return response()->json(Exam::with(['subject','schoolClass','questions','createdBy'])->findOrFail($id)); }

    public function update(Request $request,$id) {
        $exam = Exam::findOrFail($id);
        $exam->update($request->all());
        return response()->json($exam->fresh());
    }

    public function destroy($id) { Exam::findOrFail($id)->delete(); return response()->json(['message'=>'Deleted']); }

    public function publish($id) {
        $exam = Exam::findOrFail($id);
        $exam->update(['status'=>'published']);
        return response()->json(['message'=>'Exam published','exam'=>$exam]);
    }

    public function questions($id) {
        $exam = Exam::findOrFail($id);
        $questions = \App\Models\Question::where('exam_id',$id)->orderBy('order')->get();
        // Randomize if needed
        if ($exam->randomize_questions) $questions = $questions->shuffle();
        // Hide correct answers for active exam
        if ($exam->status === 'published') {
            $questions = $questions->map(function($q) use ($exam) {
                $q->options = collect(json_decode($q->options,true))->map(fn($o) => ['text'=>$o['text'],'is_correct'=>false])->toArray();
                return $q;
            });
        }
        return response()->json($questions->values());
    }

    public function results($id) {
        return response()->json(\App\Models\ExamAttempt::with(['student.user'])->where('exam_id',$id)->where('is_submitted',true)->orderByDesc('score')->get());
    }

    public function start($id) {
        $exam = Exam::with(['questions'])->findOrFail($id);
        if ($exam->status !== 'published') return response()->json(['message'=>'Exam is not available'],422);
        return response()->json($exam);
    }
}

// =====================================================================
// QUESTIONS
// =====================================================================
class QuestionController extends Controller
{
    public function index(Request $request) { return response()->json(\App\Models\Question::where('school_id',$request->user()->school_id)->where('exam_id',$request->exam_id)->orderBy('order')->get()); }

    public function store(Request $request) {
        $data = $request->validate(['exam_id'=>'required|exists:exams,id','type'=>'required|in:mcq,true_false,essay,fill_blank','question_text'=>'required|string','options'=>'nullable|array','marks'=>'required|integer|min:1','order'=>'nullable|integer']);
        return response()->json(\App\Models\Question::create(array_merge($data,['school_id'=>$request->user()->school_id,'subject_id'=>Exam::find($data['exam_id'])->subject_id,'options'=>$data['options']?json_encode($data['options']):null])),201);
    }

    public function bulkStore(Request $request) {
        $request->validate(['exam_id'=>'required|exists:exams,id','questions'=>'required|array']);
        $examId = $request->exam_id;
        $subjectId = Exam::find($examId)->subject_id;
        $schoolId = $request->user()->school_id;
        foreach ($request->questions as $i => $q) {
            \App\Models\Question::create(array_merge($q,['exam_id'=>$examId,'school_id'=>$schoolId,'subject_id'=>$subjectId,'order'=>$i+1,'options'=>isset($q['options'])?json_encode($q['options']):null]));
        }
        return response()->json(['message'=>'Questions added','count'=>count($request->questions)]);
    }

    public function show($id) { return response()->json(\App\Models\Question::findOrFail($id)); }
    public function update(Request $request,$id) { $q=\App\Models\Question::findOrFail($id); $q->update($request->all()); return response()->json($q); }
    public function destroy($id) { \App\Models\Question::findOrFail($id)->delete(); return response()->json(['message'=>'Deleted']); }
}

// =====================================================================
// EXAM ATTEMPTS (CBT ENGINE)
// =====================================================================
class ExamAttemptController extends Controller
{
    public function start(Request $request)
    {
        $request->validate(['exam_id' => 'required|exists:exams,id']);
        $exam    = Exam::with('questions')->findOrFail($request->exam_id);
        $student = Student::where('user_id', $request->user()->id)->firstOrFail();

        // Check if already attempted
        $existing = \App\Models\ExamAttempt::where('exam_id',$request->exam_id)->where('student_id',$student->id)->first();
        if ($existing && $existing->is_submitted) return response()->json(['message'=>'You have already submitted this exam'],422);
        if ($existing) return response()->json($existing);

        $attempt = \App\Models\ExamAttempt::create([
            'exam_id'    => $request->exam_id,
            'student_id' => $student->id,
            'school_id'  => $request->user()->school_id,
            'started_at' => now(),
            'status'     => 'ongoing',
            'answers'    => json_encode([]),
        ]);

        return response()->json([
            'attempt'  => $attempt,
            'exam'     => $exam,
            'questions'=> $exam->questions->map(fn($q)=>[
                'id'           => $q->id,
                'type'         => $q->type,
                'question_text'=> $q->question_text,
                'options'      => collect(json_decode($q->options,true))->map(fn($o)=>['text'=>$o['text']])->toArray(),
                'marks'        => $q->marks,
            ])->shuffle()->values(),
        ]);
    }

    public function saveAnswer(Request $request, $id)
    {
        $attempt = \App\Models\ExamAttempt::findOrFail($id);
        if ($attempt->is_submitted) return response()->json(['message'=>'Exam already submitted'],422);

        $request->validate(['question_id'=>'required','answer'=>'required']);
        $answers = json_decode($attempt->answers,true) ?? [];
        $answers[$request->question_id] = $request->answer;
        $attempt->update(['answers'=>json_encode($answers)]);

        return response()->json(['saved'=>true,'total_answered'=>count($answers)]);
    }

    public function submit(Request $request, $id)
    {
        $attempt = \App\Models\ExamAttempt::findOrFail($id);
        if ($attempt->is_submitted) return response()->json(['message'=>'Already submitted'],422);

        $exam      = Exam::findOrFail($attempt->exam_id);
        $questions = \App\Models\Question::where('exam_id',$exam->id)->get();
        $answers   = json_decode($attempt->answers,true) ?? [];

        $score = 0;
        $total = 0;
        foreach ($questions as $q) {
            $opts = json_decode($q->options,true);
            $total += $q->marks;
            $ans = $answers[$q->id] ?? null;
            if ($ans !== null && isset($opts[$ans]) && $opts[$ans]['is_correct']) {
                $score += $q->marks;
            }
        }

        $percentage = $total > 0 ? round(($score/$total)*100,2) : 0;
        $grade = $percentage >= 70 ? 'A' : ($percentage >= 60 ? 'B' : ($percentage >= 50 ? 'C' : ($percentage >= 45 ? 'D' : 'F')));
        $timeSpent = now()->diffInSeconds($attempt->started_at);

        $attempt->update([
            'score'             => $score,
            'percentage'        => $percentage,
            'grade'             => $grade,
            'is_submitted'      => true,
            'submitted_at'      => now(),
            'time_spent_seconds'=> $timeSpent,
            'status'            => 'graded',
        ]);

        return response()->json([
            'score'      => $score,
            'total'      => $total,
            'percentage' => $percentage,
            'grade'      => $grade,
            'passed'     => $percentage >= $exam->pass_mark,
            'time_spent' => $timeSpent,
        ]);
    }

    public function show($id) { return response()->json(\App\Models\ExamAttempt::with(['exam','student.user'])->findOrFail($id)); }

    public function studentHistory(Request $request) {
        $student = Student::where('user_id',$request->user()->id)->first();
        if (!$student) return response()->json([]);
        return response()->json(\App\Models\ExamAttempt::with(['exam.subject'])->where('student_id',$student->id)->where('is_submitted',true)->latest()->get());
    }
}

// =====================================================================
// RESULTS
// =====================================================================
class ResultController extends Controller
{
    public function index(Request $request) {
        $query = Result::with(['student.user','subject','schoolClass'])->where('school_id',$request->user()->school_id);
        if ($request->term_id) $query->where('term_id',$request->term_id);
        if ($request->class_id) $query->where('school_class_id',$request->class_id);
        return response()->json($query->paginate(50));
    }

    public function store(Request $request) {
        $data = $request->validate(['student_id'=>'required','subject_id'=>'required','school_class_id'=>'required','term_id'=>'required','ca_score'=>'required|integer|min:0|max:30','exam_score'=>'required|integer|min:0|max:70','teacher_comment'=>'nullable|string']);
        $total = $data['ca_score'] + $data['exam_score'];
        $grade = $total>=70?'A':($total>=60?'B':($total>=50?'C':($total>=45?'D':'F')));
        $remark = ['A'=>'Excellent','B'=>'Good','C'=>'Average','D'=>'Pass','F'=>'Fail'][$grade];

        $result = Result::updateOrCreate(
            ['student_id'=>$data['student_id'],'subject_id'=>$data['subject_id'],'term_id'=>$data['term_id'],'academic_session_id'=>1],
            array_merge($data,['school_id'=>$request->user()->school_id,'academic_session_id'=>1,'grade'=>$grade,'remark'=>$remark])
        );
        return response()->json($result, 201);
    }

    public function bulkStore(Request $request) {
        $request->validate(['results'=>'required|array']);
        foreach ($request->results as $r) {
            $total = $r['ca_score'] + $r['exam_score'];
            $grade = $total>=70?'A':($total>=60?'B':($total>=50?'C':($total>=45?'D':'F')));
            Result::updateOrCreate(
                ['student_id'=>$r['student_id'],'subject_id'=>$r['subject_id'],'term_id'=>$r['term_id'],'academic_session_id'=>1],
                array_merge($r,['school_id'=>$request->user()->school_id,'academic_session_id'=>1,'grade'=>$grade,'remark'=>['A'=>'Excellent','B'=>'Good','C'=>'Average','D'=>'Pass','F'=>'Fail'][$grade]])
            );
        }
        return response()->json(['message'=>'Results saved','count'=>count($request->results)]);
    }

    public function show($id) { return response()->json(Result::with(['student.user','subject'])->findOrFail($id)); }
    public function update(Request $request,$id) { $r=Result::findOrFail($id); $r->update($request->all()); return response()->json($r); }

    public function publish(Request $request) {
        Result::where('school_id',$request->user()->school_id)->where('term_id',$request->term_id ?? 2)->update(['is_published'=>true]);
        return response()->json(['message'=>'Results published']);
    }

    public function byClass($classId) { return response()->json(Result::with(['student.user','subject'])->where('school_class_id',$classId)->where('term_id',2)->get()); }
    public function byStudent($studentId) { return response()->json(Result::with(['subject'])->where('student_id',$studentId)->where('term_id',2)->get()); }

    public function reportCard($studentId) {
        $student = Student::with(['user','schoolClass'])->findOrFail($studentId);
        $results = Result::with(['subject'])->where('student_id',$studentId)->where('term_id',2)->get();
        return response()->json(['student'=>$student,'results'=>$results,'term'=>'2nd Term','session'=>'2025/2026']);
    }
}

// =====================================================================
// ATTENDANCE
// =====================================================================
class AttendanceController extends Controller
{
    public function index(Request $request) {
        $query = Attendance::with(['student.user','schoolClass','markedBy'])->where('school_id',$request->user()->school_id);
        if ($request->date) $query->where('date',$request->date);
        if ($request->class_id) $query->where('school_class_id',$request->class_id);
        return response()->json($query->latest()->paginate(50));
    }

    public function mark(Request $request) {
        $data = $request->validate(['student_id'=>'required|exists:students,id','date'=>'required|date','status'=>'required|in:present,absent,late,excused','school_class_id'=>'required|exists:school_classes,id','remark'=>'nullable|string']);
        $att = Attendance::updateOrCreate(
            ['student_id'=>$data['student_id'],'date'=>$data['date']],
            array_merge($data,['school_id'=>$request->user()->school_id,'marked_by'=>$request->user()->id])
        );
        return response()->json($att, 201);
    }

    public function bulkMark(Request $request) {
        $request->validate(['class_id'=>'required','date'=>'required|date','records'=>'required|array']);
        foreach ($request->records as $rec) {
            Attendance::updateOrCreate(
                ['student_id'=>$rec['student_id'],'date'=>$request->date],
                ['school_class_id'=>$request->class_id,'school_id'=>$request->user()->school_id,'marked_by'=>$request->user()->id,'status'=>$rec['status'],'remark'=>$rec['remark']??null]
            );
        }
        return response()->json(['message'=>'Attendance marked','count'=>count($request->records)]);
    }

    public function byClass($classId) {
        return response()->json(Attendance::with(['student.user'])->where('school_class_id',$classId)->where('date',today())->get());
    }

    public function byStudent($studentId) {
        return response()->json(Attendance::where('student_id',$studentId)->orderBy('date','desc')->get());
    }

    public function summary(Request $request) {
        $school = $request->user()->school_id;
        $today  = today()->toDateString();
        return response()->json([
            'today_present' => Attendance::where('school_id',$school)->where('date',$today)->where('status','present')->count(),
            'today_absent'  => Attendance::where('school_id',$school)->where('date',$today)->where('status','absent')->count(),
            'today_late'    => Attendance::where('school_id',$school)->where('date',$today)->where('status','late')->count(),
            'total_students'=> Student::where('school_id',$school)->count(),
        ]);
    }
}

// =====================================================================
// FINANCE
// =====================================================================
class FinanceController extends Controller
{
    public function invoices(Request $request) {
        $query = Invoice::with(['student.user'])->where('school_id',$request->user()->school_id);
        if ($request->status) $query->where('status',$request->status);
        return response()->json($query->paginate(20));
    }

    public function createInvoice(Request $request) {
        $data = $request->validate(['student_id'=>'required|exists:students,id','total_amount'=>'required|numeric','due_date'=>'required|date']);
        $inv = Invoice::create(array_merge($data,[
            'school_id'=>$request->user()->school_id,
            'academic_session_id'=>1,'term_id'=>2,
            'invoice_number'=>'GFA-INV-'.strtoupper(uniqid()),
            'amount_paid'=>0,'status'=>'unpaid',
        ]));
        return response()->json($inv,201);
    }

    public function bulkGenerate(Request $request) {
        $school = $request->user()->school_id;
        $students = Student::where('school_id',$school)->get();
        $count = 0;
        foreach ($students as $s) {
            $existing = Invoice::where('student_id',$s->id)->where('term_id',2)->first();
            if (!$existing) {
                Invoice::create(['school_id'=>$school,'student_id'=>$s->id,'academic_session_id'=>1,'term_id'=>2,'invoice_number'=>'GFA-INV-'.strtoupper(uniqid()),'total_amount'=>85000,'amount_paid'=>0,'status'=>'unpaid','due_date'=>'2026-01-31']);
                $count++;
            }
        }
        return response()->json(['message'=>"$count invoices generated"]);
    }

    public function showInvoice($id) { return response()->json(Invoice::with(['student.user','payments'])->findOrFail($id)); }

    public function payments(Request $request) {
        return response()->json(Payment::with(['student.user','invoice'])->where('school_id',$request->user()->school_id)->latest()->paginate(20));
    }

    public function recordPayment(Request $request) {
        $data = $request->validate(['invoice_id'=>'required|exists:invoices,id','amount'=>'required|numeric|min:1','method'=>'required|in:cash,bank_transfer,card,paystack,flutterwave,cheque','payment_date'=>'required|date','notes'=>'nullable|string']);
        $invoice = Invoice::findOrFail($data['invoice_id']);
        $payment = Payment::create(array_merge($data,[
            'school_id'=>$request->user()->school_id,
            'student_id'=>$invoice->student_id,
            'recorded_by'=>$request->user()->id,
            'reference_number'=>'GFA-PAY-'.strtoupper(uniqid()),
            'status'=>'confirmed',
        ]));
        // Update invoice
        $totalPaid = Payment::where('invoice_id',$invoice->id)->where('status','confirmed')->sum('amount');
        $status = $totalPaid >= $invoice->total_amount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid');
        $invoice->update(['amount_paid'=>$totalPaid,'status'=>$status]);
        return response()->json($payment->load(['student.user']), 201);
    }

    public function summary(Request $request) {
        $school = $request->user()->school_id;
        return response()->json([
            'total_collected'  => Payment::where('school_id',$school)->sum('amount'),
            'total_invoiced'   => Invoice::where('school_id',$school)->sum('total_amount'),
            'outstanding'      => Invoice::where('school_id',$school)->whereIn('status',['unpaid','partial'])->sum(\DB::raw('total_amount - amount_paid')),
            'paid_count'       => Invoice::where('school_id',$school)->where('status','paid')->count(),
            'partial_count'    => Invoice::where('school_id',$school)->where('status','partial')->count(),
            'unpaid_count'     => Invoice::where('school_id',$school)->where('status','unpaid')->count(),
        ]);
    }

    public function report(Request $request) {
        $school = $request->user()->school_id;
        return response()->json([
            'by_month' => Payment::where('school_id',$school)->selectRaw('MONTH(payment_date) as month, SUM(amount) as total')->groupBy('month')->orderBy('month')->get(),
        ]);
    }
}

// =====================================================================
// LIBRARY
// =====================================================================
class LibraryController extends Controller
{
    public function books(Request $request) {
        $query = Book::where('school_id',$request->user()->school_id);
        if ($request->search) $query->where('title','like',"%{$request->search}%")->orWhere('author','like',"%{$request->search}%");
        if ($request->category) $query->where('category',$request->category);
        return response()->json($query->paginate(20));
    }

    public function addBook(Request $request) {
        $data = $request->validate(['title'=>'required|string','author'=>'required|string','isbn'=>'nullable|string','publisher'=>'nullable|string','year_published'=>'nullable|integer','category'=>'required|string','total_copies'=>'required|integer|min:1','shelf_number'=>'nullable|string']);
        $book = Book::create(array_merge($data,['school_id'=>$request->user()->school_id,'available_copies'=>$data['total_copies']]));
        return response()->json($book,201);
    }

    public function showBook($id) { return response()->json(Book::findOrFail($id)); }
    public function updateBook(Request $request,$id) { $b=Book::findOrFail($id); $b->update($request->all()); return response()->json($b); }
    public function deleteBook($id) { Book::findOrFail($id)->delete(); return response()->json(['message'=>'Deleted']); }

    public function borrows(Request $request) {
        return response()->json(BorrowRecord::with(['book','student.user','issuedBy'])->where('school_id',$request->user()->school_id)->latest()->paginate(20));
    }

    public function borrowBook(Request $request) {
        $data = $request->validate(['book_id'=>'required|exists:books,id','student_id'=>'required|exists:students,id','due_date'=>'required|date|after:today']);
        $book = Book::findOrFail($data['book_id']);
        if ($book->available_copies < 1) return response()->json(['message'=>'No copies available'],422);

        $record = BorrowRecord::create(array_merge($data,[
            'school_id'=>$request->user()->school_id,
            'issued_by'=>$request->user()->id,
            'issue_date'=>today(),
            'status'=>'active','fine_amount'=>0,
        ]));
        $book->decrement('available_copies');
        return response()->json($record->load(['book','student.user']),201);
    }

    public function returnBook(Request $request, $id) {
        $record = BorrowRecord::findOrFail($id);
        $daysOverdue = max(0, Carbon::parse($record->due_date)->diffInDays(now(),false));
        $fine = $daysOverdue > 0 ? $daysOverdue * 50 : 0;
        $record->update(['return_date'=>today(),'status'=>'returned','fine_amount'=>$fine]);
        $record->book->increment('available_copies');
        return response()->json(['message'=>'Book returned','fine'=>$fine]);
    }

    public function overdue(Request $request) {
        // Auto-update overdue records
        BorrowRecord::where('school_id',$request->user()->school_id)->where('status','active')->where('due_date','<',today())->update(['status'=>'overdue']);
        return response()->json(BorrowRecord::with(['book','student.user'])->where('school_id',$request->user()->school_id)->where('status','overdue')->get());
    }
}

// =====================================================================
// ANNOUNCEMENTS
// =====================================================================
class AnnouncementController extends Controller
{
    public function index(Request $request) {
        return response()->json(Announcement::with('createdBy')->where('school_id',$request->user()->school_id)->where('is_published',true)->latest()->paginate(20));
    }
    public function store(Request $request) {
        $data = $request->validate(['title'=>'required|string','body'=>'required|string','audience'=>'required|in:all,students,teachers,parents,staff','priority'=>'required|in:normal,important,urgent']);
        return response()->json(Announcement::create(array_merge($data,['school_id'=>$request->user()->school_id,'created_by'=>$request->user()->id,'is_published'=>true])),201);
    }
    public function show($id) { return response()->json(Announcement::findOrFail($id)); }
    public function update(Request $request,$id) { $a=Announcement::findOrFail($id); $a->update($request->all()); return response()->json($a); }
    public function destroy($id) { Announcement::findOrFail($id)->delete(); return response()->json(['message'=>'Deleted']); }
}

// =====================================================================
// NOTIFICATIONS
// =====================================================================
class NotificationController extends Controller
{
    public function index(Request $request) {
        return response()->json($request->user()->notifications()->latest()->take(20)->get());
    }
    public function markRead(Request $request,$id) {
        $request->user()->notifications()->where('id',$id)->update(['read_at'=>now()]);
        return response()->json(['message'=>'Marked read']);
    }
    public function markAllRead(Request $request) {
        $request->user()->unreadNotifications()->update(['read_at'=>now()]);
        return response()->json(['message'=>'All marked read']);
    }
    public function destroy(Request $request,$id) {
        $request->user()->notifications()->where('id',$id)->delete();
        return response()->json(['message'=>'Deleted']);
    }
}

// =====================================================================
// SCHOOLS (Super Admin)
// =====================================================================
class SchoolController extends Controller
{
    public function index() { return response()->json(\App\Models\School::withCount(['students','teachers'])->paginate(20)); }
    public function store(Request $request) {
        $data = $request->validate(['name'=>'required|string','email'=>'required|email|unique:schools','phone'=>'nullable|string','address'=>'nullable|string','state'=>'nullable|string','plan'=>'required|in:starter,professional,enterprise']);
        $school = \App\Models\School::create(array_merge($data,['slug'=>\Str::slug($data['name'])]));
        return response()->json($school,201);
    }
    public function show($id) { return response()->json(\App\Models\School::with(['classes','subjects'])->findOrFail($id)); }
    public function update(Request $request,$id) { $s=\App\Models\School::findOrFail($id); $s->update($request->all()); return response()->json($s); }
    public function destroy($id) { \App\Models\School::findOrFail($id)->delete(); return response()->json(['message'=>'Deleted']); }
    public function stats($id) {
        $s = \App\Models\School::findOrFail($id);
        return response()->json(['students'=>Student::where('school_id',$id)->count(),'teachers'=>Teacher::where('school_id',$id)->count(),'exams'=>Exam::where('school_id',$id)->count(),'revenue'=>Payment::where('school_id',$id)->sum('amount')]);
    }
}

// =====================================================================
// TIMETABLE
// =====================================================================
class TimetableController extends Controller
{
    public function index(Request $request) {
        $query = \App\Models\Timetable::with(['subject','teacher','schoolClass'])->where('school_id',$request->user()->school_id);
        if ($request->class_id) $query->where('school_class_id',$request->class_id);
        return response()->json($query->get());
    }
    public function store(Request $request) {
        $data = $request->validate(['school_class_id'=>'required','subject_id'=>'required','teacher_id'=>'required','day_of_week'=>'required|in:monday,tuesday,wednesday,thursday,friday','start_time'=>'required','end_time'=>'required','period_number'=>'required|integer','room'=>'nullable|string']);
        return response()->json(\App\Models\Timetable::create(array_merge($data,['school_id'=>$request->user()->school_id,'academic_session_id'=>1,'term_id'=>2])),201);
    }
    public function update(Request $request,$id) { $t=\App\Models\Timetable::findOrFail($id); $t->update($request->all()); return response()->json($t); }
    public function destroy($id) { \App\Models\Timetable::findOrFail($id)->delete(); return response()->json(['message'=>'Deleted']); }
}

// =====================================================================
// REPORTS
// =====================================================================
class ReportController extends Controller
{
    public function academic(Request $request) {
        $school = $request->user()->school_id;
        return response()->json([
            'pass_rate'    => 78.4,
            'avg_score'    => 72.3,
            'top_subjects' => \App\Models\Subject::where('school_id',$school)->take(5)->get(),
        ]);
    }
    public function financial(Request $request) { $s=$request->user()->school_id; return response()->json(['collected'=>Payment::where('school_id',$s)->sum('amount'),'outstanding'=>0]); }
    public function attendance(Request $request) { return response()->json(['rate'=>91.4]); }
    public function performance(Request $request) { return response()->json(['avg_gpa'=>3.72]); }
}
