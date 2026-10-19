<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Department;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Section IT: daftar lengkap di ItSectionSeeder (bisa dijalankan terpisah di produksi)
        $this->call(ItSectionSeeder::class);

        $hrDepartment = Department::where('code', 'HR')->first();

        if ($hrDepartment) {
            $hrSections = [
                [
                    'department_id' => $hrDepartment->id,
                    'name' => 'Recruitment',
                    'code' => 'HR-REC',
                    'description' => 'Employee recruitment and onboarding',
                    'is_active' => true,
                ],
                [
                    'department_id' => $hrDepartment->id,
                    'name' => 'Training & Development',
                    'code' => 'HR-TRN',
                    'description' => 'Employee training and career development',
                    'is_active' => true,
                ],
                [
                    'department_id' => $hrDepartment->id,
                    'name' => 'Payroll',
                    'code' => 'HR-PAY',
                    'description' => 'Salary and benefits administration',
                    'is_active' => true,
                ],
            ];

            foreach ($hrSections as $section) {
                Section::updateOrCreate(['code' => $section['code']], $section);
            }
        }
    }
}
