<?php
// app/Imports/ClientsImport.php
namespace App\Imports;

use App\Models\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;
use Carbon\Carbon;

class ClientsImport implements ToCollection, WithHeadingRow
{
    // On garde les en-têtes tels quels (pas de snake)
    public function headingRow(): int { return 1; }

    public function collection(Collection $rows)
    {
        foreach ($rows as $r) {
            // 1) Récupérer via plusieurs libellés possibles (tolérant aux variantes)
            $nomStructure   = $this->pick($r, ['IDENTIFICATION CLIENT (NOM/ STRUCTURE)','IDENTIFICATION CLIENT (NOM/STRUCTURE)','Client','Nom','Structure']);
            $numLigne       = $this->pick($r, ['N° DE LIGNE','N° LIGNE','Numero ligne','No ligne']);
            $numPointFocal  = $this->pick($r, ['N° POINT FOCAL','Point focal','Numero point focal']);
            $localisation   = $this->pick($r, ['Localisation','Adresse','Adresse ligne 1']);
            $datePay        = $this->pick($r, ['Date de paiement','Date paiement','Paiement']);
            $dateAffect     = $this->pick($r, ['Date d’affectation','Date affectation','Affectation']);

            if (empty($nomStructure) && empty($numLigne) && empty($numPointFocal)) {
                continue; // ligne vide
            }

            // 2) Déterminer résidentiel / professionnel
            // très simple: si le nom semble "Entreprise" (majuscule + espace + mots clés), tu peux affiner selon tes données
            $isCompany = preg_match('/(SARL|SA|SAS|SASU|EURL|S\.A\.|SOCIETE|ENTREPRISE)/i', (string)$nomStructure);
            [$prenom, $nom] = $this->splitName($nomStructure);

            // 3) Parser les dates (gère Excel serial, dd/mm/yyyy, yyyy-mm-dd…)
            $datePaiement    = $this->parseDate($datePay);
            $dateAffectation = $this->parseDate($dateAffect);

            // 4) Upsert: on évite les doublons par N° de ligne si fourni, sinon couple (nom_structure + point focal)
            $unique = $numLigne
                ? ['numero_ligne' => $numLigne]
                : ['raison_sociale' => $nomStructure, 'numero_point_focal' => $numPointFocal];

            Client::updateOrCreate($unique, [
                'type'                => $isCompany ? 'professionnel' : 'residentiel',
                'nom'                 => $isCompany ? null : $nom,
                'prenom'              => $isCompany ? null : $prenom,
                'raison_sociale'      => $isCompany ? $nomStructure : null,
                'telephone'           => $this->pick($r, ['Téléphone','Telephone','Tel','Phone']),
                'email'               => $this->pick($r, ['Email','Courriel','Mail']),
                'adresse_ligne1'      => $localisation ?? '-',
                'ville'               => $this->pick($r, ['Ville','City']),
                'zone'                => $this->pick($r, ['Zone']),
                'numero_ligne'        => $numLigne,
                'numero_point_focal'  => $numPointFocal,
                'localisation'        => $localisation,
                'date_paiement'       => $datePaiement,
                'date_affectation'    => $dateAffectation,
            ]);
        }
    }

    private function pick($row, array $keys)
    {
        foreach ($keys as $k) {
            // Essaye clé exacte puis version "slugifiée" pour tolérer espaces/accents
            if (isset($row[$k])) return $this->clean($row[$k]);
            foreach ($row as $key => $val) {
                if (Str::slug($key) === Str::slug($k)) return $this->clean($val);
            }
        }
        return null;
    }

    private function clean($v) {
        if (is_string($v)) $v = trim($v);
        return $v === '' ? null : $v;
    }

    private function parseDate($v)
    {
        if (empty($v)) return null;
        // Excel serial number
        if (is_numeric($v)) {
            try { return Carbon::instance(XlsDate::excelToDateTimeObject((float)$v))->startOfDay(); } catch (\Throwable $e) {}
        }
        // Try common patterns
        foreach (['d/m/Y','d-m-Y','Y-m-d','m/d/Y'] as $fmt) {
            try { return Carbon::createFromFormat($fmt, (string)$v)->startOfDay(); } catch (\Throwable $e) {}
        }
        // Fallback parse
        try { return Carbon::parse((string)$v)->startOfDay(); } catch (\Throwable $e) { return null; }
    }

    private function splitName(string $full = null): array
    {
        $full = trim((string)$full);
        if ($full === '') return [null, null];
        $parts = preg_split('/\s+/', $full);
        if (count($parts) >= 2) return [array_shift($parts), implode(' ', $parts)];
        return [null, $full];
    }
}
