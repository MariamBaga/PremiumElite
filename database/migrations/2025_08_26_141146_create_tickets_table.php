<?php
// database/migrations/2025_08_26_120100_create_tickets_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('tickets', function (Blueprint $t) {
      $t->id();
      $t->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
      $t->foreignId('dossier_id')->nullable()->constrained('dossiers_raccordement')->nullOnDelete();
      $t->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
      $t->foreignId('assigned_team_id')->nullable()->constrained('teams')->nullOnDelete();
      $t->string('reference')->unique();
      $t->string('type')->default('panne'); // panne | signalement | maintenance
      $t->string('priorite')->default('normal'); // faible | normal | haute | critique
      $t->string('statut')->default('ouvert');   // ouvert | en_cours | resolu | ferme
      $t->string('titre');
      $t->text('description')->nullable();
      $t->timestamp('date_resolution')->nullable();
      $t->timestamps();

      $t->index(['statut','priorite','type']);
    });
  }
  public function down(): void { Schema::dropIfExists('tickets'); }
};
