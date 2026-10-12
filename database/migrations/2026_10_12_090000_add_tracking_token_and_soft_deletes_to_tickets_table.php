<?php

use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('tracking_token', 16)->nullable()->after('ticket_number');
            $table->softDeletes();
        });

        // Backfill token untuk tiket lama agar pemohon tetap bisa cek status
        DB::table('tickets')->whereNull('tracking_token')->orderBy('id')->each(function ($row) {
            DB::table('tickets')->where('id', $row->id)->update([
                'tracking_token' => Ticket::generateTrackingToken(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['tracking_token', 'deleted_at']);
        });
    }
};
