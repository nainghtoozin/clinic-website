<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('staff.view');

        $query = User::with(['roles', 'doctor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('position', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($rq) => $rq->where('name', $request->role));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->pluck('name', 'name');

        return view('staff.index', compact('users', 'roles'));
    }

    public function create()
    {
        Gate::authorize('staff.create');

        $roles = Role::orderBy('name')->pluck('name', 'name');
        $doctors = Doctor::whereNull('user_id')->orderBy('name')->pluck('name', 'id');

        return view('staff.create', compact('roles', 'doctors'));
    }

    public function store(Request $request)
    {
        Gate::authorize('staff.create');

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|exists:roles,name',
            'phone'    => 'nullable|string|max:50',
            'position' => 'nullable|string|max:100',
            'doctor_id' => 'nullable|exists:doctors,id',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone'    => $validated['phone'] ?? null,
            'position' => $validated['position'] ?? null,
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        if (!empty($validated['doctor_id'])) {
            Doctor::where('id', $validated['doctor_id'])->update(['user_id' => $user->id]);
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff member created successfully');
    }

    public function edit(User $staff)
    {
        Gate::authorize('staff.edit');

        $roles = Role::orderBy('name')->pluck('name', 'name');
        $userRole = $staff->roles->first()->name ?? '';
        $doctors = Doctor::whereNull('user_id')->orWhere('user_id', $staff->id)
            ->orderBy('name')->pluck('name', 'id');
        $linkedDoctor = $staff->doctor?->id;

        return view('staff.edit', compact('staff', 'roles', 'userRole', 'doctors', 'linkedDoctor'));
    }

    public function update(Request $request, User $staff)
    {
        Gate::authorize('staff.edit');

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $staff->id,
            'password' => 'nullable|min:6|confirmed',
            'role'     => 'required|exists:roles,name',
            'phone'    => 'nullable|string|max:50',
            'position' => 'nullable|string|max:100',
            'is_active' => 'required|boolean',
            'doctor_id' => 'nullable|exists:doctors,id',
        ]);

        $updateData = [
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
            'position'  => $validated['position'] ?? null,
            'is_active' => $validated['is_active'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $staff->update($updateData);
        $staff->syncRoles($validated['role']);

        if (!empty($validated['doctor_id'])) {
            Doctor::where('user_id', $staff->id)->update(['user_id' => null]);
            Doctor::where('id', $validated['doctor_id'])->update(['user_id' => $staff->id]);
        } elseif ($staff->doctor) {
            $staff->doctor->update(['user_id' => null]);
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff member updated successfully');
    }

    public function destroy(User $staff)
    {
        Gate::authorize('staff.delete');

        if ($staff->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account');
        }

        $staff->update(['is_active' => false]);

        return back()->with('success', 'Staff member deactivated');
    }

    public function toggleStatus(User $staff)
    {
        Gate::authorize('staff.edit');

        if ($staff->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own status');
        }

        $staff->update(['is_active' => !$staff->is_active]);

        $status = $staff->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Staff member {$status}");
    }
}
