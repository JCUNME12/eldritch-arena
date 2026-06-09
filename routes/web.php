<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LifeCounterController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\PremiumController;
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

    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
    Route::get('/marketplace/vender', [MarketplaceController::class, 'create'])->name('marketplace.create');
    Route::post('/marketplace', [MarketplaceController::class, 'store'])->name('marketplace.store');
    Route::get('/marketplace/{cardListing}', [MarketplaceController::class, 'show'])->name('marketplace.show');

    Route::get('/comunidade', [CommunityController::class, 'index'])->name('community');
    Route::get('/comunidade/novo', [CommunityController::class, 'create'])->name('community.create');
    Route::post('/comunidade', [CommunityController::class, 'store'])->name('community.store');
    Route::get('/comunidade/{topic}/editar', [CommunityController::class, 'edit'])->name('community.edit');
    Route::put('/comunidade/{topic}', [CommunityController::class, 'update'])->name('community.update');
    Route::delete('/comunidade/{topic}', [CommunityController::class, 'destroy'])->name('community.destroy');
    Route::post('/comunidade/{topic}/reacoes', [CommunityController::class, 'react'])->name('community.react');
    Route::get('/comunidade/{topic}', [CommunityController::class, 'show'])->name('community.show');
    Route::post('/comunidade/{topic}/comentarios', [CommunityController::class, 'comment'])->name('community.comment');
    Route::get('/comunidade/comentarios/{comment}/editar', [CommunityController::class, 'editComment'])->name('community.comments.edit');
    Route::put('/comunidade/comentarios/{comment}', [CommunityController::class, 'updateComment'])->name('community.comments.update');
    Route::delete('/comunidade/comentarios/{comment}', [CommunityController::class, 'destroyComment'])->name('community.comments.destroy');
    Route::get('/premium', [PremiumController::class, 'index'])->name('premium');
    Route::post('/premium/assinar', [PremiumController::class, 'subscribe'])->name('premium.subscribe');

    Route::get('/contador-de-vida', LifeCounterController::class)->name('life-counter');
});
