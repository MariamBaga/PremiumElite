<?php

// database/migrations/2025_08_26_120200_create_plaques_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('plaques', function (Blueprint $t) {
      $t->id();
      $t->string('code')->unique();
      $t->string('nom')->nullable();
      $t->string('zone')->nullable();
      $t->string('statut')->default('etude'); // etude | gc | pose_pbo_pm | tirage | tests | service
      $t->integer('foyers_raccordables')->default(0);
      $t->integer('pbo_installes')->default(0);
      $t->decimal('coverage',5,2)->default(0); // %
      $t->json('geom')->nullable(); // GeoJSON (polygon/linestring)
      $t->timestamps();

      $t->index(['zone','statut']);
    });
  }
  public function down(): void { Schema::dropIfExists('plaques'); }
};
