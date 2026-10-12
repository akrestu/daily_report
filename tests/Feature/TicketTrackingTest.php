<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTrackingTest extends TestCase
{
    use RefreshDatabase;

    private TicketCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin' => 0, 'level4' => 4, 'level5' => 5, 'level1' => 1] as $slug => $level) {
            Role::create(['name' => ucfirst($slug), 'slug' => $slug, 'level' => $level]);
        }
        Department::create(['name' => 'IT Department', 'code' => 'IT']);
        Department::create(['name' => 'HR Department', 'code' => 'HR']);

        $this->category = TicketCategory::create(['name' => 'IT Support', 'is_active' => true]);
    }

    private function user(string $role, string $dept): User
    {
        return User::factory()->create([
            'role_id' => Role::where('slug', $role)->value('id'),
            'department_id' => Department::where('code', $dept)->value('id'),
        ]);
    }

    private function makeTicket(array $overrides = []): Ticket
    {
        return Ticket::createWithNumber($overrides + [
            'requester_name' => 'Budi',
            'requester_department' => 'HR',
            'category_id' => $this->category->id,
            'title' => 'Printer rusak',
            'description' => 'Tidak bisa print',
            'priority' => 'medium',
        ])->refresh();
    }

    public function test_ticket_numbers_are_sequential_and_skip_deleted_ones(): void
    {
        $first = $this->makeTicket();
        $first->delete();
        $second = $this->makeTicket();

        $this->assertStringEndsWith('-0001', $first->ticket_number);
        $this->assertStringEndsWith('-0002', $second->ticket_number);
        $this->assertSoftDeleted($first);
    }

    public function test_ticket_gets_tracking_token_on_create(): void
    {
        $ticket = $this->makeTicket();

        $this->assertMatchesRegularExpression('/^[A-Z2-9]{8}$/', $ticket->tracking_token);
        $this->assertArrayNotHasKey('tracking_token', $ticket->toArray());
    }

    public function test_public_submission_shows_token_once_via_session(): void
    {
        Employee::create(['nik' => '0012345', 'name' => 'Budi', 'department_id' => Department::where('code', 'HR')->value('id')]);

        $response = $this->post('/request', [
            'nik' => '0012345',
            'type' => 'incident',
            'category_id' => $this->category->id,
            'title' => 'Printer rusak',
            'description' => 'Tidak bisa print',
            'priority' => 'medium',
        ]);

        $response->assertRedirect(route('tickets.public.success'));
        $ticket = Ticket::first();

        $this->get(route('tickets.public.success'))->assertOk()->assertSee($ticket->tracking_token);
        // Session flash sudah habis: tidak bisa dibuka ulang
        $this->get(route('tickets.public.success'))->assertRedirect(route('tickets.public.track'));
    }

    public function test_success_page_no_longer_leaks_ticket_by_number(): void
    {
        $ticket = $this->makeTicket();

        $this->get('/request/success?ticket='.$ticket->ticket_number)
            ->assertRedirect(route('tickets.public.track'));
    }

    public function test_tracking_requires_matching_token(): void
    {
        $ticket = $this->makeTicket();

        // Tanpa lookup, halaman detail tidak bisa dibuka walau nomor tiket diketahui
        $this->get(route('tickets.public.track.show', $ticket))->assertRedirect(route('tickets.public.track'));

        $this->from(route('tickets.public.track'))
            ->post(route('tickets.public.track.lookup'), [
                'ticket_number' => $ticket->ticket_number,
                'token' => 'WRONGTKN',
            ])->assertSessionHasErrors('ticket_number');

        $this->post(route('tickets.public.track.lookup'), [
            'ticket_number' => strtolower($ticket->ticket_number),
            'token' => strtolower($ticket->tracking_token),
        ])->assertRedirect(route('tickets.public.track.show', $ticket));

        $this->get(route('tickets.public.track.show', $ticket))
            ->assertOk()->assertSee($ticket->title)->assertSee($ticket->status_label);
    }

    public function test_only_admin_and_it_level4_plus_are_triagers(): void
    {
        $this->assertTrue(TicketPolicy::isTriager($this->user('admin', 'HR')));
        $this->assertTrue(TicketPolicy::isTriager($this->user('level4', 'IT')));
        $this->assertFalse(TicketPolicy::isTriager($this->user('level5', 'HR')));
        $this->assertFalse(TicketPolicy::isTriager($this->user('level1', 'IT')));
    }

    public function test_new_ticket_notifies_only_triagers(): void
    {
        $itLead = $this->user('level4', 'IT');
        $hrHead = $this->user('level5', 'HR');

        $this->makeTicket();

        $this->assertTrue(Notification::where('user_id', $itLead->id)->where('type', 'ticket_created')->exists());
        $this->assertFalse(Notification::where('user_id', $hrHead->id)->exists());
    }

    public function test_non_it_user_cannot_open_ticket_detail(): void
    {
        $ticket = $this->makeTicket();

        $this->actingAs($this->user('level5', 'HR'))
            ->get(route('tickets.show', $ticket))
            ->assertForbidden();
    }
}
