<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('game:reset', function () {
    \App\Models\Game::truncate();
    $this->info('Toutes les parties ont été réinitialisées.');
});

Artisan::command('game:list', function () {
    $games = \App\Models\Game::where('status', '!=', 'finished')->get();
    $this->info("Parties en cours : " . $games->count());
    $this->table(['ID', 'Player 1', 'Player 2', 'Status'], $games->toArray());
});
