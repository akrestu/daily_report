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
}
