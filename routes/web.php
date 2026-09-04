<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LifeCounterController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProfileTypeController;
use App\Http\Controllers\TournamentController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/cadastro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/cadastro', [RegisteredUserController::class, 'store'])->middleware('throttle:registration');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login');
});

Route::middleware('auth')->group(function () {
    Route::get('/inicio', HomeController::class)->name('home.auth');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::patch('/perfil/tipo', [ProfileTypeController::class, 'update'])->middleware('throttle:writes')->name('profile.type');
    Route::get('/conta', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('/conta', [AccountController::class, 'update'])->middleware('throttle:writes')->name('account.update');
    Route::put('/conta/senha', [AccountController::class, 'updatePassword'])->middleware('throttle:writes')->name('account.password');

    Route::get('/torneios', [TournamentController::class, 'index'])->name('tournaments.index');
    Route::middleware('organizer')->group(function () {
        Route::get('/torneios/criar', [TournamentController::class, 'create'])->name('tournaments.create');
        Route::post('/torneios', [TournamentController::class, 'store'])->middleware('throttle:writes')->name('tournaments.store');
        Route::get('/torneios/{tournament}/editar', [TournamentController::class, 'edit'])->name('tournaments.edit');
        Route::put('/torneios/{tournament}', [TournamentController::class, 'update'])->middleware('throttle:writes')->name('tournaments.update');
        Route::patch('/torneios/{tournament}/cancelar', [TournamentController::class, 'cancel'])->middleware('throttle:writes')->name('tournaments.cancel');
    });
    Route::get('/torneios/{tournament}', [TournamentController::class, 'show'])->name('tournaments.show');
    Route::post('/torneios/{tournament}/inscrever', [TournamentController::class, 'register'])->middleware('throttle:writes')->name('tournaments.register');
    Route::delete('/torneios/{tournament}/inscricao', [TournamentController::class, 'unregister'])->middleware('throttle:writes')->name('tournaments.unregister');

    Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
    Route::get('/marketplace/vender', [MarketplaceController::class, 'create'])->name('marketplace.create');
    Route::post('/marketplace', [MarketplaceController::class, 'store'])->middleware('throttle:writes')->name('marketplace.store');
    Route::get('/marketplace/{cardListing}/editar', [MarketplaceController::class, 'edit'])->name('marketplace.edit');
    Route::put('/marketplace/{cardListing}', [MarketplaceController::class, 'update'])->middleware('throttle:writes')->name('marketplace.update');
    Route::delete('/marketplace/{cardListing}', [MarketplaceController::class, 'destroy'])->middleware('throttle:writes')->name('marketplace.destroy');
    Route::get('/marketplace/{cardListing}', [MarketplaceController::class, 'show'])->name('marketplace.show');

    Route::get('/comunidade', [CommunityController::class, 'index'])->name('community');
    Route::get('/comunidade/novo', [CommunityController::class, 'create'])->name('community.create');
    Route::post('/comunidade', [CommunityController::class, 'store'])->middleware('throttle:writes')->name('community.store');
    Route::get('/comunidade/{topic}/editar', [CommunityController::class, 'edit'])->name('community.edit');
    Route::put('/comunidade/{topic}', [CommunityController::class, 'update'])->middleware('throttle:writes')->name('community.update');
    Route::delete('/comunidade/{topic}', [CommunityController::class, 'destroy'])->middleware('throttle:writes')->name('community.destroy');
    Route::post('/comunidade/{topic}/reacoes', [CommunityController::class, 'react'])->middleware('throttle:writes')->name('community.react');
    Route::get('/comunidade/{topic}', [CommunityController::class, 'show'])->name('community.show');
    Route::post('/comunidade/{topic}/comentarios', [CommunityController::class, 'comment'])->middleware('throttle:writes')->name('community.comment');
    Route::get('/comunidade/comentarios/{comment}/editar', [CommunityController::class, 'editComment'])->name('community.comments.edit');
    Route::put('/comunidade/comentarios/{comment}', [CommunityController::class, 'updateComment'])->middleware('throttle:writes')->name('community.comments.update');
    Route::delete('/comunidade/comentarios/{comment}', [CommunityController::class, 'destroyComment'])->middleware('throttle:writes')->name('community.comments.destroy');
    Route::get('/contador-de-vida', LifeCounterController::class)->name('life-counter');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::patch('/usuarios/{user}', [AdminController::class, 'updateUser'])->middleware('throttle:writes')->name('users.update');
        Route::patch('/torneios/{tournament}', [AdminController::class, 'updateTournament'])->middleware('throttle:writes')->name('tournaments.update');
        Route::delete('/anuncios/{cardListing}', [AdminController::class, 'destroyListing'])->middleware('throttle:writes')->name('listings.destroy');
        Route::patch('/topicos/{topic}', [AdminController::class, 'updateTopic'])->middleware('throttle:writes')->name('topics.update');
        Route::delete('/topicos/{topic}', [AdminController::class, 'destroyTopic'])->middleware('throttle:writes')->name('topics.destroy');
    });
});
