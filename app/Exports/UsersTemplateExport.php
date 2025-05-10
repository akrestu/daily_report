<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersTemplateExport implements FromArray, WithHeadings, ShouldAutoSize
{
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'name',
            'email',
            'user_id',
            'role',
            'department',
            'password',
            'email_verified',
        ];
    }

    /**
     * @return array
     */
    public function array(): array
    {
        // Sample data
        return [
            [
                'John Doe',
                'john@example.com',
                'john.doe',
                'staff',
                'IT Department',
                'password123',
                'Yes',
            ],
            [
                'Jane Smith',
                'jane@example.com',
                'jane.smith',
                'leader',
                'HR Department',
                'password123',
                'No',
            ],
        ];
    }
} 