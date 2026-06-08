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
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['daily_report_id']);
            $table->dropColumn('daily_report_id');
            $table->foreignId('job_plan_id')->nullable()->constrained('job_plans')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['job_plan_id']);
            $table->dropColumn('job_plan_id');
            $table->foreignId('daily_report_id')->nullable()->constrained('daily_reports')->nullOnDelete();
        });
    }
};
