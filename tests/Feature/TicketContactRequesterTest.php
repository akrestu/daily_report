<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Support\TicketMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Hubungi pemohon via WhatsApp: draf pesan per status + link akses langsung ke tiket */
class TicketContactRequesterTest extends TestCase
{
    use RefreshDatabase;

    private User $lead;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin' => 0, 'level4' => 4, 'level1' => 1] as $slug => $level) {
            Role::create(['name' => ucfirst($slug), 'slug' => $slug, 'level' => $level]);
        }
        $it = Department::create(['name' => 'IT Department', 'code' => 'IT']);
        Department::create(['name' => 'HR Department', 'code' => 'HR']);
        TicketCategory::create(['name' => 'IT Support', 'is_active' => true]);

        $this->lead = User::factory()->create(['role_id' => Role::where('slug', 'level4')->value('id'), 'department_id' => $it->id]);
    }

    private function publicTicket(array $overrides = []): Ticket
    {
        return Ticket::createWithNumber($overrides + [
            'requester_name' => 'Joko Chrismandanu',
            'requester_department' => 'Engineering',
            'requester_phone' => '0812-3456-7890',
            'category_id' => TicketCategory::value('id'),
            'type' => 'service_request',
            'title' => 'Perbaikan radio checker',
            'description' => 'Radio tidak menerima transmisi',
            'priority' => 'medium',
        ])->refresh();
    }

    public function test_whatsapp_number_is_normalized_to_international_format(): void
    {
        $this->assertSame('6281234567890', TicketMessage::whatsappNumber('0812-3456-7890'));
        $this->assertSame('6281234567890', TicketMessage::whatsappNumber('+62 812 3456 7890'));
        $this->assertSame('6281234567890', TicketMessage::whatsappNumber('81234567890'));
        $this->assertNull(TicketMessage::whatsappNumber('123'));
        $this->assertNull(TicketMessage::whatsappNumber(null));
    }

    public function test_resolved_message_contains_note_confirmation_request_and_direct_link(): void
    {
        $ticket = $this->publicTicket(['status' => Ticket::STATUS_RESOLVED, 'resolution_note' => 'Baterai diganti, radio sudah normal.']);

        foreach (array_keys(TicketMessage::TONES) as $tone) {
            $text = TicketMessage::compose($ticket, $tone);
            $this->assertStringContainsString($ticket->ticket_number, $text);
            $this->assertStringContainsString('Baterai diganti, radio sudah normal.', $text);
            $this->assertStringContainsString('tutup tiket', $text);
            $this->assertStringContainsString(TicketMessage::requesterLink($ticket), $text);
        }
    }

    public function test_direct_link_opens_ticket_without_typing_the_code(): void
    {
        $ticket = $this->publicTicket();

        $this->get(TicketMessage::requesterLink($ticket))
            ->assertRedirect(route('tickets.public.track.show', $ticket));

        $this->get(route('tickets.public.track.show', $ticket))->assertOk()->assertSee($ticket->ticket_number);
    }

    public function test_direct_link_with_wrong_code_is_rejected(): void
    {
        $ticket = $this->publicTicket();

        $this->get(route('tickets.public.track.access', ['ticket' => $ticket->ticket_number, 'token' => 'SALAH123']))
            ->assertRedirect(route('tickets.public.track'))
            ->assertSessionHasErrors('ticket_number');

        $this->get(route('tickets.public.track.show', $ticket))->assertRedirect();
    }

    public function test_staff_sees_contact_card_with_whatsapp_link(): void
    {
        $ticket = $this->publicTicket(['status' => Ticket::STATUS_RESOLVED, 'resolution_note' => 'Sudah normal.']);

        $this->actingAs($this->lead)->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Hubungi pemohon')
            ->assertSee('Kirim lewat WhatsApp')
            ->assertSee('wa.me/6281234567890', false);
    }
}
