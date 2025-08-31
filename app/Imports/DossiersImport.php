<?php

namespace App\Imports;

use App\Models\DossierRaccordement;
use App\Models\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DossiersImport implements ToCollection, WithHeadingRow
{
    protected int $headingRow = 1;

    public function headingRow(): int
    {
        return $this->headingRow;
    }

    public function collection(Collection $rows)
    {
        if ($rows->count() > 0 && $this->allEmpty($rows->first())) {
            $this->headingRow = 2;
            Log::info("[DossiersImport] Correction: headingRow=2");
            return;
        }

        Log::info('[DossiersImport] Début import', ['count' => $rows->count()]);

        foreach ($rows as $idx => $r) {
            $reference   = $this->pick($r, ['Reference','Référence']);
            $clientId    = $this->pick($r, ['Client ID','client_id']);
            $typeService = $this->pick($r, ['Type Service','type_service']) ?? 'residentiel';
            $pbo         = $this->pick($r, ['PBO']);
            $pm          = $this->pick($r, ['PM']);
            $statut      = $this->pick($r, ['Statut','statut']) ?? 'en_appel';
            $description = $this->pick($r, ['Description']);
            $zone        = $this->pick($r, ['Zone']);
            $datePlanif  = $this->parseDate($this->pick($r, ['Date planifiee','date_planifiee']));
            $dateReal    = $this->parseDate($this->pick($r, ['Date realisation','date_realisation']));

            if (empty($reference) || empty($clientId)) continue;

            $client = Client::find($clientId);
            if (!$client) {
                Log::warning("[DossiersImport] Client $clientId introuvable pour ref $reference");
                continue;
            }

            DossierRaccordement::updateOrCreate(
                ['reference' => $reference],
                [
                    'client_id'       => $client->id,
                    'type_service'    => $typeService,
                    'pbo'             => $pbo,
                    'pm'              => $pm,
                    'statut'          => $statut,
                    'description'     => $description,
                    'zone'            => $zone,
                    'date_planifiee'  => $datePlanif,
                    'date_realisation'=> $dateReal,
                    'created_by'      => auth()->id(),
                ]
            );
        }
    }

    private function allEmpty($row): bool
    {
        foreach ($row as $v) if ($v !== null && $v !== '') return false;
        return true;
    }

    private function pick($row, array $labels)
    {
        foreach ($labels as $k) if ($row->has($k)) return $this->clean($row[$k]);
        $wanted = array_map([$this,'norm'], $labels);
        foreach ($row as $key => $val) {
            if (in_array($this->norm((string)$key), $wanted, true)) return $this->clean($val);
        }
        return null;
    }

    private function parseDate($v)
    {
        if (!$v) return null;
        if (is_numeric($v)) {
            try { return Carbon::instance(XlsDate::excelToDateTimeObject((float)$v)); } catch (\Throwable $e) {}
        }
        try { return Carbon::parse((string)$v); } catch (\Throwable $e) { return null; }
    }

    private function clean($v)
    {
        if (is_string($v)) $v = trim(preg_replace('/\p{Z}+/u',' ', str_replace(["\xC2\xA0"], ' ', $v)));
        return $v === '' ? null : $v;
    }

    private function norm(string $s): string
    {
        $s = Str::ascii($s);
        $s = strtolower($s);
        return preg_replace('/[\p{Z}\s\W_]+/u','', $s);
    }
}
