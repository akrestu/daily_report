<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Employee;
use App\Models\JobSite;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Import master karyawan. NIK yang sudah ada akan diperbarui (upsert), bukan diduplikasi.
 * Kolom: nik, nama, departemen (nama/kode), jabatan, job_site (nama/kode), no_hp, status (aktif/nonaktif)
 */
class EmployeesImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithUpserts, WithValidation
{
    private array $departments;

    private array $jobSites;

    public function __construct()
    {
        $this->departments = $this->lookupMap(Department::all(['id', 'name', 'code']));
        $this->jobSites = $this->lookupMap(JobSite::all(['id', 'name', 'code']));
    }

    public function model(array $row)
    {
        return new Employee([
            'nik' => Employee::normalizeNik($row['nik']),
            'name' => trim($row['nama']),
            'department_id' => $this->departments[$this->key($row['departemen'] ?? null)] ?? null,
            'position' => $row['jabatan'] ?? null,
            'job_site_id' => $this->jobSites[$this->key($row['job_site'] ?? null)] ?? null,
            'phone' => isset($row['no_hp']) ? (string) $row['no_hp'] : null,
            'is_active' => $this->key($row['status'] ?? 'aktif') !== 'NONAKTIF',
        ]);
    }

    public function uniqueBy()
    {
        return 'nik';
    }

    public function prepareForValidation($data, $index)
    {
        // Excel sering membaca NIK sebagai angka
        $data['nik'] = isset($data['nik']) ? (string) $data['nik'] : null;

        return $data;
    }

    public function rules(): array
    {
        return [
            'nik' => 'required|string|max:30|distinct',
            'nama' => 'required|string|max:255',
            'departemen' => ['required', function ($attribute, $value, $fail) {
                if (! isset($this->departments[$this->key($value)])) {
                    $fail("Departemen \"{$value}\" tidak ditemukan. Tambahkan dulu di menu Departments.");
                }
            }],
            'status' => 'nullable|in:aktif,nonaktif,Aktif,Nonaktif,AKTIF,NONAKTIF',
        ];
    }

    private function lookupMap($models): array
    {
        $map = [];
        foreach ($models as $model) {
            $map[$this->key($model->name)] = $model->id;
            if ($model->code) {
                $map[$this->key($model->code)] = $model->id;
            }
        }

        return $map;
    }

    private function key($value): string
    {
        return strtoupper(trim((string) $value));
    }
}
