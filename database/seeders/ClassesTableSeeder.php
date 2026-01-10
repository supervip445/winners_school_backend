<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creatorId = User::query()->orderBy('id')->value('id');
        $academicYearId = AcademicYear::query()
                ->where('is_active', true)
                ->orderBy('start_date')
                ->value('id')
            ?? AcademicYear::query()->orderBy('start_date')->value('id');

        if (!$creatorId || !$academicYearId) {
            $this->command?->warn('ClassesTableSeeder skipped: missing prerequisite users or academic years.');
            return;
        }

        $grades = $this->gradeDefinitions();

        foreach ($grades as $grade) {
            $code = $this->buildCode($grade['label']);

            SchoolClass::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $grade['label'],
                    'grade_level' => $grade['level'],
                    'section' => null,
                    'capacity' => 30,
                    'is_active' => true,
                    'academic_year_id' => $academicYearId,
                    'class_teacher_id' => null,
                    'created_by' => $creatorId,
                ]
            );
        }
    }

    private function gradeDefinitions(): array
    {
        $grades = [
            
            ['label' => 'English Speaking', 'level' => 28],
            ['label' => 'English Writing', 'level' => 29],
            ['label' => 'English Reading', 'level' => 30],
            ['label' => 'English Listening', 'level' => 31],
            ['label' => 'English Grammar', 'level' => 32],
            ['label' => 'English Vocabulary', 'level' => 33],
            ['label' => 'English Pronunciation', 'level' => 34],
            ['label' => 'Computer Basic', 'level' => 35],
            
            ['label' => 'Programming Basic', 'level' => 44],
            ['label' => 'Programming Intermediate', 'level' => 45],
            
            ['label' => 'HTML', 'level' => 49],
            ['label' => 'CSS', 'level' => 50],
            ['label' => 'JavaScript', 'level' => 51],
            ['label' => 'PHP', 'level' => 52],
            ['label' => 'MySQL', 'level' => 53],
            ['label' => 'Laravel', 'level' => 54],
            ['label' => 'React', 'level' => 55],
            ['label' => 'Node.js', 'level' => 56],
        ];

        for ($level = 1; $level <= 23; $level++) {
            $grades[] = [
                'label' => sprintf('G-%d', $level),
                'level' => $level,
            ];
        }

        return $grades;
    }

    private function buildCode(string $label): string
    {
        // Strip all non-alphanumeric characters so codes are compact and unique-friendly
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $label));
    }
}

