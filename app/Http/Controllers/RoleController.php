<?php

namespace App\Http\Controllers;

use App\Services\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const GUARD = 'sanctum';

    private const SUPER_ADMIN_ROLE = 'Super-Admin';

    public function __construct()
    {
        $this->middleware('permission:view roles')->only(['index', 'show']);
        $this->middleware('permission:create roles')->only(['create', 'store']);
        $this->middleware('permission:edit roles')->only(['edit', 'update']);
    }

    public function index()
    {
        $roles = Role::where('guard_name', self::GUARD)->withCount('permissions')->orderBy('name')->get();
        $userCounts = DB::table('model_has_roles')
            ->select('role_id', DB::raw('count(*) as total'))
            ->groupBy('role_id')
            ->pluck('total', 'role_id');

        return view('roles.index', compact('roles', 'userCounts'));
    }

    public function create()
    {
        return view('roles.form', $this->formData(new Role));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        DB::transaction(function () use ($data) {
            $role = Role::create(['name' => $data['name'], 'guard_name' => self::GUARD]);
            $role->syncPermissions($this->permissions($data['permissions'] ?? []));
        });

        return to_route('roles.index')->with('success', 'Role created successfully.');
    }

    public function show(Role $role): RedirectResponse
    {
        return to_route('roles.edit', $role);
    }

    public function edit(Role $role)
    {
        $this->authorizeRole($role);

        return view('roles.form', $this->formData($role));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorizeRole($role);

        if ($role->name === self::SUPER_ADMIN_ROLE && $request->input('name') !== self::SUPER_ADMIN_ROLE) {
            return back()->withInput()->withErrors(['name' => 'The Super-Admin role cannot be renamed.']);
        }

        $data = $request->validate($this->rules($role));

        DB::transaction(function () use ($data, $role) {
            $role->update(['name' => $data['name']]);

            if ($role->name !== self::SUPER_ADMIN_ROLE) {
                $role->syncPermissions($this->permissions($data['permissions'] ?? []));
            }
        });

        return to_route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole(self::SUPER_ADMIN_ROLE), 403, 'Only a Super-Admin can delete roles.');

        $this->authorizeRole($role);

        if ($role->name === self::SUPER_ADMIN_ROLE) {
            return back()->with('error', 'The Super-Admin role cannot be deleted.');
        }

        if (DB::table('model_has_roles')->where('role_id', $role->id)->exists()) {
            return back()->with('error', 'This role is assigned to users. Move those users to another role first.');
        }

        $role->delete();

        return to_route('roles.index')->with('success', 'Role deleted. Permissions were not touched.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Role $role = null): array
    {
        $unique = Rule::unique('roles', 'name')->where('guard_name', self::GUARD);

        return [
            'name' => ['required', 'string', 'max:255', $role ? $unique->ignore($role->id) : $unique],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')->where('guard_name', self::GUARD)],
        ];
    }

    /**
     * @param  array<int, int|string>  $ids
     * @return \Illuminate\Support\Collection<int, Permission>
     */
    private function permissions(array $ids)
    {
        return Permission::whereIn('id', $ids)->where('guard_name', self::GUARD)->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Role $role): array
    {
        return [
            'role' => $role,
            'catalog' => PermissionCatalog::build(Permission::where('guard_name', self::GUARD)->get()),
            'checked' => array_map('intval', old('permissions', $role->exists ? $role->permissions->pluck('id')->all() : [])),
        ];
    }

    private function authorizeRole(Role $role): void
    {
        abort_if($role->guard_name !== self::GUARD, 404);
        abort_if(
            $role->name === self::SUPER_ADMIN_ROLE && ! auth()->user()->hasRole(self::SUPER_ADMIN_ROLE),
            403,
            'Only a Super-Admin can manage the Super-Admin role.'
        );
    }
}
