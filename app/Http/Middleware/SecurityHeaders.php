<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! config('security.headers.enabled', true)) {
            return $response;
        }

        $headers = config('security.headers', []);

        $response->headers->set('X-Frame-Options', $headers['frame_options'] ?? 'DENY');
        $response->headers->set('X-Content-Type-Options', $headers['content_type_options'] ?? 'nosniff');
        $response->headers->set('Referrer-Policy', $headers['referrer_policy'] ?? 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', $headers['permissions_policy'] ?? 'camera=(), microphone=(), geolocation=(), payment=(self)');

        if (config('security.headers.csp_enabled', true)) {
            $csp = (string) config('security.headers.csp');

            if (file_exists(public_path('hot'))) {
                $viteUrl = trim((string) file_get_contents(public_path('hot')));

                foreach (['script-src ', 'style-src ', 'connect-src ', 'font-src '] as $directive) {
                    $csp = str_replace($directive, "{$directive}{$viteUrl} ", $csp);
                }
            }

            $response->headers->set('Content-Security-Policy', $csp);
        }

        if ($request->isSecure() && config('security.headers.hsts_enabled', true)) {
            $response->headers->set('Strict-Transport-Security', 'max-age=' . (int) config('security.headers.hsts_max_age', 31536000) . '; includeSubDomains');
        }

        return $response;
    }
}
