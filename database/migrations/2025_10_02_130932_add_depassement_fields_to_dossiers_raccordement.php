<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dossiers_raccordement', function (Blueprint $table) {
            $table->string('depassement_distance')->nullable();
            $table->string('depassement_gps_abonne')->nullable();
            $table->string('depassement_gps_pbo')->nullable();
            $table->string('depassement_nom_pbo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('dossiers_raccordement', function (Blueprint $table) {
            $table->dropColumn([
                'depassement_distance',
                'depassement_gps_abonne',
                'depassement_gps_pbo',
                'depassement_nom_pbo',
            ]);
        });
    }

};
