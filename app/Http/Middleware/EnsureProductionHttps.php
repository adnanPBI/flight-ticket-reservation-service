<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProductionHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production') && config('security.force_https', true) && ! $request->isSecure()) {
            return redirect()->secure($request->getRequestUri(), Response::HTTP_MOVED_PERMANENTLY);
        }

        return $next($request);
    }
}
