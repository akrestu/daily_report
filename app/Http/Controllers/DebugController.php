<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DebugController extends Controller
{
    public function debugRoles()
    {
        // Get current user
        $user = Auth::user();
        $userId = $user->id;
        $userRole = $user->role;
        $isAdmin = $user->isAdmin();
        
        // Get all roles
        $roles = Role::all();
        
        // Get admin role specifically
        $adminRole = Role::where('slug', 'admin')->first();
        
        // Get genesis25 user
        $genesis = User::where('user_id', 'genesis25')->first();
        $genesisRole = $genesis ? $genesis->role : null;
        $genesisIsAdmin = $genesis ? $genesis->isAdmin() : false;
        
        return response()->json([
            'current_user' => [
                'id' => $userId,
                'name' => $user->name,
                'email' => $user->email,
                'user_id' => $user->user_id,
                'role_id' => $user->role_id,
                'role' => $userRole,
                'is_admin' => $isAdmin,
                'role_slug' => $userRole ? $userRole->slug : null,
            ],
            'roles' => $roles,
            'admin_role' => $adminRole,
            'genesis_user' => [
                'id' => $genesis ? $genesis->id : null,
                'name' => $genesis ? $genesis->name : null,
                'email' => $genesis ? $genesis->email : null,
                'user_id' => $genesis ? $genesis->user_id : null,
                'role_id' => $genesis ? $genesis->role_id : null,
                'role' => $genesisRole,
                'is_admin' => $genesisIsAdmin,
                'role_slug' => $genesisRole ? $genesisRole->slug : null,
            ],
        ]);
    }
}
