<?php

namespace App\Imports;

use App\Models\Distrito;
use App\Models\Municipio;
use App\Models\Freguesia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LocalidadesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows): void
    {
        DB::connection('mysql2')->transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $nomeDistrito = trim((string) ($row['distrito_ilha_dsg'] ?? ''));
                $nomeMunicipio = trim((string) ($row['municipio_dsg'] ?? ''));
                $nomeFreguesia = trim((string) ($row['freguesia_dsg'] ?? ''));

                if (!$nomeDistrito || !$nomeMunicipio || !$nomeFreguesia) {
                    continue;
                }

                $distrito = Distrito::firstOrCreate([
                    'nome' => $nomeDistrito,
                ]);

                $municipio = Municipio::firstOrCreate([
                    'nome' => $nomeMunicipio,
                    'distrito_id' => $distrito->id,
                ]);

                Freguesia::firstOrCreate([
                    'nome' => $nomeFreguesia,
                    'municipio_id' => $municipio->id,
                ]);
            }
        });
    }
}