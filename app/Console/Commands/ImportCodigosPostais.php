<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Distrito;
use App\Models\Municipio;
use App\Models\Freguesia;
use App\Models\CodigoPostal;

class ImportCodigosPostais extends Command
{
    protected $signature = 'import:codigos-postais {--path=storage/app/cod_post.csv}';
    protected $description = 'Importa códigos postais e liga distrito/município/freguesia a partir de CSV';

    public function handle()
    {
        $pathArg = $this->option('path');
        $path = str_starts_with($pathArg, '/')
            ? $pathArg
            : base_path($pathArg);

        if (!file_exists($path)) {
            $this->error("CSV não encontrado: {$path}");
            return 1;
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            $this->error("Não foi possível abrir o ficheiro.");
            return 1;
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            $this->error("CSV vazio.");
            return 1;
        }

        $delims = ["\t", ";", ","];
        $bestDelim = "\t";
        $bestCount = 0;

        foreach ($delims as $d) {
            $count = count(str_getcsv($firstLine, $d));
            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelim = $d;
            }
        }

        rewind($handle);

        $header = fgetcsv($handle, 0, $bestDelim);

        $header = array_map(function ($h) {
            $h = preg_replace('/^\xEF\xBB\xBF/', '', (string)$h); 
            return trim($h);
        }, $header ?? []);

        $expected = ['CodigoPostal', 'Distrito', 'Concelho', 'Freguesia'];

        $conn = 'mysql2';

        DB::connection($conn)->transaction(function () use ($handle, $bestDelim, $conn) {
            $distritosCache = [];
            $municipiosCache = [];
            $freguesiasCache = [];

            $i = 0;

            while (($row = fgetcsv($handle, 0, $bestDelim)) !== false) {
                $i++;

                if (count($row) < 4) continue;

                [$codigo, $distritoNome, $municipioNome, $freguesiaNome] = $row;

                $codigo = trim((string)$codigo);
                $distritoNome = trim((string)$distritoNome);
                $municipioNome = trim((string)$municipioNome);
                $freguesiaNome = trim((string)$freguesiaNome);

                if ($codigo === '' || $distritoNome === '' || $municipioNome === '' || $freguesiaNome === '') {
                    continue;
                }

                $distritoId = $distritosCache[$distritoNome] ??= Distrito::on($conn)->firstOrCreate(
                    ['nome' => $distritoNome]
                )->id;

                $municipioKey = $distritoId.'|'.$municipioNome;
                $municipioId = $municipiosCache[$municipioKey] ??= Municipio::on($conn)->firstOrCreate(
                    ['distrito_id' => $distritoId, 'nome' => $municipioNome]
                )->id;

                $freguesiaKey = $municipioId.'|'.$freguesiaNome;
                $freguesiaId = $freguesiasCache[$freguesiaKey] ??= Freguesia::on($conn)->firstOrCreate(
                    ['municipio_id' => $municipioId, 'nome' => $freguesiaNome]
                )->id;

                CodigoPostal::on($conn)->updateOrCreate(
                    ['codigo' => $codigo],
                    ['freguesia_id' => $freguesiaId]
                );

                if ($i % 5000 === 0) {
                    $this->info("Importadas {$i} linhas...");
                }
            }
        });

        fclose($handle);

        $this->info("Import concluído.");
        return 0;
    }
}
