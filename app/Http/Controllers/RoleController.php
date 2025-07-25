<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\User;
class RoleController extends Controller
{
     public function createRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string|max:255|unique:roles,name',
        ]);

        // create role
        Role::create([
            'name' => $request->role,
        ]);

        return response()->json(['message' => 'Role added successfully']);
    }
    //load role user form

    public function loadRoleUserForm()
    {
        return view('layouts.app', ['defaultComponent' => 'UserRole']);
    }
    //load role list
    public function getListRoles()
    {
        $roles = Role::select('id', 'name')->get(); // Only return required fields
        return response()->json($roles);
    }
    //Assign user role
    public function assignRole(Request $request, $userId)
    {
        // Validate that a single role_id is sent
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($userId);

        // Check if the role already exists for the user
        $alreadyAssigned = DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('role_id', $request->role_id)
            ->exists();

        if ($alreadyAssigned) {
            return response()->json([
                'message' => 'This role is already assigned to the user.',
            ], 409); // 409 Conflict
        }

        // Insert the new role
        DB::table('role_user')->insert([
            'user_id' => $user->id,
            'role_id' => $request->role_id,
        ]);

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user->load('roles'),
        ]);
    }


}
