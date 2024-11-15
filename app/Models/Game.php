<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = ['player1_id', 'player2_id', 'status'];

    public function cards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function hasPlayer($userId): bool
    {
        return $this->player1_id === $userId || $this->player2_id === $userId;
    }
}
