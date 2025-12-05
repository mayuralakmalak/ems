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
        $roles = Role::all();
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
        ]);

        Role::create(['name' => $validated['name']]);

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function editPermissions($id)
    {
        $role = Role::findOrFail($id);
        
        // Ensure default permissions exist
        $defaultPermissions = [
            'Admin Access',
            'Exhibition Management',
            'Payment Management',
            'User Management',
            'Report Generation'
        ];
        
        foreach ($defaultPermissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => 'web']
            );
        }
        
        $permissions = Permission::all();
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

        $permissions = $request->permissions ?? [];
        $role->syncPermissions($permissions);

        return redirect()->route('admin.roles.index')->with('success', 'Permissions updated successfully.');
    }
}
