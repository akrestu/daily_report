<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // enum -> string agar status lifecycle baru (assigned, pending, cancelled) bisa ditambahkan
            $table->string('status', 20)->default('open')->change();
            $table->string('type', 30)->default('incident')->after('category_id');
            $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            $table->text('resolution_note')->nullable()->after('resolved_at');
            $table->timestamp('closed_at')->nullable()->after('resolution_note');
            $table->string('close_method', 20)->nullable()->after('closed_at');
            $table->timestamp('cancelled_at')->nullable()->after('close_method');
            $table->unsignedTinyInteger('reopen_count')->default(0)->after('cancelled_at');
        });

        Schema::create('ticket_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('author_name')->nullable()->after('user_id');
            $table->boolean('is_internal')->default(false)->after('comment');
        });

        // Komentar lama berasal dari fitur "Komentar Internal"
        DB::table('ticket_comments')->update(['is_internal' => true]);

        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('ticket_id')->nullable()->after('job_plan_id')->constrained('tickets')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ticket_id');
        });

        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->dropColumn(['author_name', 'is_internal']);
        });

        Schema::dropIfExists('ticket_status_histories');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['type', 'assigned_at', 'resolution_note', 'closed_at', 'close_method', 'cancelled_at', 'reopen_count']);
        });
    }
};
