<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('User Management - View'), 403);
        $users = User::with('roles')->latest()->paginate(15);
        $roles = Role::where('status', 'active')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        abort_unless(auth()->user()->can('User Management - Create'), 403);
        $roles = Role::where('status', 'active')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('User Management - Create'), 403);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? null,
        ]);

        if ($request->filled('roles')) {
            $user->syncRoles($request->roles);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(string $id)
    {
        abort_unless(auth()->user()->can('User Management - Modify'), 403);
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::where('status', 'active')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, string $id)
    {
        abort_unless(auth()->user()->can('User Management - Modify'), 403);
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'roles' => 'array',
        ]);

        $user->update($validated);

        if ($request->filled('roles')) {
            $user->syncRoles($request->roles);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(string $id)
    {
        abort_unless(auth()->user()->can('User Management - Delete'), 403);
        $user = User::findOrFail($id);

        if ($user->hasRole('Admin')) {
            return back()->with('error', 'Cannot delete an Admin user.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        abort_unless(auth()->user()->can('User Management - Delete'), 403);
        $request->validate([
            'user_ids' => 'required|string',
        ]);

        $userIds = json_decode($request->user_ids);
        $users = User::whereIn('id', $userIds)->get();
        $deletedCount = 0;

        foreach ($users as $user) {
            if (!$user->hasRole('Admin')) {
                $user->delete();
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            return redirect()->route('admin.users.index')->with('success', $deletedCount . ' user(s) deleted successfully.');
        }

        return back()->with('error', 'No users could be deleted. Admin users cannot be deleted.');
    }
}

