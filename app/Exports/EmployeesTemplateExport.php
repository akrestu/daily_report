<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EmployeesTemplateExport implements FromArray, ShouldAutoSize, WithColumnFormatting, WithHeadings
{
    public function headings(): array
    {
        return ['nik', 'nama', 'departemen', 'jabatan', 'job_site', 'no_hp', 'status'];
    }

    public function array(): array
    {
        return [
            ['0012345', 'Budi Santoso', 'HR', 'Staff Admin', '', '081234567890', 'aktif'],
            ['0012346', 'Siti Aminah', 'IT Department', 'Supervisor', '', '', 'aktif'],
        ];
    }

    public function columnFormats(): array
    {
        // NIK & no HP sebagai teks agar angka 0 di depan tidak hilang
        return ['A' => NumberFormat::FORMAT_TEXT, 'F' => NumberFormat::FORMAT_TEXT];
    }
}
