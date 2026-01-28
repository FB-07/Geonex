<?php

namespace App\Console\Commands;

use App\Models\Distrito;
use App\Models\Freguesia;
use App\Models\Municipio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportLocations extends Command
{
    protected $signature = 'locations:import {--path= : storage/app/locations.xlsx}';

    public function handle(): int
    {
        $path = $this->option('path') ?: storage_path('app/locations.xlsx');

        if (!file_exists($path)) {
            $this->error("Ficheiro não encontrado: {$path}");
            return self::FAILURE;
        }

        $rows = Excel::toArray([], $path)[0] ?? [];

        if (count($rows) === 0) {
            $this->error("Excel vazio ou não foi possível ler.");
            return self::FAILURE;
        }

        array_shift($rows);

        $this->info('A importar');

        $distritosCache = [];  
        $municipiosCache = [];  

        DB::connection('mysql2')->transaction(function () use ($rows, &$distritosCache, &$municipiosCache) {

            foreach ($rows as $row) {
                $nuts = trim((string)($row[1] ?? ''));

                if ($nuts !== 'Continente') {
                    continue;
                }

                $distritoNome  = trim((string)($row[2] ?? ''));
                $municipioNome = trim((string)($row[3] ?? ''));
                $freguesiaNome = trim((string)($row[4] ?? ''));

                if ($distritoNome === '' || $municipioNome === '' || $freguesiaNome === '') {
                    continue;
                }

                $distritoId = $distritosCache[$distritoNome] ?? null;
                if (!$distritoId) {
                    $d = Distrito::firstOrCreate(['nome' => $distritoNome]);
                    $distritoId = $d->id;
                    $distritosCache[$distritoNome] = $distritoId;
                }

                $mKey = $distritoId . '|' . $municipioNome;
                $municipioId = $municipiosCache[$mKey] ?? null;
                if (!$municipioId) {
                    $m = Municipio::firstOrCreate([
                        'distrito_id' => $distritoId,
                        'nome'        => $municipioNome,
                    ]);
                    $municipioId = $m->id;
                    $municipiosCache[$mKey] = $municipioId;
                }

                Freguesia::firstOrCreate([
                    'municipio_id' => $municipioId,
                    'nome'         => $freguesiaNome,
                ]);
            }
        });

        $this->info('Import concluído!');
        return self::SUCCESS;
    }
}
