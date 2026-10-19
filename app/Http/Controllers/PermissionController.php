<?php

namespace App\Http\Controllers;

use App\Services\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    private const GUARD = 'sanctum';

    public function index()
    {
        $permissions = Permission::where('guard_name', self::GUARD)->get();

        $roleCounts = DB::table('role_has_permissions')
            ->select('permission_id', DB::raw('count(*) as total'))
            ->groupBy('permission_id')
            ->pluck('total', 'permission_id');

        $userCounts = DB::table('model_has_permissions')
            ->select('permission_id', DB::raw('count(*) as total'))
            ->groupBy('permission_id')
            ->pluck('total', 'permission_id');

        return view('permissions.index', [
            'catalog' => PermissionCatalog::build($permissions),
            'roleCounts' => $roleCounts,
            'userCounts' => $userCounts,
            'total' => $permissions->count(),
        ]);
    }

    public function create()
    {
        return view('permissions.form', ['permission' => new Permission]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());

        Permission::create(['name' => $data['name'], 'guard_name' => self::GUARD]);

        return to_route('permissions.index')->with('success', 'Permission created successfully.');
    }

    public function show(Permission $permission): RedirectResponse
    {
        return to_route('permissions.edit', $permission);
    }

    public function edit(Permission $permission)
    {
        abort_if($permission->guard_name !== self::GUARD, 404);

        return view('permissions.form', compact('permission'));
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        abort_if($permission->guard_name !== self::GUARD, 404);

        $data = $request->validate($this->rules($permission));

        $permission->update(['name' => $data['name']]);

        return to_route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('Super-Admin'), 403, 'Only a Super-Admin can delete permissions.');
        abort_if($permission->guard_name !== self::GUARD, 404);

        $inUse = DB::table('role_has_permissions')->where('permission_id', $permission->id)->exists()
            || DB::table('model_has_permissions')->where('permission_id', $permission->id)->exists();

        if ($inUse) {
            return back()->with('error', 'This permission is still given to a role or a user. Remove it from them first.');
        }

        $permission->delete();

        return to_route('permissions.index')->with('success', 'Permission deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Permission $permission = null): array
    {
        $unique = Rule::unique('permissions', 'name')->where('guard_name', self::GUARD);

        return [
            'name' => ['required', 'string', 'max:255', $permission ? $unique->ignore($permission->id) : $unique],
        ];
    }
}
