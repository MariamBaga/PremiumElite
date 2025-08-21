<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers_raccordement')->cascadeOnDelete();
            $table->foreignId('technicien_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('debut')->nullable();
            $table->timestamp('fin')->nullable();
            $table->string('etat')->default('en_cours'); // en_cours | realisee | suspendue
            $table->text('observations')->nullable();
            $table->json('metriques')->nullable(); // mesures, niveaux optiques, photos...
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('interventions');
    }
};
