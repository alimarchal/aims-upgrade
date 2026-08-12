<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const ROLE_GUARD = 'sanctum';

    public function index(Request $request)
    {
        $query = User::with('roles', 'permissions', 'department');

        // Apply filters
        if ($request->filled('filter.name')) {
            $query->where('name', 'like', '%'.$request->input('filter.name').'%');
        }

        if ($request->filled('filter.email')) {
            $query->where('email', 'like', '%'.$request->input('filter.email').'%');
        }

        if ($request->filled('filter.role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->input('filter.role'));
            });
        }

        if ($request->filled('filter.status')) {
            $query->where('status', $request->input('filter.status'));
        }

        if ($request->filled('filter.department_id')) {
            $query->where('department_id', $request->input('filter.department_id'));
        }

        $users = $query->orderBy('id')->get();
        $roles = Role::where('guard_name', self::ROLE_GUARD)->orderBy('name')->pluck('name');
        $departments = Department::orderBy('name')->pluck('name', 'id');

        return view('users.index', compact('users', 'roles', 'departments'));
    }

    public function create()
    {
        $roles = Role::query()
            ->where('guard_name', self::ROLE_GUARD)
            ->orderBy('name')
            ->pluck('name', 'id');

        $departments = Department::orderBy('name')->pluck('name', 'id');

        return view('users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,id,guard_name,'.self::ROLE_GUARD,
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'required|boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'department_id' => $request->department_id,
            'status' => $request->boolean('status'),
        ]);

        $role = Role::findById($request->integer('role'), self::ROLE_GUARD);
        $user->assignRole($role);

        session()->flash('status', 'User has been successfully added into database.');

        return to_route('users.index');
    }

    public function show(User $user)
    {
        $user->load('roles', 'permissions', 'department');

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $user->load('roles', 'permissions');

        $roles = Role::query()
            ->where('guard_name', self::ROLE_GUARD)
            ->orderBy('name')
            ->pluck('name', 'id');

        $permissions = Permission::query()
            ->where('guard_name', self::ROLE_GUARD)
            ->orderBy('name')
            ->get();

        $departments = Department::orderBy('name')->pluck('name', 'id');

        $userPermissions = $user->getAllPermissions()->pluck('id')->toArray();
        $userRoleId = $user->roles->first()?->id;

        return view('users.edit', compact('user', 'roles', 'permissions', 'departments', 'userPermissions', 'userRoleId'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,id,guard_name,'.self::ROLE_GUARD,
            'department_id' => 'nullable|exists:departments,id',
            'status' => 'required|boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id,guard_name,'.self::ROLE_GUARD,
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'department_id' => $request->department_id,
            'status' => $request->boolean('status'),
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        // Sync role
        $role = Role::findById($request->integer('role'), self::ROLE_GUARD);
        $user->syncRoles([$role]);

        // Sync direct permissions
        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)
                ->where('guard_name', self::ROLE_GUARD)
                ->get();
            $user->syncPermissions($permissions);
        } else {
            $user->syncPermissions([]);
        }

        session()->flash('status', 'User has been successfully updated.');

        return to_route('users.index');
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');

            return to_route('users.index');
        }

        // Prevent deletion of super admin (user ID 1)
        if ($user->id === 1) {
            session()->flash('error', 'You cannot delete the primary administrator.');

            return to_route('users.index');
        }

        $user->delete();

        session()->flash('status', 'User has been successfully deleted.');

        return to_route('users.index');
    }
}
