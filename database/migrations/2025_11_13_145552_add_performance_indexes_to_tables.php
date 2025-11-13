<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds indexes to improve query performance on frequently queried columns.
     * This addresses BUG-018: Missing Database Indexes
     */
    public function up(): void
    {
        // Add indexes to daily_reports table
        Schema::table('daily_reports', function (Blueprint $table) {
            // Single column indexes for frequently filtered columns
            $table->index('status', 'idx_daily_reports_status');
            $table->index('approval_status', 'idx_daily_reports_approval_status');
            $table->index('report_date', 'idx_daily_reports_report_date');

            // Composite indexes for common query patterns
            // Used in queries filtering by user and status (e.g., "my pending reports")
            $table->index(['user_id', 'status'], 'idx_daily_reports_user_status');

            // Used in queries filtering by department and approval status (e.g., "pending approvals in my department")
            $table->index(['department_id', 'approval_status'], 'idx_daily_reports_dept_approval');
        });

        // Add indexes to notifications table
        Schema::table('notifications', function (Blueprint $table) {
            // Index on is_read for filtering unread notifications
            $table->index('is_read', 'idx_notifications_is_read');

            // Index on created_at for sorting by date and cleanup queries
            $table->index('created_at', 'idx_notifications_created_at');

            // Composite index for common pattern: getting user's unread notifications sorted by date
            $table->index(['user_id', 'is_read', 'created_at'], 'idx_notifications_user_read_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from daily_reports table
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropIndex('idx_daily_reports_status');
            $table->dropIndex('idx_daily_reports_approval_status');
            $table->dropIndex('idx_daily_reports_report_date');
            $table->dropIndex('idx_daily_reports_user_status');
            $table->dropIndex('idx_daily_reports_dept_approval');
        });

        // Drop indexes from notifications table
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_is_read');
            $table->dropIndex('idx_notifications_created_at');
            $table->dropIndex('idx_notifications_user_read_date');
        });
    }
};
