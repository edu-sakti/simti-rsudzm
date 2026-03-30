<?php

namespace App\Support;

use App\Models\User;

class Permission
{
    public static function can(?User $user, string $menu, string $action = 'read'): bool
    {
        // Semua user yang login diizinkan akses semua menu/action.
        return (bool) $user;
    }
}
