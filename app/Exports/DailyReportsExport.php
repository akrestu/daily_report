<?php

namespace App\Exports;

use App\Models\DailyReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;

class DailyReportsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $filters;
    protected $isAllReports;

    public function __construct(array $filters = [], bool $isAllReports = false)
    {
        $this->filters = $filters;
        $this->isAllReports = $isAllReports;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $user = Auth::user();

        // Start building the query
        $query = DailyReport::with(['user', 'department', 'jobSite', 'section', 'pic', 'approver']);

        if (!$this->isAllReports) {
            // Only get user's reports for personal export
            if (isset($this->filters['view']) && $this->filters['view'] === 'assigned') {
                // For assigned reports view - get reports where user is PIC
                $query->where('job_pic', $user->id);
            } else {
                // For my reports view - get reports created by the user
                $query->where('user_id', $user->id);
            }
        } else {
            // For all reports, apply department filter if user is not admin
            if (!($user->role && $user->role->slug === 'admin') && $user->department_id) {
                $query->where('department_id', $user->department_id);
            }

            // Filter by approval status type
            if (isset($this->filters['type'])) {
                $query->where('approval_status', $this->filters['type']);
            }
        }
            
        // Apply common filters
        if (isset($this->filters['search']) && !empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where('job_name', 'like', "%{$search}%");
        }

        if (isset($this->filters['status']) && !empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['department']) && !empty($this->filters['department'])) {
            $query->where('department_id', $this->filters['department']);
        }

        if (isset($this->filters['section']) && !empty($this->filters['section'])) {
            $query->where('section_id', $this->filters['section']);
        }

        // Filter by date range
        if (isset($this->filters['date_from']) && !empty($this->filters['date_from'])) {
            $query->whereDate('report_date', '>=', $this->filters['date_from']);
        }
        
        if (isset($this->filters['date_to']) && !empty($this->filters['date_to'])) {
            $query->whereDate('report_date', '<=', $this->filters['date_to']);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Job Name',
            'Created By',
            'Department',
            'Job Site',
            'Section',
            'Status',
            'Report Date',
            'Due Date',
            'Description',
            'Remarks',
            'PIC',
            'Approval Status',
            'Approved/Rejected By',
            'Rejection Reason',
            'Attachment 1',
            'Attachment 2',
            'Attachment 3',
            'Created At'
        ];
    }

    /**
     * @param mixed $report
     *
     * @return array
     */
    public function map($report): array
    {
        return [
            $report->id,
            $report->job_name,
            $report->user->name ?? 'N/A',
            $report->department->name ?? 'N/A',
            $report->jobSite->name ?? 'N/A',
            $report->section->name ?? 'N/A',
            ucfirst(str_replace('_', ' ', $report->status)),
            $report->report_date->format('Y-m-d'),
            $report->due_date->format('Y-m-d'),
            $report->description,
            $report->remark ?? '',
            $report->pic->name ?? 'N/A',
            ucfirst(str_replace('_', ' ', $report->approval_status)),
            $report->approver->name ?? 'N/A',
            $report->rejection_reason ?? '',
            $report->attachment_original_name ?? '',
            $report->attachment_original_name_2 ?? '',
            $report->attachment_original_name_3 ?? '',
            $report->created_at->format('Y-m-d H:i:s')
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style the header row (A1:S1 = 19 columns)
        $sheet->getStyle('A1:S1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
        ]);

        return $sheet;
    }
}