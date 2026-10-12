<?php

namespace App\Http\Controllers\Admin;

use App\Exports\EmployeesTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\EmployeesImport;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'jobSite'])->withCount('tickets');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $employees = $query->orderBy('name')->paginate(20)->withQueryString();
        $departments = Department::orderBy('name')->get();

        return view('admin.employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        return view('admin.employees.form', $this->formData(new Employee(['is_active' => true])));
    }

    public function store(Request $request)
    {
        Employee::create($this->validated($request));

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(Employee $employee)
    {
        return view('admin.employees.form', $this->formData($employee));
    }

    public function update(Request $request, Employee $employee)
    {
        $employee->update($this->validated($request, $employee));

        return redirect()->route('admin.employees.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        // Karyawan yang pernah membuat tiket hanya dinonaktifkan agar riwayat tiket tetap utuh
        if ($employee->tickets()->withTrashed()->exists()) {
            $employee->update(['is_active' => false]);

            return redirect()->route('admin.employees.index')
                ->with('warning', 'Karyawan memiliki riwayat tiket, sehingga dinonaktifkan (tidak dihapus).');
        }

        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            Excel::import(new EmployeesImport, $request->file('file'));

            return redirect()->route('admin.employees.index')
                ->with('success', 'Data karyawan berhasil diimport.');
        } catch (ValidationException $e) {
            $errors = [];
            foreach ($e->failures() as $failure) {
                $errors[] = "Baris {$failure->row()}: {$failure->errors()[0]}";
            }

            return redirect()->back()->withErrors(['import_errors' => $errors]);
        } catch (\Exception $e) {
            Log::error('Error importing employees', ['error' => $e->getMessage()]);

            return redirect()->back()
                ->with('error', 'Gagal mengimport data karyawan. Periksa format file atau hubungi administrator.');
        }
    }

    public function exportTemplate()
    {
        return Excel::download(new EmployeesTemplateExport, 'template_karyawan.xlsx');
    }

    private function formData(Employee $employee): array
    {
        return [
            'employee' => $employee,
            'departments' => Department::orderBy('name')->get(),
            'jobSites' => JobSite::where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?Employee $employee = null): array
    {
        $request->merge(['nik' => Employee::normalizeNik($request->input('nik'))]);

        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:30', Rule::unique('employees', 'nik')->ignore($employee?->id)],
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'position' => 'nullable|string|max:255',
            'job_site_id' => 'nullable|exists:job_sites,id',
            'phone' => 'nullable|string|max:30',
        ], [
            'nik.unique' => 'NIK sudah terdaftar.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
