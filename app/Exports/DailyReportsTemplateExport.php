<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class DailyReportsTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'job_name',
            'department',
            'status',
            'report_date',
            'due_date',
            'description',
            'remark',
            'user_id'
        ];
    }

    /**
     * @return array
     */
    public function array(): array
    {
        // Today and tomorrow's date in d/m/Y format
        $today = Carbon::now()->format('d/m/Y');
        $tomorrow = Carbon::tomorrow()->format('d/m/Y');

        // Sample data
        return [
            [
                'Monthly Report',
                'IT Department',
                'pending',
                $today,
                $tomorrow,
                'This is a sample description of the task or job.',
                'Additional remarks can be added here.',
                'leader1'
            ],
            [
                'Weekly Meeting Minutes',
                'HR Department',
                'in_progress',
                $today,
                $tomorrow,
                'Document the minutes from weekly department meeting.',
                'Remember to include action items and assignments.',
                'manager1'
            ],
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
        ]);
        
        // Add comment to job_name cell
        $sheet->getComment('A1')->getText()->createTextRun('Required. Name of the job or task.');
        
        // Add comment to department cell
        $sheet->getComment('B1')->getText()->createTextRun('Required. Must match an existing department name in the system.');
        
        // Add comment to status cell
        $sheet->getComment('C1')->getText()->createTextRun('Required. Must be one of: pending, in_progress, completed');
        
        // Add comment to report_date cell
        $sheet->getComment('D1')->getText()->createTextRun('Required. Format: DD/MM/YYYY (e.g., 28/04/2025)');
        
        // Add comment to due_date cell
        $sheet->getComment('E1')->getText()->createTextRun('Required. Format: DD/MM/YYYY (e.g., 30/04/2025). Must be on or after report_date.');
        
        // Add comment to description cell
        $sheet->getComment('F1')->getText()->createTextRun('Required. Detailed description of the job/task.');
        
        // Add comment to remark cell
        $sheet->getComment('G1')->getText()->createTextRun('Optional. Additional notes or remarks.');
        
        // Add comment to user_id cell
        $sheet->getComment('H1')->getText()->createTextRun('Required. User ID of the Person In Charge. Must be a valid user_id in the system.');
        
        return $sheet;
    }
}