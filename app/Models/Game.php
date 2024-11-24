<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'player1_id',
        'player2_id',
        'player1_deck',
        'player2_deck',
        'played_cards',
        'status',
    ];

    protected $casts = [
        'player1_deck' => 'array',
        'player2_deck' => 'array',
        'played_cards' => 'array',
    ];

    public function hasPlayer($userId): bool
    {
        return $this->player1_id === $userId || $this->player2_id === $userId;
    }
}
