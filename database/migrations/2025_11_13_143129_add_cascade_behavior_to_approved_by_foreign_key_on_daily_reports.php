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
            // Drop existing foreign key constraint
            $table->dropForeign(['approved_by']);

            // Recreate foreign key with onDelete('set null') behavior
            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            // Drop the modified foreign key
            $table->dropForeign(['approved_by']);

            // Recreate without onDelete behavior (original state)
            $table->foreign('approved_by')
                ->references('id')
                ->on('users');
        });
    }
};
