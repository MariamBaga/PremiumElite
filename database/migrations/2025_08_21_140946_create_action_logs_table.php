<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();

            // Sujet audité (dossier, intervention, tentative, etc.)
            $table->morphs('subject'); // subject_type, subject_id

            // Dossier racine pour agréger l’historique (facilite les requêtes)
            $table->foreignId('dossier_id')->nullable()->constrained('dossiers_raccordement')->nullOnDelete();

            // Auteur de l’action (user)
            $table->foreignId('causer_id')->nullable()->constrained('users')->nullOnDelete();

            // Type d’action
            $table->string('action'); // created, updated, assigned, status_changed, contact_attempted, intervention_added, etc.

            // Données additionnelles (diffs, commentaires, payload…)
            $table->json('properties')->nullable();

            // Contexte
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->index(['dossier_id','action']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('action_logs');
    }
};
