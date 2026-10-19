<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-site: site tambahan per user. users.job_site_id tetap jadi site utama (default saat membuat laporan);
 * cakupan user = site utama + site tambahan (User::jobSiteIds()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_job_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_site_id')->constrained('job_sites')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'job_site_id']);
            $table->index('job_site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_job_sites');
    }
};
