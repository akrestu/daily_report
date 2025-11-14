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
        // Get departments
        $itDepartment = Department::where('name', 'IT')->first();
        $hrDepartment = Department::where('name', 'HR')->first();

        if ($itDepartment) {
            $itSections = [
                [
                    'department_id' => $itDepartment->id,
                    'name' => 'Jaringan',
                    'code' => 'IT-NET',
                    'description' => 'Network infrastructure and connectivity',
                    'is_active' => true,
                ],
                [
                    'department_id' => $itDepartment->id,
                    'name' => 'Hardware',
                    'code' => 'IT-HW',
                    'description' => 'Computer hardware and equipment maintenance',
                    'is_active' => true,
                ],
                [
                    'department_id' => $itDepartment->id,
                    'name' => 'System',
                    'code' => 'IT-SYS',
                    'description' => 'System administration and software',
                    'is_active' => true,
                ],
            ];

            foreach ($itSections as $section) {
                Section::create($section);
            }
        }

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
                Section::create($section);
            }
        }
    }
}
