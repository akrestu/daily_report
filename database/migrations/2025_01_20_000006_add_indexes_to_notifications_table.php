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
        Schema::table('notifications', function (Blueprint $table) {
            // Index for user notifications queries (user_id + is_read)
            $table->index(['user_id', 'is_read'], 'idx_notifications_user_read');
            
            // Index for ordering by created_at (descending)
            $table->index(['created_at'], 'idx_notifications_created');
            
            // Composite index for user + created_at (for pagination)
            $table->index(['user_id', 'created_at'], 'idx_notifications_user_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_read');
            $table->dropIndex('idx_notifications_created');
            $table->dropIndex('idx_notifications_user_created');
        });
    }
}; 