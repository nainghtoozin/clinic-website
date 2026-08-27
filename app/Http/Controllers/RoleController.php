<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        Gate::authorize('role.view');
        $roles = Role::with('permissions')->withCount('users')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        Gate::authorize('role.create');
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        Gate::authorize('role.create');

        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'nullable|array'
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        AuditService::logCreated($role, 'Role');

        return redirect()->route('roles.index')->with('success', 'Role created successfully');
    }

    public function show(Role $role)
    {
        Gate::authorize('role.view');

        $role->load('permissions');

        return view('roles.show', compact('role'));
    }

    public function edit(Role $role)
    {
        Gate::authorize('role.edit');
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        Gate::authorize('role.edit');

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array'
        ]);

        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $oldName = $role->name;

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        $newPermissions = $role->fresh()->permissions->pluck('name')->toArray();

        $addedPermissions = array_diff($newPermissions, $oldPermissions);
        $removedPermissions = array_diff($oldPermissions, $newPermissions);

        AuditService::log(
            'updated',
            'Role',
            $role,
            "Role \"{$oldName}\" permissions updated",
            ['permissions' => $oldPermissions, 'name' => $oldName],
            ['permissions' => $newPermissions, 'name' => $request->name],
            [
                'added_permissions' => array_values($addedPermissions),
                'removed_permissions' => array_values($removedPermissions),
            ]
        );

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy(Role $role)
    {
        Gate::authorize('role.delete');

        // Prevent deletion of super-admin role
        if ($role->name === 'super-admin') {
            return back()->with('error', 'The Super Admin role cannot be deleted.');
        }

        // Prevent deletion of role with assigned users
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Cannot delete a role that has users assigned to it. Reassign users first.');
        }

        $roleName = $role->name;
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        $role->delete();

        AuditService::log(
            'deleted',
            'Role',
            $role,
            "Role \"{$roleName}\" deleted",
            ['name' => $roleName, 'permissions' => $rolePermissions],
            null
        );

        return back()->with('success', 'Role deleted');
    }
}
