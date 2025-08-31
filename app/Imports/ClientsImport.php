<?php

namespace App\Imports;

use App\Models\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClientsImport implements ToCollection, WithHeadingRow
{
    // par défaut 1, mais on va auto-détecter dans collection()
    protected int $headingRow = 1;

    public function headingRow(): int
    {
        return $this->headingRow;
    }

    public function collection(Collection $rows)
    {
        // Détection dynamique : si la 1re ligne est vide ou bizarre → on corrige
        if ($rows->count() > 0) {
            $firstRow = array_keys($rows->first()->toArray());

            // Cherche "identification" ou "ligne" dans les clés
            $foundIndex = 1;
            foreach ($firstRow as $k) {
                $norm = $this->norm($k);
                if (str_contains($norm, 'identificationclient')
                    || str_contains($norm, 'nodeligne')
                    || str_contains($norm, 'npointfocal')
                ) {
                    $foundIndex = 1; // déjà bon
                    break;
                }
            }

            // si la première ligne n'est pas bonne → on force à 2
            if ($this->allEmpty($rows->first())) {
                $this->headingRow = 2;
                Log::info("[ClientsImport] Correction: headingRow=2");
                return; // relance à la 2e tentative avec la bonne ligne
            }
        }

        Log::info('[ClientsImport] Début import', ['count' => $rows->count()]);

        foreach ($rows as $idx => $r) {
            $nomStructure  = $this->pick($r, ['IDENTIFICATION CLIENT (NOM/STRUCTURE)','identification_client_nom_structure']);
            $numLigne      = $this->pick($r, ['N° DE LIGNE','n_de_ligne']);
            $numPointFocal = $this->pick($r, ['N° POINT FOCAL','n_point_focal']);
            $localisation  = $this->pick($r, ['Localisation']);
            $datePay       = $this->pick($r, ['Date de paiement','date_de_paiement']);
            $dateAffect    = $this->pick($r, ["Date d'affectation","date_d_affectation"]);

            if (empty($nomStructure) && empty($numLigne) && empty($numPointFocal)) {
                continue;
            }

            $isCompany = (bool) preg_match('/\b(SARL|SA|SAS|EURL|SOCIETE|ENTREPRISE)\b/i', (string)$nomStructure);
            [$prenom, $nom] = $this->splitName($nomStructure);

            $datePaiement    = $this->parseDate($datePay);
            $dateAffectation = $this->parseDate($dateAffect);

            $unique = !empty($numLigne)
                ? ['numero_ligne' => (string) $numLigne]
                : ['raison_sociale' => $nomStructure, 'numero_point_focal' => (string) $numPointFocal];

            Client::updateOrCreate($unique, [
                'type'               => $isCompany ? 'professionnel' : 'residentiel',
                'nom'                => $isCompany ? null : $nom,
                'prenom'             => $isCompany ? null : $prenom,
                'raison_sociale'     => $isCompany ? $nomStructure : null,
                'telephone'          => $this->pick($r, ['Telephone','Tel','Phone']),
                'email'              => $this->pick($r, ['Email','Mail']),
                'adresse_ligne1'     => $localisation ?? '-',
                'ville'              => $this->pick($r, ['Ville','City']),
                'zone'               => $this->pick($r, ['Zone']),
                'numero_ligne'       => (string) $numLigne,
                'numero_point_focal' => (string) $numPointFocal,
                'localisation'       => $localisation,
                'date_paiement'      => $datePaiement,
                'date_affectation'   => $dateAffectation,
            ]);
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
        foreach (['d/m/Y','d-m-Y','Y-m-d','m/d/Y'] as $fmt) {
            try { return Carbon::createFromFormat($fmt, (string)$v)->startOfDay(); } catch (\Throwable $e) {}
        }
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
