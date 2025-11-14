<?php

namespace Database\Seeders;

use App\Models\JobSite;
use Illuminate\Database\Seeder;

class JobSiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobSites = [
            [
                'name' => 'Head Office',
                'code' => 'HO',
                'description' => 'Main headquarters office',
                'location' => 'Jakarta',
                'is_active' => true,
            ],
            [
                'name' => 'Branch Office Surabaya',
                'code' => 'SBY',
                'description' => 'Branch office in Surabaya',
                'location' => 'Surabaya, East Java',
                'is_active' => true,
            ],
            [
                'name' => 'Branch Office Bandung',
                'code' => 'BDG',
                'description' => 'Branch office in Bandung',
                'location' => 'Bandung, West Java',
                'is_active' => true,
            ],
            [
                'name' => 'Regional Office Medan',
                'code' => 'MDN',
                'description' => 'Regional office in Medan',
                'location' => 'Medan, North Sumatra',
                'is_active' => true,
            ],
        ];

        foreach ($jobSites as $jobSite) {
            JobSite::create($jobSite);
        }
    }
}
