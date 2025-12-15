<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ==================== DASHBOARD PRINCIPAL (REDIRECTION) ====================
Route::get('/dashboard', function (Request $request) {
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
            return view('dashboard'); // Vue Breeze par défaut
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// ==================== ROUTES TEST POUR LES RÔLES ====================
Route::get('/admin/test', function () {
    return 'Accès Super Admin autorisé !';
})->middleware(['auth', 'role:super_gestionnaire']);

Route::get('/manager/test', function () {
    return 'Accès Manager autorisé !';
})->middleware(['auth', 'role:super_gestionnaire,gestionnaire']);

Route::get('/collecteur/test', function () {
    return 'Accès Collecteur autorisé !';
})->middleware(['auth', 'role:collecteur']);

Route::get('/livreur/test', function () {
    return 'Accès Livreur autorisé !';
})->middleware(['auth', 'role:livreur']);

Route::get('/client/test', function () {
    return 'Accès Client autorisé !';
})->middleware(['auth', 'role:client']);

// ==================== DASHBOARDS SPÉCIFIQUES ====================
Route::get('/super/dashboard', function () {
    return view('dashboards.super');
})->middleware(['auth', 'role:super_gestionnaire'])->name('super.dashboard');

Route::get('/manager/dashboard', function () {
    return view('dashboards.manager');
})->middleware(['auth', 'role:super_gestionnaire,gestionnaire'])->name('manager.dashboard');

Route::get('/collector/dashboard', function () {
    return view('dashboards.collector');
})->middleware(['auth', 'role:collecteur'])->name('collector.dashboard');

Route::get('/delivery/dashboard', function () {
    return view('dashboards.delivery');
})->middleware(['auth', 'role:livreur'])->name('delivery.dashboard');

Route::get('/client/dashboard', function () {
    return view('dashboards.client');
})->middleware(['auth', 'role:client'])->name('client.dashboard');

// ==================== ROUTES PROFIL (BREEZE) ====================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';