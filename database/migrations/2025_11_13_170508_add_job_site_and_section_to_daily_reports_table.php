<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->foreignId('job_site_id')->nullable()->after('department_id')->constrained()->onDelete('set null');
            $table->foreignId('section_id')->nullable()->after('job_site_id')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropForeign(['job_site_id']);
            $table->dropForeign(['section_id']);
            $table->dropColumn(['job_site_id', 'section_id']);
        });
    }
};
