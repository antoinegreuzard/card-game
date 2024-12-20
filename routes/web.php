<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Lobby'); // Utilisez le composant Lobby
})->name('lobby'); // Ajoutez un nom à cette route

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Routes pour le jeu
Route::get('/game/{id}', [GameController::class, 'show'])->name('game.show');
Route::get('/game/{id}/state', [GameController::class, 'getGameState'])->name('game.state'); // Ajout de la route manquante
Route::get('/game/status/{id}', [GameController::class, 'status'])->name('game.status');
Route::post('/game/play', [GameController::class, 'playCard']);
Route::post('/game/leave', [GameController::class, 'leaveGame']);
Route::post('/game/end/{id}', [GameController::class, 'endGame']);

// Routes pour le lobby
Route::post('/lobby/create', [LobbyController::class, 'createGame']);
Route::post('/lobby/join', [LobbyController::class, 'joinGame']);

require __DIR__ . '/auth.php';
