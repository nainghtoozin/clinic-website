<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // GET /api/roles
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => Role::all()
        ]);
    }

    // POST /api/roles
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name'
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    // GET /api/roles/{id}
    public function show($id)
    {
        $role = Role::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }

    // PUT /api/roles/{id}
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id
        ]);

        $role->update([
            'name' => $request->name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $role
        ]);
    }

    // DELETE /api/roles/{id}
    public function destroy($id)
    {
        Role::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully'
        ]);
    }
}
