<?php

namespace App\Support;

use App\Models\RolePermission;
use App\Models\User;

class Permission
{
    protected static array $cache = [];

    public static function can(?User $user, string $menu, string $action = 'read'): bool
    {
        if (!$user) {
            return false;
        }
        if ($user->role === 'admin') {
            return true;
        }

        $role = $user->role;
        if (in_array($role, ['petugas', 'staff'], true)) {
            $role = 'petugas_it';
        }

        if (!isset(self::$cache[$role])) {
            self::$cache[$role] = RolePermission::where('role', $role)->get()->keyBy('menu');
        }

        $perm = self::$cache[$role][$menu] ?? null;
        $field = 'can_' . $action;

        return (bool) ($perm->{$field} ?? false);
    }
}
