<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Str;

class UsersImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Find role by name
        $role = null;
        if (!empty($row['role'])) {
            $role = Role::where('name', $row['role'])->first();
        }
        
        // Find department by name
        $department = null;
        if (!empty($row['department'])) {
            $department = Department::where('name', $row['department'])->first();
        }
        
        return new User([
            'name' => $row['name'],
            'email' => $row['email'],
            'user_id' => $row['user_id'] ?? Str::slug($row['name']),
            'password' => Hash::make($row['password'] ?? 'password123'), // Default password
            'role_id' => $role ? $role->id : null,
            'department_id' => $department ? $department->id : null,
            'email_verified_at' => (strtolower($row['email_verified'] ?? '') == 'yes') ? now() : null,
        ]);
    }
    
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'user_id' => 'nullable|unique:users,user_id',
        ];
    }
}
