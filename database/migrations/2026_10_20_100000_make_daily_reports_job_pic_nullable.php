<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * PIC opsional untuk pelapor Level 4 ke atas (User::picOptional()).
 * Laporan tanpa PIC langsung disetujui sebagai catatan kerja mandiri (DailyReport::booted).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->foreignId('job_pic')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Laporan tanpa PIC diisi pembuatnya agar kolom bisa kembali NOT NULL
        DB::table('daily_reports')->whereNull('job_pic')->update(['job_pic' => DB::raw('user_id')]);

        Schema::table('daily_reports', function (Blueprint $table) {
            $table->foreignId('job_pic')->nullable(false)->change();
        });
    }
};
