<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $fillable = ['school_id','first_name','last_name','email','phone','password','role','avatar','date_of_birth','gender','address','state','is_active','last_login_at'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = ['is_active'=>'boolean','last_login_at'=>'datetime','date_of_birth'=>'date'];
    public function school()   { return $this->belongsTo(School::class); }
    public function student()  { return $this->hasOne(Student::class); }
    public function teacher()  { return $this->hasOne(Teacher::class); }
    public function getFullNameAttribute() { return "$this->first_name $this->last_name"; }
}

class School extends Model
{
    use SoftDeletes;
    protected $fillable = ['name','slug','email','phone','address','state','country','logo','stamp','motto','website','proprietor','principal','type','level','plan','status','current_session','current_term','subscription_expires_at'];
    public function students()       { return $this->hasMany(Student::class); }
    public function teachers()       { return $this->hasMany(Teacher::class); }
    public function classes()        { return $this->hasMany(SchoolClass::class); }
    public function subjects()       { return $this->hasMany(Subject::class); }
    public function exams()          { return $this->hasMany(Exam::class); }
    public function announcements()  { return $this->hasMany(Announcement::class); }
    public function gradingSystems() { return $this->hasMany(GradingSystem::class)->orderBy('sort_order'); }

    public function getGradeForScore(int $score): array
    {
        $grading = $this->gradingSystems()
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();
        if (!$grading) return ['grade'=>'F9','remark'=>'Fail','category'=>'Fail'];
        return ['grade'=>$grading->grade,'remark'=>$grading->remark,'category'=>$grading->category];
    }
}

class GradingSystem extends Model
{
    protected $table    = 'grading_systems';
    protected $fillable = ['school_id','grade','min_score','max_score','remark','category','sort_order'];
    public function school() { return $this->belongsTo(School::class); }
}

class Student extends Model
{
    protected $fillable = ['user_id','school_id','school_class_id','admission_number','admission_date','parent_id','passport_photo','parent_name','parent_phone','parent_email','parent_relationship','state_of_origin','religion','blood_group','medical_notes'];
    protected $appends  = ['passport_url'];
    public function user()        { return $this->belongsTo(User::class); }
    public function school()      { return $this->belongsTo(School::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class); }
    public function parent()      { return $this->belongsTo(User::class, 'parent_id'); }
    public function results()     { return $this->hasMany(Result::class); }
    public function attendance()  { return $this->hasMany(Attendance::class); }
    public function invoices()    { return $this->hasMany(Invoice::class); }
    public function borrows()     { return $this->hasMany(BorrowRecord::class); }
    public function examAttempts(){ return $this->hasMany(ExamAttempt::class); }
    public function getPassportUrlAttribute(): ?string
    {
        if (!$this->passport_photo) return null;
        return url('storage/' . $this->passport_photo);
    }
}

class Teacher extends Model
{
    protected $fillable = ['user_id','school_id','staff_id','qualification','specialization','date_joined','employment_type'];
    public function user()    { return $this->belongsTo(User::class); }
    public function school()  { return $this->belongsTo(School::class); }
    public function classes() { return $this->hasMany(SchoolClass::class, 'form_teacher_id', 'user_id'); }
}

class SchoolClass extends Model
{
    protected $table    = 'school_classes';
    protected $fillable = ['school_id','name','level','arm','form_teacher_id','capacity'];
    public function school()      { return $this->belongsTo(School::class); }
    public function formTeacher() { return $this->belongsTo(User::class, 'form_teacher_id'); }
    public function students()    { return $this->hasMany(Student::class); }
    public function subjects()    { return $this->belongsToMany(Subject::class, 'class_subjects', 'school_class_id', 'subject_id')->withPivot('teacher_id'); }
    public function timetables()  { return $this->hasMany(Timetable::class); }
    public function exams()       { return $this->hasMany(Exam::class); }
}

class Subject extends Model
{
    protected $fillable = ['school_id','name','code','category','ca_weight','exam_weight'];
    public function school()        { return $this->belongsTo(School::class); }
    public function classSubjects() { return $this->hasMany(ClassSubject::class); }
    public function results()       { return $this->hasMany(Result::class); }
    public function exams()         { return $this->hasMany(Exam::class); }
}

class ClassSubject extends Model
{
    protected $table    = 'class_subjects';
    protected $fillable = ['school_class_id','subject_id','teacher_id'];
    public $timestamps  = false;
}

class AcademicSession extends Model
{
    protected $table    = 'academic_sessions';
    protected $fillable = ['school_id','name','label','start_date','end_date','is_current'];
    protected $casts    = ['is_current'=>'boolean','start_date'=>'date','end_date'=>'date'];
}

class Term extends Model
{
    protected $fillable = ['school_id','academic_session_id','name','start_date','end_date','is_current'];
    protected $casts    = ['is_current'=>'boolean'];
}

class Exam extends Model
{
    use SoftDeletes;
    protected $fillable = ['school_id','school_class_id','subject_id','created_by','academic_session_id','term_id','title','instructions','duration_minutes','total_marks','pass_mark','num_questions','randomize_questions','randomize_options','show_results_immediately','allow_review','anti_cheat','status','starts_at','ends_at'];
    protected $casts    = ['randomize_questions'=>'boolean','randomize_options'=>'boolean','show_results_immediately'=>'boolean','allow_review'=>'boolean','anti_cheat'=>'boolean','starts_at'=>'datetime','ends_at'=>'datetime'];
    public function school()      { return $this->belongsTo(School::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class); }
    public function subject()     { return $this->belongsTo(Subject::class); }
    public function createdBy()   { return $this->belongsTo(User::class, 'created_by'); }
    public function questions()   { return $this->hasMany(Question::class); }
    public function attempts()    { return $this->hasMany(ExamAttempt::class); }
}

