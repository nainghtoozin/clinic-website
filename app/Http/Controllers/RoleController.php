<?php

namespace App\Http\Controllers;

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

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy(Role $role)
    {
        Gate::authorize('role.delete');
        $role->delete();
        return back()->with('success', 'Role deleted');
    }
}
