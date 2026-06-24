<?php

namespace App\Policies;

use App\Models\AdminUser;

class AdminUserPolicy
{
    public function viewAny(AdminUser $admin): bool
    {
        return $admin->is_active && in_array($admin->role, ['super_admin', 'admin'], true);
    }

    public function view(AdminUser $admin, AdminUser $target): bool
    {
        return $this->viewAny($admin);
    }

    public function create(AdminUser $admin): bool
    {
        return $admin->is_active && $admin->role === 'super_admin';
    }

    public function update(AdminUser $admin, AdminUser $target): bool
    {
        return $admin->is_active && $admin->role === 'super_admin';
    }

    public function delete(AdminUser $admin, AdminUser $target): bool
    {
        return $admin->is_active
            && $admin->role === 'super_admin'
            && $target->id !== $admin->id;
    }
}
