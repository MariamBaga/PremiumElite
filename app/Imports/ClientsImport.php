<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\DossierRaccordement;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClientsImport implements ToCollection, WithHeadingRow
{
    protected int $headingRow = 1;
    public function headingRow(): int { return $this->headingRow; }

    public function collection(Collection $rows)
    {
        // auto-détection d’un header décalé
        if ($rows->count() > 0) {
            if ($this->allEmpty($rows->first())) {
                $this->headingRow = 2;
                Log::info('[ClientsImport] Correction: headingRow=2');
                return; // relance par Laravel-Excel
            }
        }

        Log::info('[ClientsImport] Début import', ['count' => $rows->count()]);

        foreach ($rows as $idx => $r) {
            // ---- ANCIENNES COLONNES (toujours supportées)
            $nomStructure   = $this->pick($r, ['IDENTIFICATION CLIENT (NOM/STRUCTURE)','identification_client_nom_structure','CLIENT','Client','client']);
            $numLigneOld    = $this->pick($r, ['N° DE LIGNE','n_de_ligne']);
            $numPointFocal  = $this->pick($r, ['N° POINT FOCAL','n_point_focal']);
            $localisation   = $this->pick($r, ['Localisation']);

            $datePay        = $this->pick($r, ['Date de paiement','date_de_paiement']);
            $dateAffect     = $this->pick($r, ["Date d'affectation","date_d_affectation"]);

            // ---- NOUVELLES COLONNES (fichier test Optimux)
            $ligne          = $this->pick($r, ['LIGNE','Ligne','ligne']) ?? $numLigneOld;
            $contact        = $this->pick($r, ['Contact','contact']);
            $serviceRaw     = $this->pick($r, ['Service (Cuivre/FTTH)','Service','service']);
            $localite       = $this->pick($r, ['LOCALITE','Localite','localite']);
            $categorieRaw   = $this->pick($r, ['Catégorie (B2B/B2C)','Categorie','categorie']);
            $dateRecep      = $this->pick($r, ['Date de Réception des Raccordements','Date Reception','date_reception']);
            $dateFinTrav    = $this->pick($r, ['Date de Fin des Travaux','Date Fin','date_fin']);
            $port           = $this->pick($r, ['Port','port']);
            $pboLineaire    = $this->pick($r, ['PBO / Lineaire Utilisé','PBO/Lineaire','pbo_lineaire']);
            $poteaux        = $this->pick($r, ['Poteaux Implantés','Poteaux','poteaux']);
            $armements      = $this->pick($r, ['Armements Poteaux','Armements','armements']);
            $statutRaw      = $this->pick($r, ['Statut','STATUT','Etat','ETAT']);
            $reportingRaw   = $this->pick($r, ['Taux de Reporting Daily J+1 (Ok/NOk)','Reporting Daily','reporting']);
            $activeRaw      = $this->pick($r, ['ACTIVE','Active']);
            $observation    = $this->pick($r, ['Observation','OBSERVATION']);
            $pilote         = $this->pick($r, ['Pilote Raccordement  (Prestataire)','Pilote Raccordement (Prestataire)','Pilote']);

            // identité minimale
            if (empty($nomStructure) && empty($ligne) && empty($contact)) {
                continue;
            }

            // heuristique personne/entreprise
            $isCompany = (bool) preg_match('/\b(SARL|SA|SAS|EURL|SOCIETE|ENTREPRISE|SPA|SNC)\b/i', (string)$nomStructure);
            [$prenom, $nom] = $this->splitName($nomStructure);

            // dates
            $datePaiement    = $this->parseDate($datePay);
            $dateAffectation = $this->parseDate($dateAffect);
            $dateReception   = $this->parseDate($dateRecep);
            $dateFin         = $this->parseDate($dateFinTrav);

            // clé d'unicité côté client (priorité à la ligne si fournie)
            $unique = !empty($ligne)
                ? ['numero_ligne' => (string) $ligne]
                : ['raison_sociale' => (string)$nomStructure, 'telephone' => (string)$contact];

            // upsert client
            $client = Client::updateOrCreate($unique, [
                'type'               => $isCompany ? 'professionnel' : 'residentiel',
                'nom'                => $isCompany ? null : $nom,
                'prenom'             => $isCompany ? null : $prenom,
                'raison_sociale'     => $isCompany ? (string)$nomStructure : null,
                'telephone'          => $contact ?? $this->pick($r, ['Telephone','Tel','Phone']),
                'email'              => $this->pick($r, ['Email','Mail']),
                'adresse_ligne1'     => $localisation ?? ($localite ?? '-'),
                'ville'              => $this->pick($r, ['Ville','City']),
                'zone'               => $localite ?? $this->pick($r, ['Zone']),
                'numero_ligne'       => (string)$ligne,
                'numero_point_focal' => (string)$numPointFocal,
                'localisation'       => $localisation ?? $localite,
                'date_paiement'      => $datePaiement,
                'date_affectation'   => $dateAffectation,
            ]);

            // dossier (un courant par client)
            $dossier = DossierRaccordement::firstOrNew([
                'client_id' => $client->id,
            ]);

            if (!$dossier->exists) {
                $dossier->reference = 'DR-'.date('Y').'-'.str_pad(rand(1,999999),6,'0',STR_PAD_LEFT);
            }

            // mapping service/catégorie/reporting/active
            $serviceAcces = (stripos((string)$serviceRaw, 'cuivre') !== false) ? 'Cuivre' : ((stripos((string)$serviceRaw,'ftth') !== false) ? 'FTTH' : null);
            $categorie    = null;
            if ($categorieRaw) {
                $u = strtoupper(trim((string)$categorieRaw));
                if (in_array($u, ['B2B','B2C'], true)) $categorie = $u;
            }
            $reporting = null;
            if ($reportingRaw) {
                $u = strtoupper(trim((string)$reportingRaw));
                $reporting = in_array($u, ['OK','NOK'], true) ? $u : null;
            }
            $isActive = in_array(strtoupper((string)$activeRaw), ['1','OUI','YES','TRUE','OK'], true);

            // statut normalisé (string pour compat avec ta table)
            $statMap = [
                'en appel'       => 'en_appel',
                'injoignable'    => 'injoignable',
                'rendez-vous'    => 'rendez_vous',
                'rendez vous'    => 'rendez_vous',
                'pbo sature'     => 'pbo_sature',
                'pbo saturé'     => 'pbo_sature',
                'zone depourvue' => 'zone_depourvue',
                'zone dépourvue' => 'zone_depourvue',
                'active'         => 'active',
                'activé'         => 'active',
                'realise'        => 'realise',
                'réalisé'        => 'realise',
            ];
            $statCle = $this->norm((string)$statutRaw);
            // petite normalisation douce
            $statKeyReadable = trim(mb_strtolower((string)$statutRaw));
            $statut = $statMap[$statKeyReadable] ?? ($dossier->statut ?? 'en_appel');

            // remplissage dossier (nouvelles colonnes si ta migration ALTER est passée)
            $dossier->ligne                    = (string)($ligne ?? $client->numero_ligne);
            $dossier->contact                  = (string)($contact ?? $client->telephone);
            $dossier->service_acces            = $serviceAcces ?? $dossier->service_acces;
            $dossier->localite                 = $localite ?? $dossier->localite;
            $dossier->categorie                = $categorie ?? $dossier->categorie;
            $dossier->date_reception_raccordement = $dateReception ?? $dossier->date_reception_raccordement;
            $dossier->date_fin_travaux         = $dateFin ?? $dossier->date_fin_travaux;
            $dossier->port                     = $port ?? $dossier->port;
            $dossier->pbo_lineaire_utilise     = $pboLineaire ?? $dossier->pbo_lineaire_utilise;
            $dossier->nb_poteaux_implantes     = is_null($poteaux) ? $dossier->nb_poteaux_implantes : (int)$poteaux;
            $dossier->nb_armements_poteaux     = is_null($armements) ? $dossier->nb_armements_poteaux : (int)$armements;
            $dossier->taux_reporting_j1        = $reporting ?? $dossier->taux_reporting_j1;
            $dossier->is_active                = $isActive;
            $dossier->observation              = $observation ?? $dossier->observation;
            $dossier->pilote_raccordement      = $pilote ?? $dossier->pilote_raccordement;

            // statut + RDV si présent
            $dossier->statut = $statut;

            if ($dossier->statut === 'rendez_vous') {
                // tente RDV direct si une colonne dédiée existe
                $maybeRdv = $this->pick($r, ['Date RDV','Rendez-Vous','RDV']);
                $rdv = $this->parseDate($maybeRdv);
                // sinon essaie de parser dans "Observation" (ex: "RDV 02/10/2025")
                if (!$rdv && $observation) {
                    if (preg_match('/(\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4})/u', $observation, $m)) {
                        $rdv = $this->parseDate($m[1]);
                    }
                }
                if ($rdv) $dossier->rendez_vous_at = $rdv;
            }

            // compat ancien fichier
            $dossier->assigned_to      = $this->pick($r, ['Technicien ID','technicien']) ?? $dossier->assigned_to;
            $dossier->assigned_team_id = $this->pick($r, ['Equipe ID','equipe']) ?? $dossier->assigned_team_id;
            $dossier->zone             = $this->pick($r, ['Zone']) ?? $dossier->zone;
            $dossier->date_planifiee   = $this->parseDate($this->pick($r, ['Date planifiée','date_planifiee'])) ?? $dossier->date_planifiee;

            $dossier->save();
        }
    }

    // -------- Helpers ----------
    private function allEmpty($row): bool
    {
        foreach ($row as $v) {
            if ($v !== null && $v !== '') return false;
        }
        return true;
    }

    private function pick($row, array $labels)
    {
        foreach ($labels as $k) {
            if ($row->has($k)) return $this->clean($row[$k]);
        }
        $wanted = array_map([$this,'norm'], $labels);
        foreach ($row as $key => $val) {
            if (in_array($this->norm((string)$key), $wanted, true)) {
                return $this->clean($val);
            }
        }
        return null;
    }

    private function parseDate($v)
    {
        if ($v === null || $v === '') return null;
        if (is_numeric($v)) {
            try { return Carbon::instance(XlsDate::excelToDateTimeObject((float)$v))->startOfDay(); } catch (\Throwable $e) {}
        }
        foreach (['d/m/Y','d-m-Y','Y-m-d','m/d/Y','d/m/y','d-m-y'] as $fmt) {
            try { return Carbon::createFromFormat($fmt, (string)$v)->startOfDay(); } catch (\Throwable $e) {}
        }
        // ex: "02-juin" -> on tente un parse libre
        try { return Carbon::parse((string)$v)->startOfDay(); } catch (\Throwable $e) { return null; }
    }

    private function splitName(?string $full): array
    {
        $full = trim((string)$full);
        if ($full === '') return [null, null];
        $parts = preg_split('/\s+/', $full);
        if (count($parts) >= 2) return [array_shift($parts), implode(' ', $parts)];
        return [null, $full];
    }

    private function clean($v)
    {
        if (is_string($v)) {
            $v = preg_replace('/\p{Z}+/u', ' ', $v);
            $v = str_replace(["\xC2\xA0"], ' ', $v);
            $v = trim($v);
        }
        if (is_int($v) || is_float($v)) $v = (string)$v;
        return $v === '' ? null : $v;
    }

    private function norm(string $s): string
    {
        $s = Str::ascii($s);
        $s = strtolower($s);
        $s = preg_replace('/[\p{Z}\s\W_]+/u', '', $s);
        return $s;
    }
}
