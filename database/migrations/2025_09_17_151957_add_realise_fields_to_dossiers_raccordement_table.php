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
        if (!Schema::hasColumn('dossiers_raccordement', 'raison_non_activation')) {
            $table->text('raison_non_activation')->nullable()->after('rapport_intervention');
        }
    });
}

public function down(): void
{
    Schema::table('dossiers_raccordement', function (Blueprint $table) {
        if (Schema::hasColumn('dossiers_raccordement', 'raison_non_activation')) {
            $table->dropColumn('raison_non_activation');
        }
    });
}


};
