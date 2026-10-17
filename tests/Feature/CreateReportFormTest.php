<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateReportFormTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $slug, Department $department): User
    {
        $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);

        return User::factory()->create(['role_id' => $role->id, 'department_id' => $department->id]);
    }

    public function test_create_page_renders_new_form(): void
    {
        $department = Department::create(['name' => 'Maintenance', 'code' => 'MTC']);

        $this->actingAs($this->user('level1', $department))
            ->get(route('daily-reports.create'))
            ->assertOk()
            ->assertSee('sgReportForm', false)
            ->assertSee('Tambah laporan lain');
    }

    /** Form baru memakai kunci entri tetap: setelah entri tengah dihapus, kunci bisa 0 dan 2. */
    public function test_store_multiple_accepts_non_sequential_entry_keys_with_attachments(): void
    {
        Storage::fake('attachments');
        $department = Department::create(['name' => 'Maintenance', 'code' => 'MTC']);
        $reporter = $this->user('level1', $department);
        $pic = $this->user('level2', $department);

        $entry = fn (string $name) => [
            'department_id' => $department->id,
            'job_name' => $name,
            'report_date' => now()->toDateString(),
            'due_date' => now()->addDay()->toDateString(),
            'job_pic' => $pic->id,
            'status' => 'pending',
            'description' => 'Deskripsi '.$name,
        ];

        $this->actingAs($reporter)
            ->post(route('daily-reports.store-multiple'), [
                'reports' => [
                    0 => $entry('Entri pertama'),
                    2 => $entry('Entri ketiga') + ['attachment' => UploadedFile::fake()->create('catatan.pdf', 20, 'application/pdf')],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(2, DailyReport::count());
        $this->assertNull(DailyReport::where('job_name', 'Entri pertama')->value('attachment_path'));
        $this->assertSame('catatan.pdf', DailyReport::where('job_name', 'Entri ketiga')->value('attachment_original_name'));
    }

    public function test_offline_resend_with_same_idempotency_key_creates_once(): void
    {
        $department = Department::create(['name' => 'Maintenance', 'code' => 'MTC']);
        $reporter = $this->user('level1', $department);
        $pic = $this->user('level2', $department);

        $payload = [
            'idempotency_key' => 'draft-abc-123',
            'reports' => [0 => [
                'department_id' => $department->id,
                'job_name' => 'Ganti bearing',
                'report_date' => now()->toDateString(),
                'due_date' => now()->addDay()->toDateString(),
                'job_pic' => $pic->id,
                'status' => 'pending',
                'description' => 'Bearing pompa diganti',
            ]],
        ];

        // Kiriman dari antrian offline (fetch, JSON)
        $this->actingAs($reporter)->postJson(route('daily-reports.store-multiple'), $payload)
            ->assertOk()->assertJson(['ok' => true, 'count' => 1]);

        // Kiriman kedua dengan kunci sama (mis. submit manual) tidak membuat duplikat
        $this->actingAs($reporter)->postJson(route('daily-reports.store-multiple'), $payload)
            ->assertOk()->assertJson(['ok' => true, 'duplicate' => true]);
        $this->actingAs($reporter)->post(route('daily-reports.store-multiple'), $payload)
            ->assertRedirect(route('daily-reports.user-jobs'));

        $this->assertSame(1, DailyReport::count());
    }

    public function test_draft_and_offline_queue_are_wired_in_create_page(): void
    {
        $department = Department::create(['name' => 'Maintenance', 'code' => 'MTC']);
        $this->user('level2', $department);

        $this->actingAs($this->user('level1', $department))->get(route('daily-reports.create'))
            ->assertOk()
            ->assertSee('name="idempotency_key"', false)
            ->assertSee('restoreDraft()', false)
            ->assertSee('$store.outbox.count', false);
    }
}
