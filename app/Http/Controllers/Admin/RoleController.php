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
        abort_unless(auth()->user()->can('Role & Permission Management - View'), 403);
        $roles = Role::latest()->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('Role & Permission Management - Create'), 403);
        return view('admin.roles.create');
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('Role & Permission Management - Create'), 403);
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
        abort_unless(auth()->user()->can('Role & Permission Management - Modify'), 403);
        $role = Role::findOrFail($id);
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Role & Permission Management - Modify'), 403);
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
        abort_unless(auth()->user()->can('Role & Permission Management - Delete'), 403);
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
        abort_unless(auth()->user()->can('Role & Permission Management - Delete'), 403);
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
        abort_unless(auth()->user()->can('Role & Permission Management - Modify'), 403);
        $role = Role::findOrFail($id);

        // Define modules (currently used sections in the admin / exhibitor panels)
        $modules = [
            'admin_access' => 'Admin Access',
            'exhibitions' => 'Exhibition Management',
            'bookings' => 'Booking Management',
            'booths' => 'Booth Management',
            'floorplan' => 'Floorplan Management',
            'payments' => 'Payment Management',
            'financial' => 'Financial Management',
            'users' => 'User Management',
            'roles' => 'Role & Permission Management',
            'documents' => 'Document Management',
            'document_categories' => 'Document Category Management',
            'badges' => 'Badge Management',
            'sponsorships' => 'Sponsorship Management',
            'sponsorship_bookings' => 'Sponsorship Booking Management',
            'categories' => 'Category Management',
            'size_types' => 'Size Type Management',
            'cms_pages' => 'CMS Pages Management',
            'settings' => 'Settings Management',
            'booth_requests' => 'Booth Request Management',
            'discounts' => 'Discount Management',
            'checklists' => 'Checklist Management',
            'service_configuration' => 'Service Configuration Management',
            'analytics' => 'Analytics Management',
            'exhibitors' => 'Exhibitor Management',
            'communications' => 'Communication Management',
            'emails' => 'Email Management',
            'notifications' => 'Notification Management',
            'additional_service_requests' => 'Additional Service Request Management',
            'wallet' => 'Wallet Management',
            'messages' => 'Message Management',
            'services' => 'Additional Services Management',
            'sponsorship_payments' => 'Sponsorship Payment Management',
            'reports' => 'Report Generation',
        ];

        // Standard actions per module
        $actions = ['Create', 'View', 'Delete', 'Modify', 'Download'];

        // Ensure all permissions exist and group them by module & action
        $permissionsByModule = [];

        foreach ($modules as $moduleKey => $moduleLabel) {
            foreach ($actions as $action) {
                $permissionName = $moduleLabel . ' - ' . $action;

                $permission = Permission::firstOrCreate(
                    ['name' => $permissionName, 'guard_name' => 'web']
                );

                $permissionsByModule[$moduleKey][$action] = $permission;
            }
        }

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit-permissions', [
            'role' => $role,
            'modules' => $modules,
            'actions' => $actions,
            'permissionsByModule' => $permissionsByModule,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function updatePermissions(Request $request, $id)
    {
        abort_unless(auth()->user()->can('Role & Permission Management - Modify'), 403);
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
