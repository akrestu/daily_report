<?php

namespace App\Support;

use App\Models\JobPlan;
use App\Models\Ticket;
use App\Models\User;
use App\Policies\TicketPolicy;

/**
 * Struktur menu shell (sidebar, rail, bottom nav, sheet "Lainnya") per peran.
 * Aturan visibilitas sama dengan navigasi lama (layouts/navigation.blade.php sebelum redesign).
 */
class Navigation
{
    public function __construct(private User $user)
    {
    }

    /**
     * Grup menu: [['label' => ?string, 'items' => [item, ...]], ...]
     * item: key, label, icon, url, active, badge
     */
    public function groups(): array
    {
        $user = $this->user;
        $groups = [['label' => null, 'items' => [
            $this->item('home', 'Beranda', 'bi-house', 'dashboard', ['dashboard']),
        ]]];

        if (! $user->isAdmin()) {
            $level = $user->getRoleLevel();
            $reports = [$this->item('reports', 'Laporan Harian', 'bi-file-earmark-text', 'daily-reports.index', ['daily-reports.index', 'daily-reports.show', 'daily-reports.edit'])];

            if (! $user->isLevel8()) {
                $reports[] = $this->item('my-reports', 'Laporan Saya', 'bi-person-lines-fill', 'daily-reports.user-jobs', ['daily-reports.user-jobs']);
                $reports[] = $this->item('create', 'Buat Laporan', 'bi-plus-circle', 'daily-reports.create', ['daily-reports.create']);
            }
            if ($level >= 2) {
                $reports[] = $this->item('approvals', 'Persetujuan', 'bi-check2-square', 'daily-reports.pending', ['daily-reports.pending']);
                $reports[] = $this->item('assigned', 'Ditugaskan ke Saya', 'bi-person-check', 'daily-reports.assigned-jobs', ['daily-reports.assigned-jobs']);
            }
            $groups[] = ['label' => 'Laporan', 'items' => $reports];

            $plans = [];
            if ($level >= 3) {
                $plans[] = $this->item('plans', 'Rencana Kerja', 'bi-calendar3', 'job-plans.index', ['job-plans.index', 'job-plans.create', 'job-plans.show', 'job-plans.edit']);
            }
            if (! $user->isLevel8()) {
                $count = JobPlan::where('assignee_id', $user->id)->where('status', 'assigned')->count();
                $plans[] = $this->item('plans-mine', 'Rencana untuk Saya', 'bi-calendar-check', 'job-plans.assigned-to-me', ['job-plans.assigned-to-me'], $count);
            }
            if ($plans) {
                $groups[] = ['label' => 'Rencana Kerja', 'items' => $plans];
            }
        }

        $services = [];
        if (! $user->isAdmin()) {
            $services[] = $this->item('tickets-mine', 'Tiket Saya', 'bi-ticket-perforated', 'tickets.mine', ['tickets.mine', 'tickets.create']);
        }
        if (TicketPolicy::isTriager($user)) {
            $services[] = $this->item('tickets', 'Tiket Masuk', 'bi-inbox', 'tickets.index', ['tickets.index', 'tickets.show'], Ticket::where('status', Ticket::STATUS_OPEN)->count());
            $services[] = $this->item('tickets-kpi', 'Dashboard Tiket', 'bi-graph-up', 'tickets.dashboard', ['tickets.dashboard']);
        }
        if (! $user->isAdmin() && ! $user->isLevel8()) {
            $services[] = $this->item('org', 'Organisasi', 'bi-diagram-3', 'organization.chart', ['organization.chart']);
        }
        $groups[] = ['label' => 'Layanan', 'items' => $services];

        if ($user->isAdmin()) {
            $groups[] = ['label' => 'Admin', 'items' => [
                $this->item('admin-users', 'Pengguna', 'bi-people', 'admin.users.index', ['admin.users.*']),
                $this->item('admin-departments', 'Departemen', 'bi-building', 'admin.departments.index', ['admin.departments.*']),
                $this->item('admin-sections', 'Section', 'bi-diagram-2', 'admin.sections.index', ['admin.sections.*']),
                $this->item('admin-job-sites', 'Job Site', 'bi-geo-alt', 'admin.job-sites.index', ['admin.job-sites.*']),
                $this->item('admin-employees', 'Master Karyawan', 'bi-person-vcard', 'admin.employees.index', ['admin.employees.*']),
                $this->item('admin-ticket-categories', 'Kategori Tiket', 'bi-tags', 'admin.ticket-categories.index', ['admin.ticket-categories.*']),
                $this->item('admin-sla', 'SLA & Hari Libur', 'bi-stopwatch', 'admin.sla.index', ['admin.sla.*']),
            ]];
        }

        return array_values(array_filter($groups, fn ($group) => $group['items']));
    }

    /** Semua item dalam satu daftar datar. */
    public function items(): array
    {
        return array_merge(...array_column($this->groups(), 'items'));
    }

    /**
     * Bottom nav mobile: Beranda, Laporan, [FAB], Tiket, Lainnya (sheet berisi sisa menu).
     * FAB = Buat Laporan; Level 8 & Admin tidak membuat laporan → FAB ke Persetujuan / Tiket Masuk.
     */
    public function bottomNav(): array
    {
        $groups = $this->groups();
        $items = collect(array_merge(...array_column($groups, 'items')))->keyBy('key');

        $fab = $items->get('create') ?? $items->get('approvals') ?? $items->get('tickets') ?? $items['home'];
        $reports = $items->get('reports') ?? $items->get('tickets-kpi') ?? $items['home'];
        $tickets = $items->get('tickets') ?? $items->get('tickets-mine');

        $primary = array_filter([$items['home']['key'], $reports['key'], $fab['key'], $tickets['key'] ?? null]);
        $more = collect($groups)
            ->map(fn ($g) => ['label' => $g['label'], 'items' => array_values(array_filter($g['items'], fn ($i) => ! in_array($i['key'], $primary, true)))])
            ->filter(fn ($g) => $g['items'])
            ->values()
            ->all();

        return [
            'home' => $items['home'],
            'reports' => $reports,
            'fab' => $fab,
            'tickets' => $tickets,
            'more' => $more,
            'moreActive' => collect($more)->pluck('items')->flatten(1)->contains('active', true),
        ];
    }

    private function item(string $key, string $label, string $icon, string $route, array $patterns, ?int $badge = null): array
    {
        return [
            'key' => $key,
            'label' => $label,
            'icon' => $icon,
            'url' => route($route),
            'active' => request()->routeIs(...$patterns),
            'badge' => $badge ?: null,
        ];
    }
}
