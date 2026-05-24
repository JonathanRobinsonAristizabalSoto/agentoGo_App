<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        // Manejo de preflight CORS
        if ($request->isMethod('OPTIONS')) {
            $resp = response('', 204);
        } else {
            $resp = $next($request);
        }

        // Encabezados de seguridad recomendados
        $resp->headers->set('X-Frame-Options', 'DENY');
        $resp->headers->set('X-Content-Type-Options', 'nosniff');
        $resp->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $resp->headers->set('X-XSS-Protection', '1; mode=block');
        // Content-Security-Policy mínimo para APIs
        $resp->headers->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'none';");

        // CORS básico configurable vía .env
        $allowedOrigin = env('API_CORS_ALLOWED', '*');
        $allowedMethods = env('API_CORS_METHODS', 'GET,POST,PUT,DELETE,OPTIONS');
        $allowedHeaders = env('API_CORS_HEADERS', 'Content-Type,Authorization');
        $allowCredentials = env('API_CORS_CREDENTIALS', 'false');

        $resp->headers->set('Access-Control-Allow-Origin', $allowedOrigin);
        $resp->headers->set('Access-Control-Allow-Methods', $allowedMethods);
        $resp->headers->set('Access-Control-Allow-Headers', $allowedHeaders);
        $resp->headers->set('Access-Control-Allow-Credentials', $allowCredentials);

        return $resp;
    }
}
