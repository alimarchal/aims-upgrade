<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Services\PermissionCatalog;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const ROLE_GUARD = 'sanctum';

    private const SUPER_ADMIN_ROLE = 'Super-Admin';

    private const FRONT_DESK_ROLE = 'Front Desk/Receptionist';

    public function __construct()
    {
        $this->middleware('permission:view users')->only(['index', 'show']);
        $this->middleware('permission:create users')->only(['create', 'store']);
        $this->middleware('permission:edit users')->only(['edit', 'update']);
    }

    public function index(Request $request)
    {
        $filters = (array) $request->input('filter', []);

        $query = User::with('roles', 'permissions', 'department');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $term = '%'.mb_strtolower($search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])->orWhereRaw('LOWER(email) LIKE ?', [$term]);
            });
        }

        if (! empty($filters['role'])) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $filters['role']));
        }

        if (($filters['department_id'] ?? '') !== '') {
            $query->where('department_id', $filters['department_id']);
        }

        $statsQuery = clone $query;
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('status', true)->count(),
            'inactive' => (clone $statsQuery)->where('status', false)->count(),
            'no_role' => (clone $statsQuery)->doesntHave('roles')->count(),
        ];

        $tab = (string) ($filters['status'] ?? '');
        if ($tab === '1' || $tab === '0') {
            $query->where('status', $tab === '1');
        } elseif ($tab === 'no_role') {
            $query->doesntHave('roles');
        }

        $users = $query->orderBy('id')->paginate(25)->withQueryString();
        $roles = Role::where('guard_name', self::ROLE_GUARD)->orderBy('name')->pluck('name');
        $departments = Department::orderBy('name')->pluck('name', 'id');

        return view('users.index', compact('users', 'roles', 'departments', 'stats', 'filters', 'tab'));
    }

    public function create()
    {
        return view('users.form', $this->formData(new User(['status' => true])));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        if ($error = $this->superAdminRoleError($data['roles'])) {
            return back()->withInput()->withErrors(['roles' => $error]);
        }

        $user = DB::transaction(function () use ($data, $request) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'department_id' => $data['department_id'] ?? null,
                'status' => $request->boolean('status'),
            ]);

            $roles = Role::whereIn('id', $data['roles'])->where('guard_name', self::ROLE_GUARD)->get();
            $user->syncRoles($roles);

            $permissionIds = $this->assignablePermissionIds($data['permissions'] ?? []);

            // Front Desk users get the dashboard as a direct permission so it can be revoked per user
            if ($roles->contains('name', self::FRONT_DESK_ROLE)) {
                $dashboard = Permission::where('name', 'view dashboard')->where('guard_name', self::ROLE_GUARD)->value('id');
                if ($dashboard && ! in_array($dashboard, $permissionIds, true) && $this->actor()->can('view dashboard')) {
                    $permissionIds[] = $dashboard;
                }
            }

            $user->syncPermissions(Permission::whereIn('id', $permissionIds)->where('guard_name', self::ROLE_GUARD)->get());

            return $user;
        });

        return to_route('users.index')->with('status', "User {$user->name} has been added.");
    }

    public function show(User $user)
    {
        $user->load('roles.permissions', 'permissions', 'department');

        return view('users.show', [
            'user' => $user,
            'catalog' => PermissionCatalog::build($this->guardPermissions()),
            'direct' => $user->permissions->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'inherited' => $user->getPermissionsViaRoles()->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ]);
    }

    public function edit(User $user)
    {
        $this->authorizeTarget($user);

        return view('users.form', $this->formData($user));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeTarget($user);

        $data = $request->validate($this->rules($user));

        if ($user->id === $this->actor()->id && ! $request->boolean('status')) {
            return back()->withInput()->withErrors(['status' => 'You cannot deactivate your own account.']);
        }

        if ($error = $this->superAdminRoleError($data['roles'], $user)) {
            return back()->withInput()->withErrors(['roles' => $error]);
        }

        DB::transaction(function () use ($data, $request, $user) {
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'department_id' => $data['department_id'] ?? null,
                'status' => $request->boolean('status'),
            ]);

            if (! empty($data['password'])) {
                $user->update(['password' => Hash::make($data['password'])]);
            }

            $user->syncRoles(Role::whereIn('id', $data['roles'])->where('guard_name', self::ROLE_GUARD)->get());

            $keep = $this->actor()->hasRole(self::SUPER_ADMIN_ROLE)
                ? []
                : $user->permissions
                    ->filter(fn (Permission $permission) => ! $this->actor()->can($permission->name))
                    ->pluck('id')
                    ->all();

            $ids = array_values(array_unique(array_merge($this->assignablePermissionIds($data['permissions'] ?? []), $keep)));
            $user->syncPermissions(Permission::whereIn('id', $ids)->where('guard_name', self::ROLE_GUARD)->get());
        });

        return to_route('users.index')->with('status', "User {$user->name} has been updated.");
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($this->actor()->hasRole(self::SUPER_ADMIN_ROLE), 403, 'Only a Super-Admin can delete users.');

        $this->authorizeTarget($user);

        if ($user->id === $this->actor()->id) {
            return to_route('users.index')->with('error', 'You cannot delete your own account.');
        }

        if ($user->id === 1) {
            return to_route('users.index')->with('error', 'You cannot delete the primary administrator.');
        }

        if ($user->hasRole(self::SUPER_ADMIN_ROLE) && $this->superAdminCount() <= 1) {
            return to_route('users.index')->with('error', 'Cannot delete the last Super-Admin.');
        }

        try {
            $user->delete();
        } catch (QueryException) {
            return to_route('users.index')->with('error', 'This user has records linked to them and cannot be deleted. Mark the user Inactive instead.');
        }

        return to_route('users.index')->with('status', 'User has been deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?User $user = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', $user ? Rule::unique('users')->ignore($user->id) : Rule::unique('users')],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'status' => ['required', 'boolean'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')->where('guard_name', self::ROLE_GUARD)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')->where('guard_name', self::ROLE_GUARD)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(User $user): array
    {
        $user->loadMissing('roles.permissions', 'permissions');
        $actor = $this->actor();
        $permissions = $this->guardPermissions();

        $roles = Role::where('guard_name', self::ROLE_GUARD)
            ->with('permissions:id')
            ->when(! $actor->hasRole(self::SUPER_ADMIN_ROLE), fn ($q) => $q->where('name', '!=', self::SUPER_ADMIN_ROLE))
            ->orderBy('name')
            ->get();

        return [
            'user' => $user,
            'roles' => $roles,
            'departments' => Department::orderBy('name')->pluck('name', 'id'),
            'catalog' => PermissionCatalog::build($permissions),
            'selectedRoles' => array_map('intval', old('roles', $user->exists ? $user->roles->pluck('id')->all() : [])),
            'selectedPermissions' => array_map('intval', old('permissions', $user->exists ? $user->permissions->pluck('id')->all() : [])),
            'inherited' => $user->exists ? $user->getPermissionsViaRoles()->pluck('id')->map(fn ($id) => (int) $id)->all() : [],
            'locked' => $actor->hasRole(self::SUPER_ADMIN_ROLE)
                ? []
                : $permissions->reject(fn (Permission $permission) => $actor->can($permission->name))->pluck('id')->all(),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, Permission>
     */
    private function guardPermissions()
    {
        return Permission::where('guard_name', self::ROLE_GUARD)->orderBy('name')->get();
    }

    /**
     * Keep only permissions the signed-in editor may hand out (Super-Admin: all).
     *
     * @param  array<int, int|string>  $ids
     * @return array<int, int>
     */
    private function assignablePermissionIds(array $ids): array
    {
        $actor = $this->actor();

        return Permission::whereIn('id', $ids)
            ->where('guard_name', self::ROLE_GUARD)
            ->get()
            ->filter(fn (Permission $permission) => $actor->hasRole(self::SUPER_ADMIN_ROLE) || $actor->can($permission->name))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function actor(): User
    {
        return auth()->user();
    }

    private function authorizeTarget(User $user): void
    {
        abort_if(
            $user->hasRole(self::SUPER_ADMIN_ROLE) && ! $this->actor()->hasRole(self::SUPER_ADMIN_ROLE),
            403,
            'Only a Super-Admin can manage a Super-Admin account.'
        );
    }

    /**
     * @param  array<int, int|string>  $roleIds
     */
    private function superAdminRoleError(array $roleIds, ?User $target = null): ?string
    {
        $superAdmin = Role::where('name', self::SUPER_ADMIN_ROLE)->where('guard_name', self::ROLE_GUARD)->first();

        if (! $superAdmin) {
            return null;
        }

        $selected = in_array($superAdmin->id, array_map('intval', $roleIds), true);

        if ($selected && ! $this->actor()->hasRole(self::SUPER_ADMIN_ROLE)) {
            return 'Only a Super-Admin can assign the Super-Admin role.';
        }

        if ($target && ! $selected && $target->hasRole(self::SUPER_ADMIN_ROLE) && $this->superAdminCount() <= 1) {
            return 'You cannot remove the Super-Admin role from the last Super-Admin.';
        }

        return null;
    }

    private function superAdminCount(): int
    {
        return User::role(self::SUPER_ADMIN_ROLE, self::ROLE_GUARD)->count();
    }
}
