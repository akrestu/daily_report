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
            $table->string('attachment_path_2')->nullable()->after('attachment_original_name');
            $table->string('attachment_original_name_2')->nullable()->after('attachment_path_2');
            $table->string('attachment_path_3')->nullable()->after('attachment_original_name_2');
            $table->string('attachment_original_name_3')->nullable()->after('attachment_path_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropColumn([
                'attachment_path_2',
                'attachment_original_name_2',
                'attachment_path_3',
                'attachment_original_name_3'
            ]);
        });
    }
};
