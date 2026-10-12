<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SlaController extends Controller
{
    public function index()
    {
        $policies = SlaPolicy::all()->keyBy('priority');
        $holidays = Holiday::where('date', '>=', now()->subYear()->startOfYear())->orderBy('date')->get();

        return view('admin.sla.index', compact('policies', 'holidays'));
    }

    /**
     * Input dalam JAM kerja (boleh desimal, mis. 0.5 = 30 menit); disimpan dalam menit.
     */
    public function update(Request $request)
    {
        $rules = [];
        foreach (array_keys(Ticket::PRIORITIES) as $priority) {
            $rules["policies.$priority.response_hours"] = 'required|numeric|min:0.25|max:2000';
            $rules["policies.$priority.resolution_hours"] = "required|numeric|min:0.25|max:2000|gte:policies.$priority.response_hours";
        }

        $validated = $request->validate($rules, [
            '*.gte' => 'Target penyelesaian tidak boleh lebih cepat dari target respons.',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['policies'] as $priority => $values) {
                SlaPolicy::updateOrCreate(['priority' => $priority], [
                    'response_minutes' => (int) round($values['response_hours'] * 60),
                    'resolution_minutes' => (int) round($values['resolution_hours'] * 60),
                ]);
            }
        });

        return back()->with('success', 'Target SLA disimpan. Berlaku untuk tiket baru dan tiket yang prioritasnya diubah.');
    }

    public function storeHoliday(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date|unique:holidays,date',
            'name' => 'required|string|max:255',
        ], [
            'date.unique' => 'Tanggal tersebut sudah terdaftar sebagai hari libur.',
        ]);

        Holiday::create($validated);

        return back()->with('success', 'Hari libur ditambahkan.');
    }

    public function destroyHoliday(Holiday $holiday)
    {
        $holiday->delete();

        return back()->with('success', 'Hari libur dihapus.');
    }
}
