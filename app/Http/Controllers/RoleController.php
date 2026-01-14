<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        // VIEW
        $this->middleware('permission:role.view')
            ->only(['index', 'show']);

        // CREATE
        $this->middleware('permission:role.create')
            ->only(['create', 'store']);

        // EDIT
        $this->middleware('permission:role.edit')
            ->only(['edit', 'update']);

        // DELETE
        $this->middleware('permission:role.delete')
            ->only(['destroy']);
    }
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
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

    // 🔥 EDIT
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.edit', compact(
            'role',
            'permissions',
            'rolePermissions'
        ));
    }

    // 🔥 UPDATE
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array'
        ]);

        $role->update([
            'name' => $request->name
        ]);

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return back()->with('success', 'Role deleted');
    }
}
