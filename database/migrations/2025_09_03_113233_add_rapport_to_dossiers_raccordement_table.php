<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('dossiers_raccordement', function (Blueprint $table) {
            $table->string('rapport_satisfaction')->nullable()->after('statut');
            $table->text('rapport_intervention')->nullable()->after('rapport_satisfaction');
        });
    }

    public function down(): void {
        Schema::table('dossiers_raccordement', function (Blueprint $table) {
            $table->dropColumn(['rapport_satisfaction', 'rapport_intervention']);
        });
    }
};
