<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('games'); // Supprime la table si elle existe déjà

        Schema::create('games', function (Blueprint $table) {
            $table->id(); // Clé primaire auto-incrémentée
            $table->string('player1_id'); // ID ou pseudo du joueur 1
            $table->string('player2_id')->nullable(); // ID ou pseudo du joueur 2 (peut être NULL au début)
            $table->enum('status', ['waiting', 'ready', 'in_progress', 'finished'])->default('waiting'); // Statut du jeu
            $table->json('player1_deck')->nullable(); // Deck du joueur 1
            $table->json('player2_deck')->nullable(); // Deck du joueur 2
            $table->json('played_cards')->nullable(); // Cartes jouées
            $table->timestamps(); // Colonnes created_at et updated_at

            // Contraintes
            $table->index('player1_id'); // Index pour améliorer les performances
            $table->index('player2_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games'); // Supprime la table en cas de rollback
    }
};
