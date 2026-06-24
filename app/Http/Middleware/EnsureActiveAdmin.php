<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAdmin
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $admin = auth('admin')->user();

        abort_unless($admin instanceof AdminUser && $admin->is_active, 403, 'Inactive or missing admin account.');

        if ($roles !== []) {
            abort_unless(in_array($admin->role, $roles, true), 403, 'This admin role cannot perform this action.');
        }

        return $next($request);
    }
}
