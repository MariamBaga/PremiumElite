<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\StatutDossier;

return new class extends Migration {
    public function up(): void {
        Schema::create('dossiers_raccordement', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('reference')->unique();               // ex: DR-2025-000123
            $table->string('type_service')->default('residentiel'); // residentiel | professionnel
            $table->string('pbo')->nullable();                    // code PBO si connu
            $table->string('pm')->nullable();                     // code PM si connu
            $table->string('statut')->default('en_appel');

            $table->text('description')->nullable();
            $table->json('tags')->nullable();                     // ["VIP","prioritaire","gros_debit"]
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // technicien
            $table->timestamp('date_planifiee')->nullable();
            $table->timestamp('date_realisation')->nullable();
            $table->json('pieces_jointes')->nullable();           // liens de fichiers si besoin
            $table->timestamps();

            $table->index(['statut', 'type_service', 'zone']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('dossiers_raccordement');
    }
};
