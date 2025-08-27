<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_team_dossiers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('team_dossiers', function (Blueprint $t) {
      $t->id();
      $t->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
      $t->foreignId('dossier_id')->constrained('dossiers_raccordement')->cascadeOnDelete();
      // statut “opérationnel” côté équipe :
      // en_cours | contrainte | reporte | cloture
      $t->string('etat')->default('en_cours');

      // métadonnées selon l’action :
      $t->text('motif')->nullable();                 // contrainte ou commentaire
      $t->timestamp('date_report')->nullable();      // si report
      $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

      $t->timestamps();

      $t->unique(['team_id','dossier_id']);         // un dossier dans une équipe une seule fois
      $t->index(['etat','team_id']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('team_dossiers');
  }
};
