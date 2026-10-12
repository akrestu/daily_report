<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->unsignedTinyInteger('csat_rating')->nullable()->after('reopen_count');
            $table->text('csat_comment')->nullable()->after('csat_rating');
            $table->timestamp('csat_submitted_at')->nullable()->after('csat_comment');

            $table->index('created_at');
            $table->index('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['resolved_at']);
            $table->dropColumn(['csat_rating', 'csat_comment', 'csat_submitted_at']);
        });
    }
};
