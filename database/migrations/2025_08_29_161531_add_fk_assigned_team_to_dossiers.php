<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ne tente la FK que si les 2 tables existent
        if (Schema::hasTable('teams') && Schema::hasTable('dossiers_raccordement')) {
            Schema::table('dossiers_raccordement', function (Blueprint $table) {
                if (! Schema::hasColumn('dossiers_raccordement','assigned_team_id')) {
                    $table->foreignId('assigned_team_id')->nullable()->index();
                }
                // Ajoute la contrainte si pas déjà là
                $table->foreign('assigned_team_id')
                      ->references('id')->on('teams')
                      ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('dossiers_raccordement', function (Blueprint $table) {
            // Supprime proprement la FK et l’index
            if (Schema::hasColumn('dossiers_raccordement','assigned_team_id')) {
                try { $table->dropForeign(['assigned_team_id']); } catch (\Throwable $e) {}
                $table->dropColumn('assigned_team_id');
            }
        });
    }
};
