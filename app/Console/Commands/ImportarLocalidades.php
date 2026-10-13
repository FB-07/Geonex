<?php

namespace App\Console\Commands;

use App\Models\Distrito;
use App\Models\Freguesia;
use App\Models\Municipio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportarLocalidades extends Command
{
    protected $signature = 'localidades:importar {--limpar : Apaga freguesias, municípios e distritos antes de importar}';

    protected $description = 'Importar distritos, municípios e freguesias do Excel (CAOP)';

    private const CONEXAO = 'mysql2';

    /**
     * Remove espaços invisíveis e duplicados.
     */
    private function limpar(mixed $valor): string
    {
        $valor = str_replace("\xC2\xA0", ' ', (string) $valor); // non-breaking space
        $valor = preg_replace('/\s+/u', ' ', $valor);

        return trim($valor);
    }

    /**
     * Normaliza o nome de um cabeçalho (minúsculas, sem acentos).
     */
    private function normalizarCabecalho(mixed $valor): string
    {
        return Str::ascii(mb_strtolower($this->limpar($valor)));
    }

    public function handle(): int
    {
        $ficheiro = storage_path('app/areas-importe.xlsx');

        if (!file_exists($ficheiro)) {
            $this->error("Ficheiro não encontrado: {$ficheiro}");

            return self::FAILURE;
        }

        $sheet = IOFactory::load($ficheiro)->getSheet(0);
        $rows = $sheet->toArray(null, true, true, false);

        // Mapear colunas pelo cabeçalho
        $cabecalho = array_map(fn ($h) => $this->normalizarCabecalho($h), array_shift($rows));

        $iDistrito = array_search('distrito_ilha_dsg', $cabecalho, true);
        $iMunicipio = array_search('municipio_dsg', $cabecalho, true);
        $iFreguesia = array_search('freguesia_dsg', $cabecalho, true);

        if ($iDistrito === false || $iMunicipio === false || $iFreguesia === false) {
            $this->error('Não encontrei as colunas distrito_ilha_dsg, município_dsg e freguesia_dsg no cabeçalho.');
            $this->line('Cabeçalhos lidos: ' . implode(', ', $cabecalho));

            return self::FAILURE;
        }

        $this->line("Colunas: distrito={$iDistrito}, município={$iMunicipio}, freguesia={$iFreguesia}");

        if ($this->option('limpar')) {
            if (!$this->confirm('Isto apaga TODAS as freguesias, municípios e distritos. Continuar?')) {
                return self::FAILURE;
            }

            $db = DB::connection(self::CONEXAO);
            $db->statement('SET FOREIGN_KEY_CHECKS=0');
            $db->table('freguesias')->truncate();
            $db->table('municipios')->truncate();
            $db->table('distritos')->truncate();
            $db->statement('SET FOREIGN_KEY_CHECKS=1');

            $this->info('Tabelas limpas.');
        }

        $totais = ['distritos' => 0, 'municipios' => 0, 'freguesias' => 0, 'ignoradas' => 0];

        DB::connection(self::CONEXAO)->transaction(function () use ($rows, $iDistrito, $iMunicipio, $iFreguesia, &$totais) {
            foreach ($rows as $i => $row) {
                $nomeDistrito = $this->limpar($row[$iDistrito] ?? '');
                $nomeMunicipio = $this->limpar($row[$iMunicipio] ?? '');
                $nomeFreguesia = $this->limpar($row[$iFreguesia] ?? '');

                if ($nomeDistrito === '' || $nomeMunicipio === '' || $nomeFreguesia === '') {
                    $totais['ignoradas']++;
                    continue;
                }

                $distrito = Distrito::firstOrCreate(['nome' => $nomeDistrito]);
                $totais['distritos'] += (int) $distrito->wasRecentlyCreated;

                $municipio = Municipio::firstOrCreate([
                    'nome' => $nomeMunicipio,
                    'distrito_id' => $distrito->id,
                ]);
                $totais['municipios'] += (int) $municipio->wasRecentlyCreated;

                $freguesia = Freguesia::firstOrCreate([
                    'nome' => $nomeFreguesia,
                    'municipio_id' => $municipio->id,
                ]);
                $totais['freguesias'] += (int) $freguesia->wasRecentlyCreated;
            }
        });

        $this->info('Importação concluída!');
        $this->info("Novos distritos: {$totais['distritos']}");
        $this->info("Novos municípios: {$totais['municipios']}");
        $this->info("Novas freguesias: {$totais['freguesias']}");
        $this->info("Linhas ignoradas (vazias/lixo): {$totais['ignoradas']}");

        return self::SUCCESS;
    }
}