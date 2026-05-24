<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Business;

class EnsureTenant
{
    /**
     * Handle an incoming request.
     *
     * Se espera un header X-Business-Id. Si no existe, se toma el primer negocio
     * del usuario autenticado (si tiene). Valida que el usuario pertenezca al negocio.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $businessId = $request->header('X-Business-Id');

        // If the route has a bound business (route-model binding), prefer it
        $routeBusiness = $request->route('business');
        if ($routeBusiness && $routeBusiness instanceof Business) {
            $business = $routeBusiness;
        } elseif ($businessId) {
            $business = Business::find($businessId);
        } else {
            // fallback: primer negocio del usuario
            $business = $user->businesses()->orderBy('id')->first();
        }

        if (!$business) {
            return response()->json(['message' => 'Business not found or not available.'], 404);
        }

        // Validar que el usuario pertenezca al negocio
        $belongs = $user->businesses()->where('business_id', $business->id)->exists();

        if (!$belongs) {
            // If the route explicitly pointed to another business, return 403
            return response()->json(['message' => 'No autorizado para este negocio.'], 403);
        }

        // Hacer disponible el negocio en el request y contenedor
        $request->attributes->set('business', $business);
        app()->instance('current_business', $business);

        return $next($request);
    }
}
