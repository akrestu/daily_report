<?php

namespace Database\Seeders;

use App\Models\TicketCategory;
use Illuminate\Database\Seeder;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'IT Support',              'description' => 'Bantuan umum terkait perangkat IT'],
            ['name' => 'Jaringan & Infrastruktur', 'description' => 'Masalah jaringan, internet, dan infrastruktur IT'],
            ['name' => 'Hardware & Perangkat',     'description' => 'Kerusakan atau permintaan perangkat keras'],
            ['name' => 'HR & Kepegawaian',         'description' => 'Permintaan terkait SDM dan kepegawaian'],
            ['name' => 'Fasilitas & Umum',         'description' => 'Permintaan terkait fasilitas kantor dan umum'],
            ['name' => 'Lainnya',                  'description' => 'Permintaan di luar kategori yang tersedia'],
        ];

        foreach ($categories as $category) {
            TicketCategory::updateOrCreate(
                ['name' => $category['name']],
                array_merge($category, ['is_active' => true])
            );
        }
    }
}
