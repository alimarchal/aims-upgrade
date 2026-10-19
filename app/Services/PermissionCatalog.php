<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

/**
 * Arranges the flat permission list ("view patients", "edit users" ...) into
 * module sections so an admin finds a permission under the same heading as
 * the menu. Unknown permissions still show up (under "Other").
 */
class PermissionCatalog
{
    /**
     * @var array<int, string>
     */
    private const ACTIONS = ['view', 'create', 'edit', 'delete', 'return', 'manage'];

    /**
     * @return array<int, array{label: string, groups: array<int, array{label: string, permissions: array<int, array{id: int, name: string, action: string}>}>}>
     */
    public static function build(Collection $permissions): array
    {
        $modules = [];

        foreach ($permissions->sortBy('name') as $permission) {
            [$action, $subject] = self::split($permission->name);
            $module = self::moduleFor($permission->name, $subject);

            $modules[$module][$subject][] = [
                'id' => (int) $permission->id,
                'name' => $permission->name,
                'action' => $action,
            ];
        }

        $order = array_flip(['Dashboard', 'Patients', 'Chits / OPD', 'Invoices', 'Admissions', 'Reports', 'Administration', 'Other']);
        uksort($modules, fn ($a, $b) => ($order[$a] ?? 50) <=> ($order[$b] ?? 50) ?: strcmp($a, $b));

        $result = [];
        foreach ($modules as $module => $groups) {
            $rows = [];
            foreach ($groups as $subject => $items) {
                $rank = array_flip(self::ACTIONS);
                usort($items, fn ($a, $b) => ($rank[$a['action']] ?? 99) <=> ($rank[$b['action']] ?? 99));
                $rows[] = ['label' => Str::headline($subject), 'permissions' => $items];
            }
            $result[] = ['label' => $module, 'groups' => $rows];
        }

        return $result;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function split(string $name): array
    {
        $first = Str::before($name, ' ');

        if (in_array($first, self::ACTIONS, true) && Str::contains($name, ' ')) {
            return [$first, Str::after($name, ' ')];
        }

        return ['access', $name];
    }

    private static function moduleFor(string $name, string $subject): string
    {
        return match (true) {
            str_contains($name, 'report') => 'Reports',
            str_contains($name, 'dashboard') => 'Dashboard',
            str_contains($subject, 'patient') => 'Patients',
            str_contains($subject, 'chit') => 'Chits / OPD',
            str_contains($subject, 'invoice') => 'Invoices',
            str_contains($subject, 'admission') => 'Admissions',
            in_array($subject, ['users', 'roles', 'permissions'], true) => 'Administration',
            default => 'Other',
        };
    }
}
