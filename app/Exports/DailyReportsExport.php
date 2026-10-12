<?php

namespace App\Exports;

use App\Models\DailyReport;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Hyperlink;
use Illuminate\Support\Facades\Auth;

class DailyReportsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
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
            // Pre-filter by scope; per-report visibility is enforced by the policy below
            if ($user->isLevel8()) {
                $query->where('job_site_id', $user->job_site_id);
            } elseif (!$user->isAdmin()) {
                $query->where('department_id', $user->department_id);
            }

            // Filter by approval status type
            if (in_array($this->filters['type'] ?? null, ['approved', 'pending', 'rejected'], true)) {
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
        
        $reports = $query->orderBy('created_at', 'desc')->get();

        if (!$this->isAllReports) {
            return $reports;
        }

        return $reports->filter(fn (DailyReport $report) => $user->can('view', $report))->values();
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
            $report->attachment_path ? url('storage/attachments/' . basename($report->attachment_path)) : '',
            $report->attachment_path_2 ? url('storage/attachments/' . basename($report->attachment_path_2)) : '',
            $report->attachment_path_3 ? url('storage/attachments/' . basename($report->attachment_path_3)) : '',
            $report->created_at->format('Y-m-d H:i:s')
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Columns P, Q, R = Attachment 1, 2, 3 (index 16, 17, 18)
                $attachmentCols = ['P', 'Q', 'R'];

                for ($row = 2; $row <= $highestRow; $row++) {
                    foreach ($attachmentCols as $col) {
                        $cell = $sheet->getCell("{$col}{$row}");
                        $url = $cell->getValue();

                        if (!empty($url)) {
                            $cell->setHyperlink(new Hyperlink($url, $url));
                            $sheet->getStyle("{$col}{$row}")->applyFromArray([
                                'font' => [
                                    'color' => ['rgb' => '0563C1'],
                                    'underline' => true,
                                ],
                            ]);
                        }
                    }
                }
            },
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