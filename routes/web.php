<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Lobby', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/game/{id}', [GameController::class, 'show'])->name('game.show');
    Route::post('/lobby/create', [LobbyController::class, 'createGame']);
    Route::post('/lobby/join', [LobbyController::class, 'joinGame']);
    Route::post('/game/play', [GameController::class, 'playCard']);
    Route::get('/game/status/{id}', [GameController::class, 'status']);
    Route::post('/game/leave', [GameController::class, 'leaveGame']);
    Route::post('/game/end/{id}', [GameController::class, 'endGame']);
});

require __DIR__ . '/auth.php';
