<?php

// database/migrations/2025_08_26_200000_add_nature_and_report_to_dossiers.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('dossiers_raccordement', function (Blueprint $t) {
      $t->string('nature')->default('raccordement')->after('type_service'); // raccordement | maintenance
      // Rapport d’installation / dépannage
      $t->json('rapport_installation')->nullable()->after('pieces_jointes');
      // Champs clés usuels (facilitent les filtres/exports)
      $t->string('msan')->nullable();
      $t->string('fat')->nullable();
      $t->string('port')->nullable();
      $t->string('port_disponible')->nullable();
      $t->string('type_cable')->nullable();
      $t->integer('lineaire_m')->nullable(); // linéaire en mètres
      $t->decimal('puissance_fat_dbm', 6, 2)->nullable();
      $t->decimal('puissance_pto_dbm', 6, 2)->nullable();
    });
  }
  public function down(): void {
    Schema::table('dossiers_raccordement', function (Blueprint $t) {
      $t->dropColumn([
        'nature','rapport_installation','msan','fat','port','port_disponible',
        'type_cable','lineaire_m','puissance_fat_dbm','puissance_pto_dbm'
      ]);
    });
  }
};
