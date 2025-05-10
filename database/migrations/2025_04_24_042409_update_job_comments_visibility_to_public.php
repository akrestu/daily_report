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
        // Update all existing job comments to have 'public' visibility
        DB::table('job_comments')
            ->where('visibility', 'private')
            ->update(['visibility' => 'public']);
    }

    /**
     * Reverse the migrations.
     * 
     * Note: This cannot truly be reversed as we cannot determine which comments
     * were originally private.
     */
    public function down(): void
    {
        // No rollback possible as we can't know which comments were originally private
    }
};
