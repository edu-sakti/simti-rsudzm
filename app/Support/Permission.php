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
        if (($user->is_admin ?? false)) {
            return true;
        }

        $roleId = $user->role_id;
        if (!$roleId) {
            return false;
        }

        if (!isset(self::$cache[$roleId])) {
            self::$cache[$roleId] = RolePermission::where('role_id', $roleId)->get()->keyBy('menu');
        }

        $perm = self::$cache[$roleId][$menu] ?? null;
        $field = 'can_' . $action;

        return (bool) ($perm->{$field} ?? false);
    }
}
