<?php

// database/migrations/2025_08_26_120210_create_extensions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('extensions', function (Blueprint $t) {
      $t->id();
      $t->string('code')->unique();
      $t->string('zone')->nullable();
      $t->string('statut')->default('planifie'); // planifie | en_cours | termine
      $t->integer('foyers_cibles')->default(0);
      $t->decimal('roi_estime',10,2)->nullable();
      $t->json('geom')->nullable(); // GeoJSON (linestring/polygon)
      $t->timestamps();
      $t->index(['zone','statut']);
    });
  }
  public function down(): void { Schema::dropIfExists('extensions'); }
};
