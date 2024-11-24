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
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('player1_id')->nullable()->change();
            $table->unsignedBigInteger('player2_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('player1_id')->nullable(false)->change();
            $table->unsignedBigInteger('player2_id')->nullable(false)->change();
        });
    }
};
