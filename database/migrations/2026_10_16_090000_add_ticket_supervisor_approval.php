<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->boolean('requires_approval')->default(false)->after('description');
        });

        // Kategori permintaan yang wajib disetujui atasan pemohon
        foreach ([
            ['name' => 'Perangkat Baru', 'description' => 'Permintaan laptop, PC, printer, atau perangkat IT baru'],
            ['name' => 'Akses Sistem / Aplikasi', 'description' => 'Permintaan akses / akun sistem, aplikasi, folder, atau email'],
        ] as $category) {
            DB::table('ticket_categories')->updateOrInsert(
                ['name' => $category['name']],
                $category + ['is_active' => true, 'requires_approval' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('supervisor_employee_id')->nullable()->after('requester_phone')->constrained('employees')->nullOnDelete();
            // null = tidak perlu persetujuan; waiting_upload | waiting_verification | approved | rejected
            $table->string('approval_status', 25)->nullable()->after('supervisor_employee_id');
            $table->string('approval_proof_path')->nullable()->after('approval_status');
            $table->string('approval_proof_original_name')->nullable()->after('approval_proof_path');
            $table->timestamp('approval_uploaded_at')->nullable()->after('approval_proof_original_name');
            $table->foreignId('approval_verified_by')->nullable()->after('approval_uploaded_at')->constrained('users')->nullOnDelete();
            $table->timestamp('approval_verified_at')->nullable()->after('approval_verified_by');
            $table->text('approval_note')->nullable()->after('approval_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supervisor_employee_id');
            $table->dropConstrainedForeignId('approval_verified_by');
            $table->dropColumn(['approval_status', 'approval_proof_path', 'approval_proof_original_name',
                'approval_uploaded_at', 'approval_verified_at', 'approval_note']);
        });

        Schema::table('ticket_categories', function (Blueprint $table) {
            $table->dropColumn('requires_approval');
        });
    }
};
