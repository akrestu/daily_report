<?php

namespace Tests\Feature;

use App\Imports\EmployeesImport;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class TicketRequesterTest extends TestCase
{
    use RefreshDatabase;

    private TicketCategory $category;

    private Department $hr;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin' => 0, 'level4' => 4, 'level1' => 1] as $slug => $level) {
            Role::create(['name' => ucfirst($slug), 'slug' => $slug, 'level' => $level]);
        }
        Department::create(['name' => 'IT Department', 'code' => 'IT']);
        $this->hr = Department::create(['name' => 'HR Department', 'code' => 'HR']);
        $this->category = TicketCategory::create(['name' => 'IT Support', 'is_active' => true]);
    }

    private function user(string $role, string $dept): User
    {
        return User::factory()->create([
            'role_id' => Role::where('slug', $role)->value('id'),
            'department_id' => Department::where('code', $dept)->value('id'),
        ]);
    }

    private function ticketPayload(array $overrides = []): array
    {
        return $overrides + [
            'type' => 'incident',
            'category_id' => $this->category->id,
            'title' => 'Laptop tidak menyala',
            'description' => 'Sejak pagi tidak bisa dinyalakan',
            'priority' => 'high',
        ];
    }

    public function test_public_ticket_uses_requester_data_from_employee_master(): void
    {
        $employee = Employee::create(['nik' => '0012345', 'name' => 'Budi Santoso', 'department_id' => $this->hr->id]);

        // Nama/departemen yang dikirim manual diabaikan; data diambil dari master
        $this->post('/request', $this->ticketPayload([
            'nik' => ' 0012345 ',
            'requester_name' => 'Nama Palsu',
        ]))->assertRedirect(route('tickets.public.success'));

        $ticket = Ticket::first();
        $this->assertSame($employee->id, $ticket->employee_id);
        $this->assertSame('Budi Santoso', $ticket->requester_name);
        $this->assertSame('HR Department', $ticket->requester_department);
        $this->assertSame('0012345', $ticket->requester_employee_id);
    }

    public function test_public_ticket_rejects_unknown_or_inactive_nik(): void
    {
        Employee::create(['nik' => '999', 'name' => 'Mantan', 'department_id' => $this->hr->id, 'is_active' => false]);

        $this->post('/request', $this->ticketPayload(['nik' => '999']))->assertSessionHasErrors('nik');
        $this->post('/request', $this->ticketPayload(['nik' => 'TIDAKADA']))->assertSessionHasErrors('nik');

        $this->assertSame(0, Ticket::count());
    }

    public function test_nik_lookup_returns_minimal_data_for_active_employee_only(): void
    {
        Employee::create(['nik' => '0012345', 'name' => 'Budi Santoso', 'department_id' => $this->hr->id, 'phone' => '0812']);

        $this->getJson(route('tickets.public.employee', ['nik' => '0012345']))
            ->assertOk()
            ->assertExactJson(['found' => true, 'name' => 'Budi Santoso', 'department' => 'HR Department', 'job_site_id' => null]);

        $this->getJson(route('tickets.public.employee', ['nik' => 'X']))->assertNotFound();
    }

    public function test_logged_in_user_can_create_ticket_with_own_profile(): void
    {
        $user = $this->user('level1', 'HR');

        $this->actingAs($user)->get(route('tickets.create'))->assertOk();

        $response = $this->actingAs($user)->post(route('tickets.store'), $this->ticketPayload());

        $ticket = Ticket::first();
        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame($user->id, $ticket->requester_user_id);
        $this->assertSame($user->name, $ticket->requester_name);
        $this->assertSame('HR Department', $ticket->requester_department);
        $this->assertNull($ticket->employee_id);
    }

    public function test_requester_sees_own_tickets_only_and_cannot_change_status(): void
    {
        $owner = $this->user('level1', 'HR');
        $other = $this->user('level1', 'HR');

        $this->actingAs($owner)->post(route('tickets.store'), $this->ticketPayload());
        $ticket = Ticket::first();

        $this->actingAs($owner)->get(route('tickets.mine'))->assertOk()->assertSee($ticket->ticket_number);
        $this->actingAs($owner)->get(route('tickets.show', $ticket))->assertOk();
        $this->actingAs($owner)->post(route('tickets.update-status', $ticket), ['status' => 'closed'])->assertForbidden();

        $this->actingAs($other)->get(route('tickets.mine'))->assertDontSee($ticket->ticket_number);
        $this->actingAs($other)->get(route('tickets.show', $ticket))->assertForbidden();
    }

    public function test_employee_import_upserts_by_nik_and_resolves_department(): void
    {
        Employee::create(['nik' => '0012345', 'name' => 'Nama Lama', 'department_id' => $this->hr->id]);

        $file = UploadedFile::fake()->createWithContent('karyawan.csv', implode("\n", [
            'nik,nama,departemen,jabatan,job_site,no_hp,status',
            '0012345,Budi Santoso,IT,Staff,,0812,aktif',
            '0099999,Siti Aminah,HR Department,Supervisor,,,nonaktif',
        ]));

        Excel::import(new EmployeesImport, $file);

        $this->assertSame(2, Employee::count());
        $budi = Employee::where('nik', '0012345')->first();
        $this->assertSame('Budi Santoso', $budi->name);
        $this->assertSame('IT', $budi->department->code);
        $this->assertFalse(Employee::where('nik', '0099999')->first()->is_active);
    }

    public function test_employee_admin_pages_are_admin_only(): void
    {
        $this->actingAs($this->user('level4', 'IT'))->get(route('admin.employees.index'))->assertRedirect();
        $this->actingAs($this->user('admin', 'IT'))->get(route('admin.employees.index'))->assertOk();
    }
}
