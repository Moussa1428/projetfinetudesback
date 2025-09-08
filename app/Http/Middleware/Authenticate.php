<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        // Pour l'API, ne pas rediriger
        if ($request->is('api/*') || $request->expectsJson()) {
            return null;
        }

        return route('login');
    }

    protected function unauthenticated($request, array $guards)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            abort(response()->json([
                'message' => 'Pas accès à se connecter. Token requis.'
            ], 401));
        }

        parent::unauthenticated($request, $guards);
    }
}
