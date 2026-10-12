<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 30)->unique();
            $table->string('name');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('position')->nullable();
            $table->foreignId('job_site_id')->nullable()->constrained('job_sites')->nullOnDelete();
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('ticket_number')->constrained('employees')->nullOnDelete();
            $table->foreignId('requester_user_id')->nullable()->after('employee_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
            $table->dropConstrainedForeignId('requester_user_id');
        });

        Schema::dropIfExists('employees');
    }
};
