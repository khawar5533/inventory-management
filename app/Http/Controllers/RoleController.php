<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

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
}
