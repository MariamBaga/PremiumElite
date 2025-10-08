<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('dossiers_raccordement', function (Blueprint $table) {
            $table->string('implantation_gps_fat')->nullable();
        });
    }

    public function down(): void {
        Schema::table('dossiers_raccordement', function (Blueprint $table) {
            $table->dropColumn('implantation_gps_fat');
        });
    }
};
