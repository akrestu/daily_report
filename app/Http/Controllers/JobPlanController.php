<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobPlan;
use App\Models\JobSite;
use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobPlanController extends Controller
{
    // ── Access guard ─────────────────────────────────────────────────────

    private function canCreatePlan(User $user): bool
    {
        return $user->isAdmin() || $user->getRoleLevel() >= 3;
    }

    // ── Index: plans created by me ────────────────────────────────────────

    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$this->canCreatePlan($user)) {
            abort(403, 'Fitur ini hanya tersedia untuk Level 3 ke atas.');
        }

        $query = JobPlan::with(['assignee', 'department', 'section', 'jobSite'])
            ->where('creator_id', $user->id);

        if ($request->filled('search')) {
            $query->where('job_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('planned_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('planned_date', '<=', $request->date_to);
        }

        $plans = $query->orderBy('planned_date', 'desc')->paginate(15)->withQueryString();

        return view('job-plans.index', compact('plans'));
    }

    // ── Plans assigned to me ──────────────────────────────────────────────

    public function assignedToMe(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $query = JobPlan::with(['creator', 'department', 'section', 'jobSite'])
            ->where('assignee_id', $user->id);

        if ($request->filled('search')) {
            $query->where('job_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('planned_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('planned_date', '<=', $request->date_to);
        }

        $plans = $query->orderBy('planned_date', 'desc')->paginate(15)->withQueryString();

        $activeCount = JobPlan::where('assignee_id', $user->id)->where('status', 'assigned')->count();

        return view('job-plans.assigned-to-me', compact('plans', 'activeCount'));
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function create()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$this->canCreatePlan($user)) {
            abort(403, 'Fitur ini hanya tersedia untuk Level 3 ke atas.');
        }

        $eligibleAssignees = $this->getEligibleAssignees($user);
        $jobSites          = JobSite::where('is_active', true)->orderBy('name')->get();
        $sections          = Section::where('department_id', $user->department_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('job-plans.create', compact('eligibleAssignees', 'jobSites', 'sections', 'user'));
    }

    // ── Store ─────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$this->canCreatePlan($user)) {
            abort(403, 'Fitur ini hanya tersedia untuk Level 3 ke atas.');
        }

        $validated = $request->validate([
            'assignee_id'  => 'required|exists:users,id',
            'job_site_id'  => 'nullable|exists:job_sites,id',
            'section_id'   => 'nullable|exists:sections,id',
            'job_name'     => 'required|string|max:255',
            'description'  => 'required|string|max:2000',
            'remark'       => 'nullable|string|max:1000',
            'planned_date' => 'required|date',
            'due_date'     => 'required|date|after_or_equal:planned_date',
        ]);

        // Verify assignee is in eligible list
        $eligibleIds = collect($this->getEligibleAssignees($user))->pluck('id')->toArray();
        if (!in_array((int) $validated['assignee_id'], $eligibleIds)) {
            return back()->withErrors(['assignee_id' => 'Assignee yang dipilih tidak valid.'])->withInput();
        }

        DB::beginTransaction();
        try {
            JobPlan::create([
                'creator_id'   => $user->id,
                'assignee_id'  => $validated['assignee_id'],
                'department_id' => $user->department_id,
                'job_site_id'  => $validated['job_site_id'] ?? null,
                'section_id'   => $validated['section_id'] ?? null,
                'job_name'     => $validated['job_name'],
                'description'  => $validated['description'],
                'remark'       => $validated['remark'] ?? null,
                'planned_date' => $validated['planned_date'],
                'due_date'     => $validated['due_date'],
                'status'       => 'assigned',
            ]);

            DB::commit();

            return redirect()->route('job-plans.index')
                ->with('success', 'Job plan berhasil dibuat dan dikirim ke assignee.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal membuat job plan: ' . $e->getMessage());

            return back()->with('error', 'Gagal membuat job plan. Silakan coba lagi.')->withInput();
        }
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function show(JobPlan $jobPlan)
    {
        $this->authorize('view', $jobPlan);

        $jobPlan->load(['creator.role', 'assignee.role', 'department', 'jobSite', 'section', 'convertedReports']);

        $plan = $jobPlan;

        return view('job-plans.show', compact('plan'));
    }

    // ── Edit ──────────────────────────────────────────────────────────────

    public function edit(JobPlan $jobPlan)
    {
        $this->authorize('update', $jobPlan);

        /** @var User $user */
        $user = Auth::user();

        $eligibleAssignees = $this->getEligibleAssignees($user, $jobPlan->assignee_id);
        $jobSites          = JobSite::where('is_active', true)->orderBy('name')->get();
        $sections          = Section::where('department_id', $user->department_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $plan = $jobPlan;

        return view('job-plans.edit', compact('plan', 'eligibleAssignees', 'jobSites', 'sections', 'user'));
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function update(Request $request, JobPlan $jobPlan)
    {
        $this->authorize('update', $jobPlan);

        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'assignee_id'  => 'required|exists:users,id',
            'job_site_id'  => 'nullable|exists:job_sites,id',
            'section_id'   => 'nullable|exists:sections,id',
            'job_name'     => 'required|string|max:255',
            'description'  => 'required|string|max:2000',
            'remark'       => 'nullable|string|max:1000',
            'planned_date' => 'required|date',
            'due_date'     => 'required|date|after_or_equal:planned_date',
        ]);

        // Verify assignee is in eligible list
        $eligibleIds = collect($this->getEligibleAssignees($user))->pluck('id')->toArray();
        // Always allow current assignee to stay
        $eligibleIds[] = $jobPlan->assignee_id;
        if (!in_array((int) $validated['assignee_id'], $eligibleIds)) {
            return back()->withErrors(['assignee_id' => 'Assignee yang dipilih tidak valid.'])->withInput();
        }

        $jobPlan->update([
            'assignee_id'  => $validated['assignee_id'],
            'job_site_id'  => $validated['job_site_id'] ?? null,
            'section_id'   => $validated['section_id'] ?? null,
            'job_name'     => $validated['job_name'],
            'description'  => $validated['description'],
            'remark'       => $validated['remark'] ?? null,
            'planned_date' => $validated['planned_date'],
            'due_date'     => $validated['due_date'],
        ]);

        return redirect()->route('job-plans.show', $jobPlan)
            ->with('success', 'Job plan berhasil diperbarui.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────

    public function destroy(JobPlan $jobPlan)
    {
        $this->authorize('delete', $jobPlan);

        $jobPlan->delete();

        return redirect()->route('job-plans.index')
            ->with('success', 'Job plan berhasil dihapus.');
    }

    // ── Convert: redirect assignee to create DR pre-filled ───────────────

    public function convert(JobPlan $jobPlan)
    {
        $this->authorize('convert', $jobPlan);

        return redirect()->route('daily-reports.create', ['from_plan' => $jobPlan->id]);
    }

    // ── WhatsApp Share ────────────────────────────────────────────────────

    public function whatsappShare(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$this->canCreatePlan($user)) {
            abort(403, 'Fitur ini hanya tersedia untuk Level 3 ke atas.');
        }

        $validated = $request->validate([
            'search'     => 'nullable|string|max:255',
            'section'    => 'nullable|exists:sections,id',
            'date_from'  => 'nullable|date',
            'date_to'    => 'nullable|date|after_or_equal:date_from',
            'format'     => 'nullable|in:detail,ringkasan',
        ]);

        $format = $validated['format'] ?? 'detail';

        // Base query scoped to this creator's plans
        $buildQuery = function () use ($user, $validated) {
            $q = JobPlan::with(['assignee', 'section', 'jobSite', 'department'])
                ->where('creator_id', $user->id);

            if (!empty($validated['search'])) {
                $q->where('job_name', 'like', '%' . $validated['search'] . '%');
            }
            if (!empty($validated['section'])) {
                $q->where('section_id', $validated['section']);
            }
            if (!empty($validated['date_from'])) {
                $q->whereDate('planned_date', '>=', $validated['date_from']);
            }
            if (!empty($validated['date_to'])) {
                $q->whereDate('planned_date', '<=', $validated['date_to']);
            }
            return $q;
        };

        $statsBase = $buildQuery();
        $stats = [
            'total'     => (clone $statsBase)->count(),
            'assigned'  => (clone $statsBase)->where('status', 'assigned')->count(),
            'converted' => (clone $statsBase)->where('status', 'converted')->count(),
        ];

        $total = $stats['total'];

        if ($format === 'ringkasan') {
            $plans   = $buildQuery()->orderByRaw('section_id IS NULL')->orderBy('section_id')->orderBy('planned_date')->get();
            $limited = false;
            $text    = $this->formatWaTextRingkasan($plans, $validated, $total, $stats);
        } else {
            $limited = $total > 30;
            $plans   = $buildQuery()->orderByRaw('section_id IS NULL')->orderBy('section_id')->orderBy('planned_date')->limit(30)->get();
            $text    = $this->formatWaTextDetail($plans, $validated, $total, $stats);
        }

        return response()->json([
            'text'    => $text,
            'count'   => $total,
            'limited' => $limited,
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────────────

    /**
     * Get users eligible to be assigned a job plan (lower level, same dept/job_site).
     */
    private function getEligibleAssignees(User $creator, ?int $currentAssigneeId = null): \Illuminate\Database\Eloquent\Collection
    {
        $creatorLevel = $creator->getRoleLevel();

        if ($creator->isAdmin()) {
            // Admin can assign to Level 1-7 (Level 8 cannot convert plans to reports)
            $eligibleSlugs = ['level1', 'level2', 'level3', 'level4', 'level5', 'level6', 'level7'];
        } else {
            if ($creatorLevel < 2) {
                return collect();
            }
            $maxLevel = min($creatorLevel - 1, 7);
            $eligibleSlugs = [];
            for ($i = 1; $i <= $maxLevel; $i++) {
                $eligibleSlugs[] = 'level' . $i;
            }
        }

        if (empty($eligibleSlugs)) {
            return collect();
        }

        $roleIds = Role::whereIn('slug', $eligibleSlugs)->pluck('id')->toArray();

        $query = User::whereIn('role_id', $roleIds)->orderBy('name');

        if (!$creator->isAdmin()) {
            if ($creator->isLevel8() && $creator->job_site_id) {
                $query->where('job_site_id', $creator->job_site_id);
            } else {
                $query->where('department_id', $creator->department_id);
            }
        }

        $results = $query->get();

        // Always include current assignee if provided (for edit form)
        if ($currentAssigneeId && !$results->contains('id', $currentAssigneeId)) {
            $currentAssignee = User::find($currentAssigneeId);
            if ($currentAssignee) {
                $results->push($currentAssignee);
            }
        }

        return $results;
    }

    private function buildWaHeader(array $filters, \Illuminate\Database\Eloquent\Collection $plans): array
    {
        $indonesianMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $formatDate = function ($date) use ($indonesianMonths) {
            if (!$date) return '-';
            $d = \Carbon\Carbon::parse($date);
            return $d->day . ' ' . $indonesianMonths[$d->month] . ' ' . $d->year;
        };

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $dateLabel = $filters['date_from'] === $filters['date_to']
                ? $formatDate($filters['date_from'])
                : $formatDate($filters['date_from']) . ' – ' . $formatDate($filters['date_to']);
        } elseif (!empty($filters['date_from'])) {
            $dateLabel = 'Mulai ' . $formatDate($filters['date_from']);
        } elseif (!empty($filters['date_to'])) {
            $dateLabel = 'S.d. ' . $formatDate($filters['date_to']);
        } else {
            $dateLabel = 'Semua Tanggal';
        }

        $siteLabel = null;
        if ($plans->isNotEmpty() && $plans->first()->jobSite) {
            $firstName = $plans->first()->jobSite->name;
            $siteLabel = $plans->every(fn($p) => optional($p->jobSite)->name === $firstName)
                ? $firstName
                : null;
        }

        $lines   = [];
        $lines[] = '*📋 SiGAP — Rekap Job Plan*';
        $lines[] = '';
        $lines[] = '📅 *Periode  :* ' . $dateLabel;
        if ($siteLabel) $lines[] = '📍 *Job Site  :* ' . $siteLabel;

        return $lines;
    }

    private function buildWaStatsBlock(array $stats): array
    {
        $lines   = [];
        $lines[] = '';
        $lines[] = '📈 *STATISTIK*';
        $lines[] = '┌ ⏳ Aktif (belum dikerjakan): ' . $stats['assigned'];
        $lines[] = '├ ✅ Sudah dikonversi        : ' . $stats['converted'];
        $lines[] = '└ 📋 Total                   : ' . $stats['total'];
        return $lines;
    }

    private function formatWaTextDetail(\Illuminate\Database\Eloquent\Collection $plans, array $filters, int $total, array $stats): string
    {
        $lines        = $this->buildWaHeader($filters, $plans);
        $displayTotal = $total > 30 ? "30 dari {$total}" : (string) $total;
        $lines[]      = '🗂 *Tampil    :* ' . $displayTotal . ' rencana';
        $lines        = array_merge($lines, $this->buildWaStatsBlock($stats));

        $grouped = $plans->groupBy(fn($p) => optional($p->section)->name ?? 'Tanpa Section');
        $counter = 1;

        foreach ($grouped as $sectionName => $sectionPlans) {
            $lines[] = '';
            $lines[] = '━━ 📂 *' . strtoupper($sectionName) . '* (' . $sectionPlans->count() . ' rencana) ━━';

            foreach ($sectionPlans as $plan) {
                $statusIcon   = $plan->isConverted() ? '✅' : '⏳';
                $statusLabel  = $plan->isConverted() ? 'Sudah dikerjakan' : 'Belum dikerjakan';
                $assigneeName = optional($plan->assignee)->name ?? '-';
                $plannedDate  = $plan->planned_date?->format('d/m/Y') ?? '-';
                $dueDate      = $plan->due_date?->format('d/m/Y') ?? '-';

                $lines[] = '';
                $lines[] = '*' . $counter . '. ' . $plan->job_name . '*';
                $lines[] = $statusIcon . ' ' . $statusLabel;
                $lines[] = '┌ 👤 *Ditugaskan ke:* ' . $assigneeName;
                $lines[] = '├ 📅 *Tgl Rencana  :* ' . $plannedDate;
                $lines[] = '└ ⏰ *Tenggat      :* ' . $dueDate;

                $counter++;
            }
        }

        $lines[] = '';
        $lines[] = '_*SiGAP* 2026 - Managed by super team HRGA_';

        return implode("\n", $lines);
    }

    private function formatWaTextRingkasan(\Illuminate\Database\Eloquent\Collection $plans, array $filters, int $total, array $stats): string
    {
        $lines   = $this->buildWaHeader($filters, $plans);
        $lines[] = '🗂 *Total     :* ' . $total . ' rencana';
        $lines   = array_merge($lines, $this->buildWaStatsBlock($stats));

        $grouped = $plans->groupBy(fn($p) => optional($p->section)->name ?? 'Tanpa Section');

        foreach ($grouped as $sectionName => $sectionPlans) {
            $sectionConverted = $sectionPlans->where('status', 'converted')->count();
            $sectionAssigned  = $sectionPlans->where('status', 'assigned')->count();

            $lines[] = '';
            $lines[] = '📁 *' . strtoupper($sectionName) . '*';
            $lines[] = '_✅ ' . $sectionConverted . ' selesai  ⏳ ' . $sectionAssigned . ' aktif — ' . $sectionPlans->count() . ' total_';

            foreach ($sectionPlans as $j => $plan) {
                $statusIcon   = $plan->isConverted() ? '✅' : '⏳';
                $assigneeName = optional($plan->assignee)->name ?? '-';
                $lines[]      = ($j + 1) . '. ' . $statusIcon . ' ' . $plan->job_name;
                $lines[]      = '    👤 ' . $assigneeName;
            }
        }

        $lines[] = '';
        $lines[] = '_*SiGAP* 2026 - Managed by super team HRGA_';

        return implode("\n", $lines);
    }
}
