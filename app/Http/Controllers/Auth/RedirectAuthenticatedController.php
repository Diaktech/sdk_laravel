<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RedirectAuthenticatedController extends Controller
{
    public function home(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Redirection selon le rôle
        switch ($user->user_type) {
            case 'super_gestionnaire':
                return redirect()->route('super.dashboard');
            
            case 'gestionnaire':
                return redirect()->route('manager.dashboard');
            
            case 'collecteur':
                return redirect()->route('collector.dashboard');
            
            case 'livreur':
                return redirect()->route('delivery.dashboard');
            
            case 'client':
                return redirect()->route('client.dashboard');
            
            default:
                return redirect()->route('dashboard'); // Dashboard Breeze par défaut
        }
    }
}