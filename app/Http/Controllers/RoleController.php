<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
        ]);

        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permissions'));

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');

    }
    public function edit(Role $role)
    {
        $permissions = Permission::all();

        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->name = $request->input('name');
        $role->save();

        $permissions = $request->input('permissions', []);

        // Get the current permissions directly assigned to the role
        $currentPermissions = $role->permissions()->pluck('id')->toArray();

        // Detach permissions that are no longer selected
        $permissionsToDetach = array_diff($currentPermissions, $permissions);
        $role->permissions()->detach($permissionsToDetach);

        // Attach new permissions
        $permissionsToAttach = array_diff($permissions, $currentPermissions);
        $role->permissions()->attach($permissionsToAttach);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        // Retrieve the related permissions
        $permissions = $role->permissions;

        // Delete the role
        $role->delete();

        // Delete the related permissions
        foreach ($permissions as $permission) {
            $permission->delete();
        }

        return redirect()->route('roles.index')->with('success', 'Role and related permissions deleted successfully.');
    }
}
