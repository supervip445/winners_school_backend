<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $creatorId = User::query()->orderBy('id')->value('id');

        if (!$creatorId) {
            $this->command?->warn('SubjectsTableSeeder skipped: no users available for created_by.');
            return;
        }

        $subjects = [
           
            'လောကနီတိ',
            'ဓမ္မပဒ',
            'သမ္မတဆယ်ဆောင်တွဲ',
            'ဗုဒ္ဓဝင်',
            'အင်္ဂလိပ်စာ',
            'သင်္ချာ',
            'ပထဝီ',
            'သမိုင်း',
            'သိပ္ပံ',
            'ပန်းချီ',
            'ဂီတ',
            'ကွန်ပျူတာ',
            'English Subject',
            'Math Subject',
            'Science Subject',
            'Social Studies Subject',
            'History Subject',
            'Geography Subject',
            'Economics Subject',
            'Political Science Subject',
            'Philosophy Subject',
            'Religion Subject',
            'Art Subject',
            'Music Subject',
            'Dance Subject',
            'Theater Subject',
            'Film Subject',
            'Television Subject',
            'Radio Subject',
            'Internet Subject',
            'Computer Science Subject',
            'Information Technology Subject',
            'Business Subject',
            'Accounting Subject',
            'Finance Subject',
            'Economics Subject',
            'Political Science Subject',
            'Philosophy Subject',
            'Religion Subject',
            
        ];

        foreach ($subjects as $index => $name) {
            $code = $this->buildSubjectCode($name, $index + 1);

            Subject::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => null,
                    'credit_hours' => 3,
                    'is_active' => true,
                    'created_by' => $creatorId,
                ]
            );
        }
    }

    private function buildSubjectCode(string $name, int $position): string
    {
        $base = strtoupper(Str::substr(Str::slug($name, ''), 0, 3));
        $base = str_pad($base ?: 'SUB', 3, 'X');

        return sprintf('%s%03d', $base, $position);
    }
}

