<?php

namespace App\Imports;

use App\Models\DailyReport;
use App\Models\Department;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class DailyReportsImport extends StringValueBinder implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, WithCustomValueBinder
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Get the current user
        $user = Auth::user();
        
        // Find department by name
        $department = Department::where('name', $row['department'])->first();
        if (!$department) {
            // Skip this row if department doesn't exist
            return null;
        }
        
        // Find PIC by user_id instead of email
        $pic = User::where('user_id', $row['user_id'])->first();
        if (!$pic) {
            // Skip this row if PIC doesn't exist
            return null;
        }
        
        // Parse dates with flexible format handling
        $reportDate = $this->parseDate($row['report_date']);
        $dueDate = $this->parseDate($row['due_date']);

        if (!$reportDate || !$dueDate) {
            return null;
        }
        
        // Create new daily report
        return new DailyReport([
            'user_id' => $user->id,
            'job_name' => $row['job_name'],
            'department_id' => $department->id,
            'status' => $row['status'],
            'report_date' => $reportDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            'description' => $row['description'],
            'remark' => $row['remark'] ?? null,
            'job_pic' => $pic->id,
        ]);
    }
    
    /**
     * Parse date value from Excel with multiple format support
     * 
     * @param mixed $value
     * @return Carbon|null
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }
        
        // Try to parse the date with multiple formats
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];
        
        // Check if it's a numeric value (Excel date)
        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
            } catch (Exception $e) {
                // Not a valid Excel date, continue with other formats
            }
        }
        
        // Try each format
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value);
            } catch (Exception $e) {
                // Try next format
            }
        }
        
        // Try letting Carbon parse it automatically
        try {
            return Carbon::parse($value);
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'job_name' => 'required|string|max:255',
            'department' => 'required|string|exists:departments,name',
            'status' => 'required|in:pending,in_progress,completed',
            'report_date' => 'required',
            'due_date' => 'required',
            'description' => 'required|string',
            'user_id' => 'required|exists:users,user_id',
        ];
    }
    
    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'department.exists' => 'The department ":input" does not exist in the system.',
            'user_id.exists' => 'The Person In Charge user ID ":input" does not exist in the system.',
            'status.in' => 'The status must be one of: pending, in_progress, completed.',
            'report_date.required' => 'The report date is required. Please use DD/MM/YYYY format (e.g., 28/04/2025).',
            'due_date.required' => 'The due date is required. Please use DD/MM/YYYY format (e.g., 30/04/2025).',
        ];
    }
    
    /**
     * Custom validation to ensure due_date is after or equal to report_date
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            
            if (isset($data['report_date']) && isset($data['due_date'])) {
                $reportDate = $this->parseDate($data['report_date']);
                $dueDate = $this->parseDate($data['due_date']);
                
                if (!$reportDate) {
                    $validator->errors()->add('report_date', 'Invalid date format. Please use DD/MM/YYYY format (e.g., 28/04/2025).');
                }
                
                if (!$dueDate) {
                    $validator->errors()->add('due_date', 'Invalid date format. Please use DD/MM/YYYY format (e.g., 30/04/2025).');
                }
                
                if ($reportDate && $dueDate && $dueDate < $reportDate) {
                    $validator->errors()->add('due_date', 'The due date must be on or after the report date.');
                }
            }
        });
    }
}