<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Vérifie que l'utilisateur a un des rôles requis
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  Les rôles autorisés (super_gestionnaire, gestionnaire, etc.)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // 1. Si pas connecté → login
        if (!$user) {
            return redirect()->route('login');
        }

        // 2. Si compte inactif
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Votre compte est désactivé.'
            ]);
        }

        // 3. Vérifier le rôle
        if (!in_array($user->user_type, $roles)) {
            abort(403, 'Accès non autorisé pour votre rôle.');
        }

        // 4. Mettre à jour la dernière connexion
        $user->update(['last_login_at' => now()]);

        return $next($request);
    }
}