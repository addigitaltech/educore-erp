<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('schools')->insert([
            [
                'id' => 1,
                'name' => 'Greenfield Academy',
                'slug' => 'greenfield-academy',
                'email' => 'info@greenfield.edu',
                'phone' => '+234 801 234 5678',
                'address' => '123 Education Avenue, Lekki Phase 1',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'type' => 'mixed',
                'level' => 'secondary',
                'plan' => 'enterprise',
                'status' => 'active',
                'current_session' => '2025/2026',
                'current_term' => '2nd',
                'subscription_expires_at' => Carbon::now()->addYear(),
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Sunrise International School',
                'slug' => 'sunrise-international',
                'email' => 'info@sunrise.edu',
                'phone' => '+234 802 345 6789',
                'address' => '45 Wuse Zone 6',
                'state' => 'Abuja',
                'country' => 'Nigeria',
                'type' => 'mixed',
                'level' => 'both',
                'plan' => 'professional',
                'status' => 'active',
                'current_session' => '2025/2026',
                'current_term' => '2nd',
                'subscription_expires_at' => Carbon::now()->addMonths(8),
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }
}

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // Super Admin (no school)
            ['id'=>1,'school_id'=>null,'first_name'=>'Alex','last_name'=>'Morgan','email'=>'alex@educore.io','role'=>'superadmin','password'=>Hash::make('password')],
            // Greenfield Admin
            ['id'=>2,'school_id'=>1,'first_name'=>'John','last_name'=>'Doe','email'=>'john@greenfield.edu','role'=>'admin','password'=>Hash::make('password')],
            // Teachers
            ['id'=>3,'school_id'=>1,'first_name'=>'Samuel','last_name'=>'Adegoke','email'=>'s.adegoke@greenfield.edu','role'=>'teacher','password'=>Hash::make('password')],
            ['id'=>4,'school_id'=>1,'first_name'=>'Blessing','last_name'=>'Nwosu','email'=>'b.nwosu@greenfield.edu','role'=>'teacher','password'=>Hash::make('password')],
            ['id'=>5,'school_id'=>1,'first_name'=>'Chidi','last_name'=>'Eze','email'=>'c.eze@greenfield.edu','role'=>'teacher','password'=>Hash::make('password')],
            ['id'=>6,'school_id'=>1,'first_name'=>'Aisha','last_name'=>'Yusuf','email'=>'a.yusuf@greenfield.edu','role'=>'teacher','password'=>Hash::make('password')],
            // Sarah (main teacher demo)
            ['id'=>7,'school_id'=>1,'first_name'=>'Sarah','last_name'=>'Williams','email'=>'sarah@greenfield.edu','role'=>'teacher','password'=>Hash::make('password')],
            // Students
            ['id'=>8,'school_id'=>1,'first_name'=>'Michael','last_name'=>'Chen','email'=>'michael@student.edu','role'=>'student','password'=>Hash::make('password')],
            ['id'=>9,'school_id'=>1,'first_name'=>'Amara','last_name'=>'Johnson','email'=>'amara@student.edu','role'=>'student','password'=>Hash::make('password')],
            ['id'=>10,'school_id'=>1,'first_name'=>'David','last_name'=>'Okafor','email'=>'david@student.edu','role'=>'student','password'=>Hash::make('password')],
            ['id'=>11,'school_id'=>1,'first_name'=>'Fatima','last_name'=>'Al-Hassan','email'=>'fatima@student.edu','role'=>'student','password'=>Hash::make('password')],
            ['id'=>12,'school_id'=>1,'first_name'=>'Zainab','last_name'=>'Bello','email'=>'zainab@student.edu','role'=>'student','password'=>Hash::make('password')],
            ['id'=>13,'school_id'=>1,'first_name'=>'Tunde','last_name'=>'Adewale','email'=>'tunde@student.edu','role'=>'student','password'=>Hash::make('password')],
            ['id'=>14,'school_id'=>1,'first_name'=>'Ngozi','last_name'=>'Obi','email'=>'ngozi@student.edu','role'=>'student','password'=>Hash::make('password')],
            ['id'=>15,'school_id'=>1,'first_name'=>'Ibrahim','last_name'=>'Musa','email'=>'ibrahim@student.edu','role'=>'student','password'=>Hash::make('password')],
            // Parent
            ['id'=>16,'school_id'=>1,'first_name'=>'Linda','last_name'=>'Chen','email'=>'linda@email.com','role'=>'parent','password'=>Hash::make('password')],
            // Accountant
            ['id'=>17,'school_id'=>1,'first_name'=>'James','last_name'=>'Obi','email'=>'james@greenfield.edu','role'=>'accountant','password'=>Hash::make('password')],
            // Librarian
            ['id'=>18,'school_id'=>1,'first_name'=>'Grace','last_name'=>'Adams','email'=>'grace@greenfield.edu','role'=>'librarian','password'=>Hash::make('password')],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert(array_merge($user, [
                'is_active' => true,
                'gender' => ['male','female'][rand(0,1)],
                'created_at' => now(), 'updated_at' => now(),
            ]));
        }
    }
}

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        // Academic Session
        DB::table('academic_sessions')->insert([
            ['id'=>1,'school_id'=>1,'name'=>'2025/2026','start_date'=>'2025-09-01','end_date'=>'2026-07-31','is_current'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'school_id'=>1,'name'=>'2024/2025','start_date'=>'2024-09-01','end_date'=>'2025-07-31','is_current'=>false,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // Terms
        DB::table('terms')->insert([
            ['id'=>1,'school_id'=>1,'academic_session_id'=>1,'name'=>'1st','start_date'=>'2025-09-08','end_date'=>'2025-12-13','is_current'=>false,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'school_id'=>1,'academic_session_id'=>1,'name'=>'2nd','start_date'=>'2026-01-05','end_date'=>'2026-04-03','is_current'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'school_id'=>1,'academic_session_id'=>1,'name'=>'3rd','start_date'=>'2026-04-20','end_date'=>'2026-07-25','is_current'=>false,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // Classes
        $classes = [
            ['id'=>1,'name'=>'JSS 1A','level'=>'JSS','arm'=>1,'form_teacher_id'=>6,'capacity'=>42],
            ['id'=>2,'name'=>'JSS 1B','level'=>'JSS','arm'=>2,'form_teacher_id'=>4,'capacity'=>40],
            ['id'=>3,'name'=>'JSS 2A','level'=>'JSS','arm'=>1,'form_teacher_id'=>5,'capacity'=>38],
            ['id'=>4,'name'=>'JSS 3A','level'=>'JSS','arm'=>1,'form_teacher_id'=>7,'capacity'=>45],
            ['id'=>5,'name'=>'SS 1B','level'=>'SS','arm'=>2,'form_teacher_id'=>4,'capacity'=>35],
            ['id'=>6,'name'=>'SS 2A','level'=>'SS','arm'=>1,'form_teacher_id'=>3,'capacity'=>42],
            ['id'=>7,'name'=>'SS 2B','level'=>'SS','arm'=>2,'form_teacher_id'=>5,'capacity'=>40],
            ['id'=>8,'name'=>'SS 3A','level'=>'SS','arm'=>1,'form_teacher_id'=>3,'capacity'=>38],
        ];
        foreach ($classes as $c) {
            DB::table('school_classes')->insert(array_merge($c, ['school_id'=>1,'created_at'=>now(),'updated_at'=>now()]));
        }

        // Subjects
        $subjects = [
            ['id'=>1,'name'=>'Mathematics','code'=>'MTH','category'=>'Core'],
            ['id'=>2,'name'=>'English Language','code'=>'ENG','category'=>'Core'],
            ['id'=>3,'name'=>'Physics','code'=>'PHY','category'=>'Science'],
            ['id'=>4,'name'=>'Chemistry','code'=>'CHM','category'=>'Science'],
            ['id'=>5,'name'=>'Biology','code'=>'BIO','category'=>'Science'],
            ['id'=>6,'name'=>'Economics','code'=>'ECO','category'=>'Commercial'],
            ['id'=>7,'name'=>'Government','code'=>'GOV','category'=>'Arts'],
            ['id'=>8,'name'=>'Geography','code'=>'GEO','category'=>'Arts'],
            ['id'=>9,'name'=>'Further Mathematics','code'=>'FMT','category'=>'Science'],
            ['id'=>10,'name'=>'Agricultural Science','code'=>'AGR','category'=>'Science'],
        ];
        foreach ($subjects as $s) {
            DB::table('subjects')->insert(array_merge($s, ['school_id'=>1,'ca_weight'=>30,'exam_weight'=>70,'created_at'=>now(),'updated_at'=>now()]));
        }

        // Class Subjects (SS 2A has all core subjects)
        $classSubjects = [
            [6,1,3],[6,2,4],[6,3,5],[6,4,6],[6,5,7],[6,6,6],[6,7,3],[6,8,4],
            [8,1,3],[8,2,4],[8,3,5],[8,4,6],[8,5,7],[8,6,6],
            [4,1,3],[4,2,4],[4,3,5],[4,4,6],
        ];
        foreach ($classSubjects as [$classId,$subjectId,$teacherId]) {
            DB::table('class_subjects')->insert(['school_class_id'=>$classId,'subject_id'=>$subjectId,'teacher_id'=>$teacherId]);
        }
    }
}

class StudentTeacherSeeder extends Seeder
{
    public function run(): void
    {
        // Students
        $students = [
            ['user_id'=>8,'school_class_id'=>6,'admission_number'=>'GFA/2023/042','admission_date'=>'2023-09-04','parent_id'=>16],
            ['user_id'=>9,'school_class_id'=>4,'admission_number'=>'GFA/2023/001','admission_date'=>'2023-09-04','parent_id'=>null],
            ['user_id'=>10,'school_class_id'=>4,'admission_number'=>'GFA/2023/002','admission_date'=>'2023-09-04','parent_id'=>null],
            ['user_id'=>11,'school_class_id'=>5,'admission_number'=>'GFA/2023/003','admission_date'=>'2023-09-04','parent_id'=>null],
            ['user_id'=>12,'school_class_id'=>1,'admission_number'=>'GFA/2024/011','admission_date'=>'2024-09-02','parent_id'=>null],
            ['user_id'=>13,'school_class_id'=>7,'admission_number'=>'GFA/2022/078','admission_date'=>'2022-09-05','parent_id'=>null],
            ['user_id'=>14,'school_class_id'=>8,'admission_number'=>'GFA/2021/033','admission_date'=>'2021-09-06','parent_id'=>null],
            ['user_id'=>15,'school_class_id'=>3,'admission_number'=>'GFA/2023/056','admission_date'=>'2023-09-04','parent_id'=>null],
        ];
        foreach ($students as $i => $s) {
            DB::table('students')->insert(array_merge($s, ['id'=>$i+1,'school_id'=>1,'created_at'=>now(),'updated_at'=>now()]));
        }

        // Teachers
        $teachers = [
            ['user_id'=>3,'staff_id'=>'GFA/TCH/001','qualification'=>'M.Sc Mathematics','specialization'=>'Mathematics','date_joined'=>'2018-09-01','employment_type'=>'full_time'],
            ['user_id'=>4,'staff_id'=>'GFA/TCH/002','qualification'=>'M.A English','specialization'=>'English Language','date_joined'=>'2019-01-15','employment_type'=>'full_time'],
            ['user_id'=>5,'staff_id'=>'GFA/TCH/003','qualification'=>'B.Sc Physics','specialization'=>'Physics & Chemistry','date_joined'=>'2020-09-01','employment_type'=>'full_time'],
            ['user_id'=>6,'staff_id'=>'GFA/TCH/004','qualification'=>'B.Sc Biology','specialization'=>'Biology','date_joined'=>'2021-01-10','employment_type'=>'full_time'],
            ['user_id'=>7,'staff_id'=>'GFA/TCH/005','qualification'=>'B.Ed Mathematics','specialization'=>'Mathematics & English','date_joined'=>'2022-09-01','employment_type'=>'full_time'],
        ];
        foreach ($teachers as $i => $t) {
            DB::table('teachers')->insert(array_merge($t, ['id'=>$i+1,'school_id'=>1,'created_at'=>now(),'updated_at'=>now()]));
        }
    }
}

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        // Exams
        DB::table('exams')->insert([
            ['id'=>1,'school_id'=>1,'school_class_id'=>6,'subject_id'=>1,'created_by'=>3,'academic_session_id'=>1,'term_id'=>2,'title'=>'Mathematics Mid-Term Examination','duration_minutes'=>90,'total_marks'=>100,'pass_mark'=>50,'num_questions'=>10,'randomize_questions'=>true,'status'=>'published','starts_at'=>'2026-03-20 08:00:00','ends_at'=>'2026-03-20 18:00:00','created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'school_id'=>1,'school_class_id'=>4,'subject_id'=>2,'created_by'=>4,'academic_session_id'=>1,'term_id'=>2,'title'=>'English Language Test','duration_minutes'=>60,'total_marks'=>100,'pass_mark'=>50,'num_questions'=>40,'randomize_questions'=>true,'status'=>'published','starts_at'=>'2026-03-18 09:00:00','ends_at'=>'2026-03-18 18:00:00','created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'school_id'=>1,'school_class_id'=>8,'subject_id'=>3,'created_by'=>5,'academic_session_id'=>1,'term_id'=>2,'title'=>'Physics Practical Exam','duration_minutes'=>120,'total_marks'=>100,'pass_mark'=>50,'num_questions'=>30,'randomize_questions'=>true,'status'=>'completed','starts_at'=>'2026-03-10 08:00:00','ends_at'=>'2026-03-10 18:00:00','created_at'=>now(),'updated_at'=>now()],
        ]);

        // Questions for Exam 1 (Mathematics)
        $questions = [
            ['type'=>'mcq','question_text'=>'Which of the following is the quadratic formula?','options'=>json_encode([['text'=>'x = (-b ± √(b²-4ac)) / 2a','is_correct'=>true],['text'=>'x = (b ± √(b²+4ac)) / 2a','is_correct'=>false],['text'=>'x = (-b ± √(b²-4ac)) / a','is_correct'=>false],['text'=>'x = (-b ∓ √(b²-4ac)) / 2a','is_correct'=>false]]),'marks'=>2,'order'=>1],
            ['type'=>'mcq','question_text'=>'What is the derivative of sin(x)?','options'=>json_encode([['text'=>'cos(x)','is_correct'=>true],['text'=>'-cos(x)','is_correct'=>false],['text'=>'-sin(x)','is_correct'=>false],['text'=>'tan(x)','is_correct'=>false]]),'marks'=>2,'order'=>2],
            ['type'=>'mcq','question_text'=>'What is the value of log₁₀(1000)?','options'=>json_encode([['text'=>'2','is_correct'=>false],['text'=>'3','is_correct'=>true],['text'=>'4','is_correct'=>false],['text'=>'10','is_correct'=>false]]),'marks'=>2,'order'=>3],
            ['type'=>'true_false','question_text'=>'The sum of angles in a triangle equals 180°.','options'=>json_encode([['text'=>'True','is_correct'=>true],['text'=>'False','is_correct'=>false]]),'marks'=>1,'order'=>4],
            ['type'=>'mcq','question_text'=>'If f(x) = x³ - 3x + 2, what is f(1)?','options'=>json_encode([['text'=>'0','is_correct'=>true],['text'=>'1','is_correct'=>false],['text'=>'2','is_correct'=>false],['text'=>'-1','is_correct'=>false]]),'marks'=>2,'order'=>5],
            ['type'=>'mcq','question_text'=>'What is the area of a circle with radius 7 cm? (π ≈ 22/7)','options'=>json_encode([['text'=>'154 cm²','is_correct'=>true],['text'=>'44 cm²','is_correct'=>false],['text'=>'49 cm²','is_correct'=>false],['text'=>'22 cm²','is_correct'=>false]]),'marks'=>3,'order'=>6],
            ['type'=>'true_false','question_text'=>'A prime number has exactly two factors: 1 and itself.','options'=>json_encode([['text'=>'True','is_correct'=>true],['text'=>'False','is_correct'=>false]]),'marks'=>1,'order'=>7],
            ['type'=>'mcq','question_text'=>'What is 15% of 240?','options'=>json_encode([['text'=>'36','is_correct'=>true],['text'=>'30','is_correct'=>false],['text'=>'24','is_correct'=>false],['text'=>'40','is_correct'=>false]]),'marks'=>2,'order'=>8],
            ['type'=>'mcq','question_text'=>'Solve: 2x + 5 = 17. What is x?','options'=>json_encode([['text'=>'6','is_correct'=>true],['text'=>'7','is_correct'=>false],['text'=>'5','is_correct'=>false],['text'=>'11','is_correct'=>false]]),'marks'=>2,'order'=>9],
            ['type'=>'mcq','question_text'=>'What is the Highest Common Factor (HCF) of 36 and 48?','options'=>json_encode([['text'=>'12','is_correct'=>true],['text'=>'6','is_correct'=>false],['text'=>'18','is_correct'=>false],['text'=>'24','is_correct'=>false]]),'marks'=>2,'order'=>10],
        ];

        foreach ($questions as $i => $q) {
            DB::table('questions')->insert(array_merge($q, [
                'id'=>$i+1, 'exam_id'=>1, 'school_id'=>1, 'subject_id'=>1,
                'explanation'=>null,'image'=>null,'correct_answer'=>null,
                'created_at'=>now(),'updated_at'=>now()
            ]));
        }
    }
}

class ResultSeeder extends Seeder
{
    public function run(): void
    {
        // Results for student 1 (Michael Chen, id=1) in SS 2A, 2nd term
        $results = [
            ['subject_id'=>1,'ca_score'=>28,'exam_score'=>62,'grade'=>'A','remark'=>'Excellent'],
            ['subject_id'=>2,'ca_score'=>24,'exam_score'=>55,'grade'=>'B','remark'=>'Good'],
            ['subject_id'=>3,'ca_score'=>22,'exam_score'=>58,'grade'=>'A','remark'=>'Excellent'],
            ['subject_id'=>4,'ca_score'=>26,'exam_score'=>48,'grade'=>'B','remark'=>'Good'],
            ['subject_id'=>5,'ca_score'=>29,'exam_score'=>64,'grade'=>'A','remark'=>'Excellent'],
            ['subject_id'=>6,'ca_score'=>20,'exam_score'=>45,'grade'=>'C','remark'=>'Average'],
            ['subject_id'=>7,'ca_score'=>25,'exam_score'=>52,'grade'=>'B','remark'=>'Good'],
            ['subject_id'=>8,'ca_score'=>18,'exam_score'=>50,'grade'=>'C','remark'=>'Average'],
        ];

        foreach ($results as $i => $r) {
            DB::table('results')->insert(array_merge($r, [
                'school_id'=>1, 'student_id'=>1, 'school_class_id'=>6,
                'academic_session_id'=>1, 'term_id'=>2,
                'position'=>[3,7,2,8,1,15,5,11][$i],
                'is_published'=>true,
                'teacher_comment'=>'Good performance this term.',
                'created_at'=>now(),'updated_at'=>now()
            ]));
        }

        // Exam attempt for exam 1
        DB::table('exam_attempts')->insert([
            'id'=>1,'exam_id'=>1,'student_id'=>1,'school_id'=>1,
            'started_at'=>now()->subHours(2),
            'submitted_at'=>now()->subHours(1),
            'score'=>88,'percentage'=>88.00,'grade'=>'A',
            'is_submitted'=>true,'time_spent_seconds'=>3600,
            'answers'=>json_encode(['1'=>0,'2'=>0,'3'=>1,'4'=>0,'5'=>0,'6'=>0,'7'=>0,'8'=>0,'9'=>0,'10'=>0]),
            'status'=>'graded',
            'created_at'=>now(),'updated_at'=>now()
        ]);
    }
}

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = ['present','present','present','present','late','present','present','absent','present','present','present','present','present','late','present','present','present','present','absent','present'];
        $start = Carbon::parse('2026-01-06');

        for ($i = 0; $i < 20; $i++) {
            $date = $start->copy()->addDays($i);
            if ($date->isWeekend()) continue;

            DB::table('attendances')->insert([
                'school_id'=>1,'student_id'=>1,'school_class_id'=>6,
                'marked_by'=>7,'date'=>$date->toDateString(),
                'status'=>$statuses[$i] ?? 'present',
                'created_at'=>now(),'updated_at'=>now()
            ]);
        }
    }
}

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        // Fee categories
        DB::table('fee_categories')->insert([
            ['id'=>1,'school_id'=>1,'name'=>'Tuition Fee','description'=>'Term tuition fee','created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'school_id'=>1,'name'=>'Development Levy','description'=>'School development','created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'school_id'=>1,'name'=>'PTA Dues','description'=>'Parent-Teacher Association dues','created_at'=>now(),'updated_at'=>now()],
        ]);

        // Fee schedules
        DB::table('fee_schedules')->insert([
            ['school_id'=>1,'fee_category_id'=>1,'academic_session_id'=>1,'term_id'=>2,'class_level'=>'JSS','amount'=>65000,'created_at'=>now(),'updated_at'=>now()],
            ['school_id'=>1,'fee_category_id'=>1,'academic_session_id'=>1,'term_id'=>2,'class_level'=>'SS','amount'=>72000,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // Invoices for each student
        $amounts = [92000, 85000, 85000, 92000, 85000, 92000, 92000, 85000];
        $paid    = [92000, 85000, 42500, 0,     85000, 30000, 92000, 85000];
        $statuses= ['paid','paid','partial','unpaid','paid','partial','paid','paid'];

        foreach (range(1,8) as $i) {
            DB::table('invoices')->insert([
                'id'=>$i,'school_id'=>1,'student_id'=>$i,
                'academic_session_id'=>1,'term_id'=>2,
                'invoice_number'=>'GFA-INV-2026-'.str_pad($i,4,'0',STR_PAD_LEFT),
                'total_amount'=>$amounts[$i-1],'amount_paid'=>$paid[$i-1],
                'status'=>$statuses[$i-1],
                'due_date'=>'2026-01-31',
                'created_at'=>now(),'updated_at'=>now()
            ]);

            if ($paid[$i-1] > 0) {
                DB::table('payments')->insert([
                    'school_id'=>1,'invoice_id'=>$i,'student_id'=>$i,
                    'recorded_by'=>17,
                    'reference_number'=>'GFA-PAY-'.rand(100000,999999),
                    'amount'=>$paid[$i-1],'method'=>'bank_transfer',
                    'payment_date'=>Carbon::now()->subDays(rand(1,30))->toDateString(),
                    'status'=>'confirmed',
                    'created_at'=>now(),'updated_at'=>now()
                ]);
            }
        }
    }
}

class LibrarySeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            ['title'=>'Introduction to Calculus','author'=>'Thomas Stewart','isbn'=>'978-0-495-01166-8','publisher'=>'Brooks Cole','year_published'=>2016,'category'=>'Mathematics','total_copies'=>5,'available_copies'=>3,'shelf_number'=>'MAT-001'],
            ['title'=>'Physics Fundamentals','author'=>'Paul Tipler','isbn'=>'978-1-4292-0132-2','publisher'=>'Freeman','year_published'=>2014,'category'=>'Physics','total_copies'=>8,'available_copies'=>6,'shelf_number'=>'PHY-001'],
            ['title'=>'English Grammar in Use','author'=>'Raymond Murphy','isbn'=>'978-1-107-48026-4','publisher'=>'Cambridge','year_published'=>2019,'category'=>'English','total_copies'=>12,'available_copies'=>8,'shelf_number'=>'ENG-001'],
            ['title'=>'Chemistry: The Central Science','author'=>'Brown LeMay','isbn'=>'978-0-13-293090-3','publisher'=>'Pearson','year_published'=>2017,'category'=>'Chemistry','total_copies'=>6,'available_copies'=>2,'shelf_number'=>'CHM-001'],
            ['title'=>'Biology: A Global Approach','author'=>'Campbell & Reece','isbn'=>'978-1-292-17064-2','publisher'=>'Pearson','year_published'=>2018,'category'=>'Biology','total_copies'=>7,'available_copies'=>5,'shelf_number'=>'BIO-001'],
            ['title'=>'World History','author'=>'William McNeill','isbn'=>'978-0-19-516073-3','publisher'=>'Oxford','year_published'=>2015,'category'=>'History','total_copies'=>4,'available_copies'=>4,'shelf_number'=>'HIS-001'],
            ['title'=>'Principles of Economics','author'=>'N. Gregory Mankiw','isbn'=>'978-1-305-58512-6','publisher'=>'Cengage','year_published'=>2017,'category'=>'Economics','total_copies'=>9,'available_copies'=>7,'shelf_number'=>'ECO-001'],
            ['title'=>'Nigerian Literature Anthology','author'=>'Various Authors','isbn'=>'978-978-902-001-5','publisher'=>'Longman','year_published'=>2020,'category'=>'Literature','total_copies'=>15,'available_copies'=>11,'shelf_number'=>'LIT-001'],
        ];

        foreach ($books as $i => $b) {
            DB::table('books')->insert(array_merge($b, ['id'=>$i+1,'school_id'=>1,'created_at'=>now(),'updated_at'=>now()]));
        }

        // Borrow records
        DB::table('borrow_records')->insert([
            ['school_id'=>1,'book_id'=>1,'student_id'=>1,'issued_by'=>18,'issue_date'=>Carbon::now()->subDays(30),'due_date'=>Carbon::now()->subDays(16),'return_date'=>null,'status'=>'overdue','fine_amount'=>50,'created_at'=>now(),'updated_at'=>now()],
            ['school_id'=>1,'book_id'=>2,'student_id'=>2,'issued_by'=>18,'issue_date'=>Carbon::now()->subDays(10),'due_date'=>Carbon::now()->addDays(4),'return_date'=>null,'status'=>'active','fine_amount'=>0,'created_at'=>now(),'updated_at'=>now()],
            ['school_id'=>1,'book_id'=>3,'student_id'=>3,'issued_by'=>18,'issue_date'=>Carbon::now()->subDays(7),'due_date'=>Carbon::now()->addDays(7),'return_date'=>null,'status'=>'active','fine_amount'=>0,'created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $announcements = [
            ['title'=>'3rd Term Examination Schedule Released','body'=>'The 3rd Term examination timetable is now available. All students should collect printed copies from the Academic Office. Exams begin April 14, 2026.','audience'=>'all','priority'=>'urgent','created_by'=>2],
            ['title'=>'Sports Day & Inter-House Competition','body'=>'Annual Sports Day and Inter-House Sports Competition is scheduled for Saturday, April 5, 2026. All students are expected to participate.','audience'=>'all','priority'=>'important','created_by'=>2],
            ['title'=>'Parent-Teacher Conference — 2nd Term','body'=>'Parent-Teacher Conference will hold on Saturday, March 22, 2026, from 9:00 AM to 2:00 PM. All parents are strongly encouraged to attend.','audience'=>'parents','priority'=>'important','created_by'=>2],
            ['title'=>'Library Clearance Before Vacation','body'=>'All borrowed library books must be returned on or before March 25, 2026. Students with outstanding books will not receive their terminal report cards.','audience'=>'students','priority'=>'normal','created_by'=>18],
        ];

        foreach ($announcements as $i => $a) {
            DB::table('announcements')->insert(array_merge($a, ['id'=>$i+1,'school_id'=>1,'is_published'=>true,'created_at'=>now()->subDays($i*2),'updated_at'=>now()->subDays($i*2)]));
        }
    }
}
