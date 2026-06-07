<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $user->loadMissing('roles');
        $userRoles = $user->roles->pluck('nom')->all();

        foreach ($roles as $role) {
            if (in_array($role, $userRoles, true)) {
                return $next($request);
            }
        }

        if ($request->header('X-Inertia')) {
            return back()->with('error', "Vous n'avez pas les droits pour accéder à cette page.");
        }

        abort(403, "Accès refusé.");
    }
}
