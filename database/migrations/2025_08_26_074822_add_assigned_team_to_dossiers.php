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
            $table->foreignId('assigned_team_id')->nullable()
                  ->after('assigned_to')
                  ->constrained('teams')->nullOnDelete();
            $table->index(['assigned_team_id','statut']);
        });
    }
    public function down(): void
    {
        Schema::table('dossiers_raccordement', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_team_id');
        });
    }

};
