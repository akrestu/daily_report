<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DailyReport>
 */
class DailyReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'department_id' => Department::factory(),
            'job_name' => fake()->sentence(3),
            'report_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'description' => fake()->paragraph(),
            'status' => 'pending',
            'approval_status' => 'pending',
            'job_pic' => User::factory(),
        ];
    }
}