class Question extends Model
{
    protected $fillable = ['exam_id','school_id','subject_id','type','question_text','image','options','correct_answer','explanation','marks','order'];
    public function exam()    { return $this->belongsTo(Exam::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
}

class ExamAttempt extends Model
{
    protected $table    = 'exam_attempts';
    protected $fillable = ['exam_id','student_id','school_id','started_at','submitted_at','score','percentage','grade','is_submitted','time_spent_seconds','answers','status'];
    protected $casts    = ['is_submitted'=>'boolean','started_at'=>'datetime','submitted_at'=>'datetime'];
    public function exam()    { return $this->belongsTo(Exam::class); }
    public function student() { return $this->belongsTo(Student::class); }
    public function school()  { return $this->belongsTo(School::class); }
}

class Result extends Model
{
    protected $fillable = ['school_id','student_id','school_class_id','subject_id','academic_session_id','term_id','ca_score','exam_score','grade','waec_grade','remark','position','class_position','arm_position','subject_position','teacher_comment','is_published','is_locked','is_approved','approved_by','approved_at'];
    protected $casts    = ['is_published'=>'boolean','is_locked'=>'boolean','is_approved'=>'boolean','approved_at'=>'datetime'];
    protected $appends  = ['total_score'];
    public function getTotalScoreAttribute() { return ($this->ca_score ?? 0) + ($this->exam_score ?? 0); }
    public function student()    { return $this->belongsTo(Student::class); }
    public function subject()    { return $this->belongsTo(Subject::class); }
    public function schoolClass(){ return $this->belongsTo(SchoolClass::class); }
    public function approvedBy() { return $this->belongsTo(User::class, 'approved_by'); }
}

class Attendance extends Model
{
    protected $fillable = ['school_id','student_id','school_class_id','marked_by','date','status','remark'];
    protected $casts    = ['date'=>'date'];
    public function student()    { return $this->belongsTo(Student::class); }
    public function schoolClass(){ return $this->belongsTo(SchoolClass::class); }
    public function markedBy()   { return $this->belongsTo(User::class, 'marked_by'); }
}

class FeeCategory extends Model
{
    protected $table    = 'fee_categories';
    protected $fillable = ['school_id','name','description'];
}

class Invoice extends Model
{
    protected $fillable = ['school_id','student_id','academic_session_id','term_id','invoice_number','total_amount','amount_paid','status','due_date'];
    protected $casts    = ['due_date'=>'date'];
    protected $appends  = ['balance'];
    public function getBalanceAttribute() { return ($this->total_amount ?? 0) - ($this->amount_paid ?? 0); }
    public function student()  { return $this->belongsTo(Student::class); }
    public function payments() { return $this->hasMany(Payment::class); }
}

class Payment extends Model
{
    protected $fillable = ['school_id','invoice_id','student_id','recorded_by','reference_number','amount','method','payment_date','bank_name','notes','status'];
    protected $casts    = ['payment_date'=>'date'];
    public function student()    { return $this->belongsTo(Student::class); }
    public function invoice()    { return $this->belongsTo(Invoice::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
}

class Book extends Model
{
    protected $fillable = ['school_id','title','author','isbn','publisher','year_published','category','total_copies','available_copies','cover_image','description','shelf_number'];
    public function school()        { return $this->belongsTo(School::class); }
    public function borrowRecords() { return $this->hasMany(BorrowRecord::class); }
}

class BorrowRecord extends Model
{
    protected $table    = 'borrow_records';
    protected $fillable = ['school_id','book_id','student_id','issued_by','issue_date','due_date','return_date','fine_amount','fine_paid','status','notes'];
    protected $casts    = ['issue_date'=>'date','due_date'=>'date','return_date'=>'date','fine_paid'=>'boolean'];
    public function book()     { return $this->belongsTo(Book::class); }
    public function student()  { return $this->belongsTo(Student::class); }
    public function issuedBy() { return $this->belongsTo(User::class, 'issued_by'); }
}

class Timetable extends Model
{
    protected $fillable = ['school_id','school_class_id','subject_id','teacher_id','academic_session_id','term_id','day_of_week','start_time','end_time','period_number','room'];
    public function school()      { return $this->belongsTo(School::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class); }
    public function subject()     { return $this->belongsTo(Subject::class); }
    public function teacher()     { return $this->belongsTo(User::class, 'teacher_id'); }
}

class Announcement extends Model
{
    use SoftDeletes;
    protected $fillable = ['school_id','created_by','title','body','audience','priority','is_published','expires_at'];
    protected $casts    = ['is_published'=>'boolean','expires_at'=>'date'];
    public function school()    { return $this->belongsTo(School::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}

class ActivityLog extends Model
{
    protected $table    = 'activity_logs';
    protected $fillable = ['school_id','user_id','action','model_type','model_id','description','ip_address','user_agent'];
}

class Material extends Model
{
    use SoftDeletes;
    protected $fillable = ['school_id','uploaded_by','subject_id','school_class_id','title','description','file_path','file_name','file_type','file_size','external_url','audience','is_published','download_count'];
    protected $casts    = ['is_published'=>'boolean'];
    public function school()      { return $this->belongsTo(School::class); }
    public function uploadedBy()  { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function subject()     { return $this->belongsTo(Subject::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class); }
}
