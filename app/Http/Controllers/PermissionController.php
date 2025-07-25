<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
class PermissionController extends Controller
{
    public function loadPermission()
    {   
        return view('layouts.app', ['defaultComponent' => 'Permission']);
    }
    
    public function storePermissions(Request $request)
    {
            $request->validate([
                'permissions' => 'required|array',
            ]);

            $created = [];
            $duplicates = [];

            foreach ($request->permissions as $permissionName) {
                // Normalize permission name: lowercase + underscores
                $name = str_replace([' ', '.'], '_', strtolower(trim($permissionName)));

                // Check if permission already exists
                if (Permission::where('name', $name)->exists()) {
                    $duplicates[] = $name;
                } else {
                    $permission = Permission::create(['name' => $name]);
                    $created[] = $permission->name;
                }
            }

            // If there are duplicates, return an error response
           if (!empty($duplicates)) {
                // Replace underscores with spaces in duplicate names
                $readableDuplicates = array_map(function ($item) {
                    return str_replace('_', ' ', $item);
                }, $duplicates);

                return response()->json([
                    'message' => 'The following permissions already exist: ' . implode(', ', $readableDuplicates),
                    'created' => $created,
                ], 400);
            }

            return response()->json([
                'message' => 'Permissions created successfully.',
                'created' => $created
            ]);
        }
    
}
