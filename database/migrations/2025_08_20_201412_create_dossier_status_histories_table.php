<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dossier_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers_raccordement')->cascadeOnDelete();
            $table->string('ancien_statut')->nullable();
            $table->string('nouveau_statut');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('dossier_status_history');
    }
};
