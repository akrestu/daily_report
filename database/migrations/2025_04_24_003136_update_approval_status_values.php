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
        // Fix any records where status shows 'approved' or 'rejected'
        // Keep job status as is, but ensure approval_status is correct
        
        // First, update any records with 'approved' in status and approved_by to have job status 'completed'
        // but maintain approval_status as 'approved'
        DB::statement("
            UPDATE daily_reports 
            SET status = CASE
                    WHEN status = 'approved' THEN 'completed'
                    WHEN status = 'rejected' THEN 'pending'
                    ELSE status
                END
            WHERE approved_by IS NOT NULL
        ");

        // Now ensure all records with approved_by have the correct approval_status
        DB::statement("
            UPDATE daily_reports 
            SET approval_status = CASE
                    WHEN status = 'rejected' OR rejection_reason IS NOT NULL THEN 'rejected'
                    WHEN approved_by IS NOT NULL THEN 'approved'
                    ELSE 'pending'
                END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration as it's a data fix
    }
};
