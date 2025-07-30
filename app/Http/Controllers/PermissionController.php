<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\Role;

class PermissionController extends Controller
{
    /**
     * Load the main Permission management Vue component.
     * This view is responsible for creating new permissions.
     */
    public function loadPermission()
    {
        return view('layouts.app', ['defaultComponent' => 'Permission']);
    }

    /**
     * Load the component responsible for assigning permissions to users.
     */
    public function loadAssignPermission()
    {
        return view('layouts.app', ['defaultComponent' => 'User-Permission']);
    }

    /**
     * Store newly created permissions in the database.
     *
     * @param Request $request expects an array of permission names.
     * - Validates the input.
     * - Normalizes each permission name (e.g., trims, lowercase, replaces spaces/dots with underscores).
     * - Skips if a permission already exists.
     * - Returns created permissions and optionally reports duplicates.
     */
    public function storePermissions(Request $request)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        $created = [];
        $duplicates = [];

        foreach ($request->permissions as $permissionName) {
            // Normalize name
            $name = str_replace([' ', '.'], '_', strtolower(trim($permissionName)));

            if (Permission::where('name', $name)->exists()) {
                $duplicates[] = $name;
            } else {
                $permission = Permission::create(['name' => $name]);
                $created[] = $permission->name;
            }
        }

        // Handle duplicates in response
        if (!empty($duplicates)) {
            $readableDuplicates = array_map(function ($item) {
                return str_replace('_', ' ', $item);
            }, $duplicates);

            return response()->json([
                'message' => 'The following permissions already exist: ' . implode(', ', $readableDuplicates),
                'created' => $created,
            ], 400); // HTTP 400: Bad Request
        }

        // Return successful response
        return response()->json([
            'message' => 'Permissions created successfully.',
            'created' => $created
        ]);
    }

    /**
     * Get all permissions grouped by prefix (e.g., user_create, user_edit).
     * 
     * Returns a structured array like:
     * [
     *   {
     *     title: "User",
     *     permissions: [
     *       { label: "User Create", value: 1 },
     *       ...
     *     ]
     *   },
     *   ...
     * ]
     */
    public function getGroupedPermissions()
    {
        $permissions = Permission::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            $parts = explode('_', $permission->name);
            $groupName = ucfirst($parts[0]);

            $grouped[$groupName][] = [
                'label' => ucwords(str_replace('_', ' ', $permission->name)),
                'value' => $permission->id
            ];
        }

        $final = [];
        foreach ($grouped as $title => $permissions) {
            $final[] = [
                'title' => $title,
                'permissions' => $permissions
            ];
        }

        return response()->json($final);
    }

    /**
     * Assign one or more permissions to a given role.
     * 
     * @param Request $request must include:
     * - role_id: the ID of the role to assign permissions to
     * - permission_ids: array of permission IDs to be assigned
     * 
     * This method uses the sync() method to overwrite any existing assignments.
     */
    public function assignPermissionsToRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        $role->permissions()->sync($request->permission_ids); // Sync overwrites old permissions

        return response()->json(['message' => 'Permissions assigned successfully.']);
    }

    public function getRolePermissions($roleId)
    {
        $permissions = DB::table('permissions')
        ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
        ->where('permission_role.role_id', $roleId)
        ->whereNull('permissions.deleted_at') // Exclude soft-deleted
        ->select('permissions.*')
        ->get();

        return response()->json(['permissions' => $permissions]);
    }

     public function softDelete($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete(); // Soft delete
        return response()->json(['message' => 'Permission deleted Successfully.']);
    }
}


