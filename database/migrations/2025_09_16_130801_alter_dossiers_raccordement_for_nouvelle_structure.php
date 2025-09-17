<?php
// database/migrations/2025_09_16_120000_alter_dossiers_raccordement_for_nouvelle_structure.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('dossiers_raccordement', function (Blueprint $table) {
            if (!Schema::hasColumn('dossiers_raccordement','ligne'))
                $table->string('ligne')->nullable()->after('client_id');

            if (!Schema::hasColumn('dossiers_raccordement','contact'))
                $table->string('contact')->nullable()->after('ligne');

            if (!Schema::hasColumn('dossiers_raccordement','service_acces'))
                $table->enum('service_acces', ['Cuivre','FTTH'])->nullable()->after('contact');

            if (!Schema::hasColumn('dossiers_raccordement','localite'))
                $table->string('localite')->nullable()->after('service_acces');

            if (!Schema::hasColumn('dossiers_raccordement','categorie'))
                $table->enum('categorie', ['B2B','B2C'])->nullable()->after('localite');

            if (!Schema::hasColumn('dossiers_raccordement','date_reception_raccordement'))
                $table->date('date_reception_raccordement')->nullable()->after('categorie');

            if (!Schema::hasColumn('dossiers_raccordement','date_fin_travaux'))
                $table->date('date_fin_travaux')->nullable()->after('date_reception_raccordement');

            if (!Schema::hasColumn('dossiers_raccordement','port'))
                $table->string('port')->nullable()->after('date_fin_travaux');

            if (!Schema::hasColumn('dossiers_raccordement','pbo_lineaire_utilise'))
                $table->string('pbo_lineaire_utilise')->nullable()->after('port');

            if (!Schema::hasColumn('dossiers_raccordement','nb_poteaux_implantes'))
                $table->unsignedInteger('nb_poteaux_implantes')->nullable()->after('pbo_lineaire_utilise');

            if (!Schema::hasColumn('dossiers_raccordement','nb_armements_poteaux'))
                $table->unsignedInteger('nb_armements_poteaux')->nullable()->after('nb_poteaux_implantes');

            if (!Schema::hasColumn('dossiers_raccordement','taux_reporting_j1'))
                $table->enum('taux_reporting_j1', ['OK','NOK'])->nullable()->after('nb_armements_poteaux');

            if (!Schema::hasColumn('dossiers_raccordement','is_active'))
                $table->boolean('is_active')->default(false)->after('taux_reporting_j1');

            if (!Schema::hasColumn('dossiers_raccordement','observation'))
                $table->text('observation')->nullable()->after('is_active');

            if (!Schema::hasColumn('dossiers_raccordement','pilote_raccordement'))
                $table->string('pilote_raccordement')->nullable()->after('observation');

            if (!Schema::hasColumn('dossiers_raccordement','rendez_vous_at'))
                $table->timestamp('rendez_vous_at')->nullable()->after('date_planifiee');

            if (!Schema::hasColumn('dossiers_raccordement','rendez_vous_notified_at'))
                $table->timestamp('rendez_vous_notified_at')->nullable()->after('rendez_vous_at');

            if (!Schema::hasColumn('dossiers_raccordement','action_injoignable'))
                $table->string('action_injoignable')->nullable()->after('statut');

            if (!Schema::hasColumn('dossiers_raccordement','raison_non_activation'))
                $table->string('raison_non_activation')->nullable()->after('action_injoignable');

            if (!Schema::hasColumn('dossiers_raccordement','rapport_pbo_path'))
                $table->string('rapport_pbo_path')->nullable()->after('pieces_jointes');

            if (!Schema::hasColumn('dossiers_raccordement','rapport_zone_path'))
                $table->string('rapport_zone_path')->nullable()->after('rapport_pbo_path');

            if (!Schema::hasColumn('dossiers_raccordement','rapport_activation_path'))
                $table->string('rapport_activation_path')->nullable()->after('rapport_zone_path');

            if (!Schema::hasColumn('dossiers_raccordement','fiche_client_path'))
                $table->string('fiche_client_path')->nullable()->after('rapport_activation_path');

            if (!Schema::hasColumn('dossiers_raccordement','rapport_intervention_path'))
                $table->string('rapport_intervention_path')->nullable()->after('fiche_client_path');

            if (!Schema::hasColumn('dossiers_raccordement','satisfaction_client_path'))
                $table->string('satisfaction_client_path')->nullable()->after('rapport_intervention_path');

            // Index utiles si non existants
            try { $table->index(['statut','rendez_vous_at']); } catch (\Throwable $e) {}
            try { $table->index(['categorie','service_acces']); } catch (\Throwable $e) {}
            try { $table->index(['localite']); } catch (\Throwable $e) {}
        });
    }

    public function down(): void {
        Schema::table('dossiers_raccordement', function (Blueprint $table) {
            foreach ([
                'ligne','contact','service_acces','localite','categorie',
                'date_reception_raccordement','date_fin_travaux','port','pbo_lineaire_utilise',
                'nb_poteaux_implantes','nb_armements_poteaux','taux_reporting_j1','is_active',
                'observation','pilote_raccordement','rendez_vous_at','rendez_vous_notified_at',
                'action_injoignable','raison_non_activation',
                'rapport_pbo_path','rapport_zone_path','rapport_activation_path',
                'fiche_client_path','rapport_intervention_path','satisfaction_client_path',
            ] as $col) {
                if (Schema::hasColumn('dossiers_raccordement', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
