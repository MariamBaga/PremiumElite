<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DossierRaccordement;
use App\Models\TeamDossier;
use App\Models\Team;

class SyncTeamDossiers extends Command
{
     protected $signature = 'resync:team-dossiers';
    protected $description = 'Resynchronise tous les dossiers assignés aux équipes avec la table team_dossiers.';

    public function handle()
    {
        $this->info("🔄 Début de la resynchronisation...");

        $dossiers = DossierRaccordement::whereNotNull('assigned_team_id')->get();
        $countCreated = 0;
        $countSkipped = 0;

        foreach ($dossiers as $dossier) {
            $teamId = $dossier->assigned_team_id;

            // Vérifie que l’équipe existe
            if (!Team::find($teamId)) {
                $this->warn("⚠️ Dossier #{$dossier->id} assigné à une équipe inexistante (team_id={$teamId}) → ignoré");
                $countSkipped++;
                continue;
            }

            // Création ou mise à jour de la corbeille
            $teamDossier = TeamDossier::updateOrCreate(
                ['team_id' => $teamId, 'dossier_id' => $dossier->id],
                ['etat' => 'en_cours', 'created_by' => $dossier->created_by ?? 1]
            );

            // Vérification si créé ou existait déjà
            $teamDossier->wasRecentlyCreated ? $countCreated++ : $countSkipped++;
        }

        $this->info("✅ Synchronisation terminée : $countCreated dossiers ajoutés, $countSkipped ignorés.");
        return 0;
    }
}
