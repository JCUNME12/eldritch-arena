<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LifeCounterController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProfileTypeController;
use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/cadastro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/cadastro', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::patch('/perfil/tipo', [ProfileTypeController::class, 'update'])->name('profile.type');

    Route::get('/torneios', [TournamentController::class, 'index'])->name('tournaments.index');
    Route::get('/torneios/criar', [TournamentController::class, 'create'])->name('tournaments.create');
    Route::post('/torneios', [TournamentController::class, 'store'])->name('tournaments.store');
    Route::get('/torneios/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');
    Route::post('/torneios/{tournament}/inscrever', [TournamentController::class, 'register'])->name('tournaments.register');

    Route::get('/marketplace', MarketplaceController::class)->name('marketplace');
    Route::get('/contador-de-vida', LifeCounterController::class)->name('life-counter');
});
