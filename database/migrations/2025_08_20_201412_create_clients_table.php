<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('residentiel'); // residentiel | professionnel
            $table->string('nom')->nullable();              // pour résidentiel
            $table->string('prenom')->nullable();
            $table->string('raison_sociale')->nullable();   // pour entreprise
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('adresse_ligne1');
            $table->string('adresse_ligne2')->nullable();
            $table->string('ville')->nullable();
            $table->string('zone')->nullable();             // zone commerciale / technique

            // 🆕 Champs ajoutés
            $table->string('numero_ligne')->nullable();
            $table->string('numero_point_focal')->nullable();
            $table->string('localisation')->nullable();
            $table->date('date_paiement')->nullable();
            $table->date('date_affectation')->nullable();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('metadonnees')->nullable();        // champs libres (CRM id, etc.)
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('clients');
    }
};
