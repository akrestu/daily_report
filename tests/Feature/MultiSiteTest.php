<?php

namespace Tests\Feature;

use App\Http\Controllers\DailyReportController;
use App\Models\DailyReport;
use App\Models\Department;
use App\Models\JobSite;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Multi-site: user dengan site utama + site tambahan mencakup laporan, persetujuan, dan daftar PIC
 * di semua site-nya, tetapi tidak di site lain.
 */
class MultiSiteTest extends TestCase
{
    use RefreshDatabase;

    private Department $ops;

    private JobSite $siteA;

    private JobSite $siteB;

    private JobSite $siteC;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin' => 0, 'level3' => 3, 'level4' => 4, 'level5' => 5, 'level8' => 8] as $slug => $level) {
            Role::create(['name' => ucfirst($slug), 'slug' => $slug, 'level' => $level]);
        }
        $this->ops = Department::create(['name' => 'Operations', 'code' => 'OPS']);
        $this->siteA = JobSite::create(['name' => 'Site A', 'code' => 'A', 'is_active' => true]);
        $this->siteB = JobSite::create(['name' => 'Site B', 'code' => 'B', 'is_active' => true]);
        $this->siteC = JobSite::create(['name' => 'Site C', 'code' => 'C', 'is_active' => true]);
    }

    private function user(string $role, JobSite $site, array $extra = []): User
    {
        $user = User::factory()->create([
            'role_id' => Role::where('slug', $role)->value('id'),
            'department_id' => $this->ops->id,
            'job_site_id' => $site->id,
        ]);
        $user->syncExtraJobSites(collect($extra)->pluck('id')->all());

        return $user->fresh();
    }

    private function report(User $owner, JobSite $site): DailyReport
    {
        return DailyReport::create([
            'user_id' => $owner->id, 'department_id' => $this->ops->id, 'job_site_id' => $site->id,
            'job_name' => 'Laporan '.$site->code, 'report_date' => now()->toDateString(), 'due_date' => now()->toDateString(),
            'description' => 'x', 'status' => 'pending', 'approval_status' => 'pending', 'job_pic' => $owner->id,
        ]);
    }

    private function eligiblePics(User $user): array
    {
        $method = new \ReflectionMethod(DailyReportController::class, 'getEligiblePics');

        return array_values($method->invoke(app(DailyReportController::class), $user));
    }

    public function test_job_site_ids_combine_main_and_extra_sites(): void
    {
        $lead = $this->user('level4', $this->siteA, [$this->siteB, $this->siteA]);

        $this->assertEqualsCanonicalizing([$this->siteA->id, $this->siteB->id], $lead->jobSiteIds());
        $this->assertTrue($lead->coversJobSite($this->siteB->id));
        $this->assertFalse($lead->coversJobSite($this->siteC->id));
        // Site utama tidak ikut tersimpan sebagai site tambahan
        $this->assertSame([$this->siteB->id], $lead->extraJobSites->pluck('id')->all());
    }

    public function test_multi_site_supervisor_appears_in_pic_list_of_both_sites_only(): void
    {
        $lead = $this->user('level4', $this->siteA, [$this->siteB]);

        $this->assertContains($lead->id, $this->eligiblePics($this->user('level3', $this->siteA)));
        $this->assertContains($lead->id, $this->eligiblePics($this->user('level3', $this->siteB)));
        $this->assertNotContains($lead->id, $this->eligiblePics($this->user('level3', $this->siteC)));
    }

    public function test_multi_site_user_lists_reports_from_all_their_sites_only(): void
    {
        $lead = $this->user('level4', $this->siteA, [$this->siteB]);
        // Daftar laporan hanya menampilkan laporan yang sudah diproses
        foreach ([$this->siteA, $this->siteB, $this->siteC] as $site) {
            $this->report($this->user('level3', $site), $site)
                ->update(['approval_status' => 'approved', 'status' => 'completed', 'approved_by' => $lead->id]);
        }

        $this->actingAs($lead)->get(route('daily-reports.index'))
            ->assertOk()
            ->assertSee('Laporan A')
            ->assertSee('Laporan B')
            ->assertDontSee('Laporan C');
    }

    public function test_multi_site_level8_can_view_reports_across_their_sites(): void
    {
        $boss = $this->user('level8', $this->siteA, [$this->siteB]);
        $owner = $this->user('level5', $this->siteB);

        $this->assertTrue($boss->can('view', $this->report($owner, $this->siteB)));
        $this->assertFalse($boss->can('view', $this->report($this->user('level5', $this->siteC), $this->siteC)));
    }

    public function test_admin_can_set_extra_sites(): void
    {
        $admin = User::factory()->create(['role_id' => Role::where('slug', 'admin')->value('id')]);
        $lead = $this->user('level4', $this->siteA);

        $this->actingAs($admin)->put(route('admin.users.update', $lead), [
            'name' => $lead->name, 'email' => $lead->email, 'role_id' => $lead->role_id,
            'department_id' => $this->ops->id, 'job_site_id' => $this->siteA->id,
            'extra_job_site_ids' => [$this->siteB->id, $this->siteC->id],
        ])->assertSessionHasNoErrors();

        $this->assertEqualsCanonicalizing([$this->siteA->id, $this->siteB->id, $this->siteC->id], $lead->fresh()->jobSiteIds());

        $this->actingAs($admin)->put(route('admin.users.update', $lead), [
            'name' => $lead->name, 'email' => $lead->email, 'role_id' => $lead->role_id,
            'department_id' => $this->ops->id, 'job_site_id' => $this->siteA->id,
        ]);

        $this->assertSame([$this->siteA->id], $lead->fresh()->jobSiteIds());
    }
    public function test_user_edit_page_explains_ticket_access(): void
    {
        $admin = User::factory()->create(['role_id' => Role::where('slug', 'admin')->value('id')]);
        $lead = $this->user('level4', $this->siteA, [$this->siteB]);

        // Departemen OPS belum jadi tim layanan tiket
        $this->actingAs($admin)->get(route('admin.users.edit', $lead))->assertOk()
            ->assertSee('Akses tiket masuk')
            ->assertSee('Tidak bisa memilah tiket')
            ->assertSee('Tidak aktif di departemen ini');

        $this->ops->update(['handles_tickets' => true]);

        $this->actingAs($admin)->get(route('admin.users.edit', $lead))
            ->assertSee('Bisa memilah &amp; menugaskan tiket', false);
    }
    public function test_job_plan_due_today_is_not_overdue_yet(): void
    {
        $lead = $this->user('level4', $this->siteA);
        $plan = fn (string $due) => \App\Models\JobPlan::create([
            'creator_id' => $lead->id, 'assignee_id' => $lead->id, 'department_id' => $this->ops->id,
            'job_site_id' => $this->siteA->id, 'job_name' => 'Perbaikan radio', 'description' => 'x',
            'planned_date' => now()->toDateString(), 'due_date' => $due, 'status' => 'assigned',
        ]);

        $this->travelTo(now()->setTime(23, 30));
        $this->actingAs($lead)->get(route('job-plans.show', $plan(now()->toDateString())))
            ->assertOk()->assertSee('Aktif')->assertDontSee('Lewat due');

        $this->actingAs($lead)->get(route('job-plans.show', $plan(now()->subDay()->toDateString())))
            ->assertSee('Lewat due');
    }
    /** Switch di modal admin selalu mengirim 1/0 (hidden); "0" harus berarti nonaktif */
    public function test_modal_switches_save_off_and_on(): void
    {
        $admin = User::factory()->create(['role_id' => Role::where('slug', 'admin')->value('id')]);
        $this->actingAs($admin);

        $this->put(route('admin.job-sites.update', $this->siteA), ['name' => 'Site A', 'code' => 'A', 'is_active' => '0']);
        $this->assertFalse($this->siteA->fresh()->is_active);

        $this->put(route('admin.departments.update', $this->ops), ['name' => 'Operations', 'code' => 'OPS', 'handles_tickets' => '1']);
        $this->assertTrue($this->ops->fresh()->handles_tickets);

        $this->put(route('admin.departments.update', $this->ops), ['name' => 'Operations', 'code' => 'OPS', 'handles_tickets' => '0']);
        $this->assertFalse($this->ops->fresh()->handles_tickets);
    }
}
