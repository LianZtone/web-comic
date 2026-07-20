<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Permissions-Policy', 'camera=(), geolocation=(), microphone=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        if (! app()->isLocal()) {
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'",
                "img-src 'self' data: https:",
                "object-src 'none'",
                "script-src 'self' 'unsafe-inline'",
                "style-src 'self' 'unsafe-inline'",
                "font-src 'self' data: https:",
                "connect-src 'self'",
            ]));
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
