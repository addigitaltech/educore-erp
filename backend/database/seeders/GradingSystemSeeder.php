<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GradingSystemSeeder extends Seeder
{
    public function run(): void
    {
        // Default WAEC grading for school_id = 1 (Greenfield Academy)
        $grades = [
            ['grade' => 'A1', 'min_score' => 75, 'max_score' => 100, 'remark' => 'Excellent',   'category' => 'Pass', 'sort_order' => 1],
            ['grade' => 'B2', 'min_score' => 70, 'max_score' => 74,  'remark' => 'Very Good',   'category' => 'Pass', 'sort_order' => 2],
            ['grade' => 'B3', 'min_score' => 65, 'max_score' => 69,  'remark' => 'Good',        'category' => 'Pass', 'sort_order' => 3],
            ['grade' => 'C4', 'min_score' => 60, 'max_score' => 64,  'remark' => 'Credit',      'category' => 'Pass', 'sort_order' => 4],
            ['grade' => 'C5', 'min_score' => 55, 'max_score' => 59,  'remark' => 'Credit',      'category' => 'Pass', 'sort_order' => 5],
            ['grade' => 'C6', 'min_score' => 50, 'max_score' => 54,  'remark' => 'Credit',      'category' => 'Pass', 'sort_order' => 6],
            ['grade' => 'D7', 'min_score' => 45, 'max_score' => 49,  'remark' => 'Pass',        'category' => 'Pass', 'sort_order' => 7],
            ['grade' => 'E8', 'min_score' => 40, 'max_score' => 44,  'remark' => 'Pass',        'category' => 'Pass', 'sort_order' => 8],
            ['grade' => 'F9', 'min_score' => 0,  'max_score' => 39,  'remark' => 'Fail',        'category' => 'Fail', 'sort_order' => 9],
        ];

        foreach ([1, 2] as $schoolId) {
            foreach ($grades as $g) {
                DB::table('grading_systems')->updateOrInsert(
                    ['school_id' => $schoolId, 'grade' => $g['grade']],
                    array_merge($g, ['school_id' => $schoolId, 'created_at' => now(), 'updated_at' => now()])
                );
            }
        }
    }
}
