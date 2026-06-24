<?php

namespace App\Policies;

use App\Models\AdminUser;
use App\Models\AuditLog;

class AuditLogPolicy
{
    public function viewAny(AdminUser $admin): bool
    {
        return $admin->is_active && in_array($admin->role, ['super_admin', 'admin', 'operations'], true);
    }

    public function view(AdminUser $admin, AuditLog $auditLog): bool
    {
        return $this->viewAny($admin);
    }
}
