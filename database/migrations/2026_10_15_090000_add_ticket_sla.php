<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->string('priority', 20)->unique();
            // Dalam menit JAM KERJA (bukan menit kalender)
            $table->unsignedInteger('response_minutes');
            $table->unsignedInteger('resolution_minutes');
            $table->timestamps();
        });

        // Default (1 hari kerja = 11 jam = 660 menit, jam kerja 06:00–17:00)
        $now = now();
        DB::table('sla_policies')->insert([
            ['priority' => 'urgent', 'response_minutes' => 30, 'resolution_minutes' => 240, 'created_at' => $now, 'updated_at' => $now],
            ['priority' => 'high', 'response_minutes' => 60, 'resolution_minutes' => 660, 'created_at' => $now, 'updated_at' => $now],
            ['priority' => 'medium', 'response_minutes' => 240, 'resolution_minutes' => 1980, 'created_at' => $now, 'updated_at' => $now],
            ['priority' => 'low', 'response_minutes' => 660, 'resolution_minutes' => 3300, 'created_at' => $now, 'updated_at' => $now],
        ]);

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('impact', 10)->nullable()->after('priority');
            $table->string('urgency', 10)->nullable()->after('impact');
            $table->timestamp('first_responded_at')->nullable()->after('assigned_at');
            $table->timestamp('response_due_at')->nullable()->after('first_responded_at');
            $table->timestamp('resolution_due_at')->nullable()->after('response_due_at');
            $table->timestamp('sla_paused_at')->nullable()->after('resolution_due_at');
            $table->unsignedInteger('sla_paused_minutes')->default(0)->after('sla_paused_at');
            $table->boolean('response_breached')->default(false)->after('sla_paused_minutes');
            $table->boolean('resolution_breached')->default(false)->after('response_breached');

            $table->index(['status', 'resolution_due_at']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status', 'resolution_due_at']);
            $table->dropColumn([
                'impact', 'urgency', 'first_responded_at', 'response_due_at', 'resolution_due_at',
                'sla_paused_at', 'sla_paused_minutes', 'response_breached', 'resolution_breached',
            ]);
        });

        Schema::dropIfExists('holidays');
        Schema::dropIfExists('sla_policies');
    }
};
