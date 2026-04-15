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
        Schema::create('job_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assignee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('job_site_id')->nullable()->constrained('job_sites')->onDelete('set null');
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->string('job_name');
            $table->text('description');
            $table->string('remark', 1000)->nullable();
            $table->date('planned_date');
            $table->date('due_date');
            $table->string('status')->default('assigned'); // assigned, converted
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index('creator_id');
            $table->index('assignee_id');
            $table->index('status');
            $table->index('planned_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_plans');
    }
};
