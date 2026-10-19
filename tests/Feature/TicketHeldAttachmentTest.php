<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Lampiran tidak hilang saat validasi gagal, dan form publik tetap menerima NIK walau pengirim login.
 */
class TicketHeldAttachmentTest extends TestCase
{
    use RefreshDatabase;

    private TicketCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('attachments');
        Storage::fake('local');

        Role::create(['name' => 'Level1', 'slug' => 'level1', 'level' => 1]);
        $hr = Department::create(['name' => 'HR Department', 'code' => 'HR']);
        Employee::create(['nik' => '100', 'name' => 'Budi', 'department_id' => $hr->id]);
        $this->category = TicketCategory::create(['name' => 'IT Support', 'is_active' => true]);
    }

    private function payload(array $overrides = []): array
    {
        return $overrides + [
            'nik' => '100', 'type' => 'incident', 'category_id' => $this->category->id,
            'title' => 'Printer rusak', 'description' => 'Tidak bisa print',
            'impact' => 'low', 'urgency' => 'medium',
        ];
    }

    public function test_valid_attachment_is_held_when_validation_fails_and_reused_on_resubmit(): void
    {
        $file = UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf');

        $this->post('/request', $this->payload(['title' => '', 'attachment' => $file]))
            ->assertSessionHasErrors('title')
            ->assertSessionHas('held_attachment', fn ($held) => $held['name'] === 'bukti.pdf');

        $token = session('held_attachment')['token'];
        $this->assertCount(1, Storage::disk('local')->files('tmp/ticket-attachments'));

        $this->post('/request', $this->payload(['attachment_token' => $token]))->assertSessionHasNoErrors();

        $ticket = Ticket::latest('id')->firstOrFail();
        $this->assertSame('bukti.pdf', $ticket->attachment_original_name);
        Storage::disk('attachments')->assertExists($ticket->attachment_path);
        $this->assertCount(0, Storage::disk('local')->files('tmp/ticket-attachments'));
    }

    public function test_held_attachment_survives_a_second_failed_attempt(): void
    {
        $this->post('/request', $this->payload(['title' => '', 'attachment' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')]));
        $token = session('held_attachment')['token'];

        $this->post('/request', $this->payload(['description' => '', 'attachment_token' => $token]))
            ->assertSessionHas('held_attachment', fn ($held) => $held['token'] === $token);
    }

    public function test_foreign_token_is_ignored(): void
    {
        $this->post('/request', $this->payload(['attachment_token' => str_repeat('a', 40)]))->assertSessionHasNoErrors();

        $this->assertNull(Ticket::latest('id')->firstOrFail()->attachment_path);
    }

    public function test_logged_in_user_can_submit_public_form_with_nik(): void
    {
        $user = User::factory()->create(['role_id' => Role::where('slug', 'level1')->value('id')]);

        $this->actingAs($user)->post('/request', $this->payload())->assertSessionHasNoErrors();

        $this->assertSame('Budi', Ticket::latest('id')->firstOrFail()->requester_name);
    }
}
