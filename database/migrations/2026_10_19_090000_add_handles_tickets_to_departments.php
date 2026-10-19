<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Departemen penanganan tiket diatur admin per departemen (bukan lagi satu kode tetap).
 * Departemen dengan kode bawaan (IT) langsung diaktifkan agar perilaku lama tetap jalan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->boolean('handles_tickets')->default(false)->after('description');
        });

        DB::table('departments')
            ->whereRaw('UPPER(TRIM(code)) = ?', [strtoupper(config('tickets.department_code', 'IT'))])
            ->update(['handles_tickets' => true]);
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('handles_tickets');
        });
    }
};
