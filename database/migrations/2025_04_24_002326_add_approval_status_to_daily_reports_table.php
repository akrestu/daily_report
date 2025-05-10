<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->string('approval_status')->default('pending')->after('status');
        });

        // Update existing reports to have the appropriate approval_status based on current status
        DB::statement("
            UPDATE daily_reports 
            SET approval_status = CASE
                WHEN approved_by IS NOT NULL AND status = 'approved' THEN 'approved'
                WHEN approved_by IS NOT NULL AND status = 'rejected' THEN 'rejected'
                ELSE 'pending'
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
