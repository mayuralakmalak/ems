<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::latest()->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'status' => 'required|in:active,inactive',
        ]);

        Role::create([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'status' => 'required|in:active,inactive',
        ]);

        $role->update([
            'name' => $validated['name'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Prevent deletion if role is assigned to users
        $usersWithRole = \App\Models\User::role($role->name)->count();
        if ($usersWithRole > 0) {
            return back()->with('error', 'Cannot delete role that is assigned to users.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'role_ids' => 'required|string',
        ]);

        $roleIds = json_decode($request->role_ids);
        
        if (!is_array($roleIds) || empty($roleIds)) {
            return back()->with('error', 'No roles selected for deletion.');
        }

        // Validate that all IDs exist
        $existingIds = Role::whereIn('id', $roleIds)->pluck('id')->toArray();
        $invalidIds = array_diff($roleIds, $existingIds);
        
        if (!empty($invalidIds)) {
            return back()->with('error', 'Some selected roles do not exist.');
        }

        $roles = Role::whereIn('id', $roleIds)->get();
        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($roles as $role) {
            $usersWithRole = \App\Models\User::role($role->name)->count();
            if ($usersWithRole == 0) {
                $role->delete();
                $deletedCount++;
            } else {
                $skippedCount++;
            }
        }

        if ($deletedCount > 0) {
            $message = $deletedCount . ' role(s) deleted successfully.';
            if ($skippedCount > 0) {
                $message .= ' ' . $skippedCount . ' role(s) could not be deleted as they are assigned to users.';
            }
            return redirect()->route('admin.roles.index')->with('success', $message);
        }

        return back()->with('error', 'No roles could be deleted. All selected roles are assigned to users.');
    }

    public function editPermissions($id)
    {
        $role = Role::findOrFail($id);
        
        // Ensure all permissions exist
        $allPermissions = [
            'Admin Access',
            'Exhibition Management',
            'Booking Management',
            'Booth Management',
            'Floorplan Management',
            'Payment Management',
            'Financial Management',
            'User Management',
            'Role & Permission Management',
            'Document Management',
            'Document Category Management',
            'Badge Management',
            'Sponsorship Management',
            'Sponsorship Booking Management',
            'Category Management',
            'Settings Management',
            'Booth Request Management',
            'Discount Management',
            'Checklist Management',
            'Service Configuration Management',
            'Analytics Management',
            'Exhibitor Management',
            'Communication Management',
            'Email Management',
            'Notification Management',
            'Additional Service Request Management',
            'Report Generation'
        ];
        
        foreach ($allPermissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
        }
        
        $permissions = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit-permissions', compact('role', 'permissions', 'rolePermissions'));
    }

    public function updatePermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $permissionIds = $request->permissions ?? [];
        
        // Convert permission IDs to Permission models
        $permissions = Permission::whereIn('id', $permissionIds)->get();
        
        $role->syncPermissions($permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Permissions updated successfully.');
    }
}
