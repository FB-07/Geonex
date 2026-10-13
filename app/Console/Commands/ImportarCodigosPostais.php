<?php

namespace App\Console\Commands;

use App\Models\CodigoPostal;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportarCodigosPostais extends Command
{
    protected $signature = 'codigos-postais:importar
        {--ficheiro=cp.csv : Ficheiro dentro de storage/app}
        {--limpar : Apaga os códigos postais antes de importar}';

    protected $description = 'Importar códigos postais e ligá-los às freguesias já existentes';

    private const CONEXAO = 'mysql2';
    
    private function corrigirEncoding(string $texto): string
    {
        if (!mb_check_encoding($texto, 'UTF-8')) {
            return mb_convert_encoding($texto, 'UTF-8', 'Windows-1252');
        }

        if ($texto === '' || !preg_match('/[\x{0080}-\x{FFFF}]/u', $texto)) {
            return $texto;
        }

        $bytes = '';

        foreach (mb_str_split($texto) as $caractere) {
            $codigo = mb_ord($caractere);

            if ($codigo < 256) {
                $bytes .= chr($codigo);
            } else {
                $bytes .= mb_convert_encoding($caractere, 'Windows-1252', 'UTF-8');
            }
        }

        return mb_check_encoding($bytes, 'UTF-8') ? $bytes : $texto;
    }

    private function limpar(mixed $valor): string
    {
        $valor = $this->corrigirEncoding((string) $valor);
        $valor = str_replace("\xC2\xA0", ' ', $valor); // non-breaking space
        $valor = preg_replace('/\s+/u', ' ', $valor);

        return trim($valor);
    }

    private function chave(string ...$partes): string
    {
        return implode('|', array_map(function (string $parte) {
            $parte = Str::ascii(mb_strtolower($parte));

            return trim(preg_replace('/\s+/', ' ', $parte));
        }, $partes));
    }

    private function formatarCodigo(mixed $valor): ?string
    {
        $digitos = preg_replace('/\D/', '', (string) $valor);

        return strlen($digitos) === 7 ? $digitos : null;
    }


    private function resolverUniao(string $concelho, string $freguesia, array $porMunicipio): ?int
    {
        if (!preg_match('/^uni[aã]o d[ae]s? freguesias? d[eao]s? (.+)$/iu', $freguesia, $m)) {
            return null;
        }

        foreach (preg_split('/\s*,\s*|\s+e\s+/iu', $m[1]) as $parte) {
            $ids = $porMunicipio[$this->chave($concelho, trim($parte))] ?? [];

            if (count($ids) === 1) {
                return $ids[0];
            }
        }

        return null;
    }

    /**
     * Lê o ficheiro linha a linha (CSV em streaming, deteta o separador;
     * outros formatos via PhpSpreadsheet). A primeira linha devolvida é o cabeçalho.
     */
    private function lerLinhas(string $ficheiro): Generator
    {
        if (strtolower(pathinfo($ficheiro, PATHINFO_EXTENSION)) !== 'csv') {
            $sheet = IOFactory::load($ficheiro)->getSheet(0);

            foreach ($sheet->toArray(null, true, true, false) as $linha) {
                yield $linha;
            }

            return;
        }

        $h = fopen($ficheiro, 'r');

        // BOM UTF-8
        $inicio = fread($h, 3) === "\xEF\xBB\xBF" ? 3 : 0;
        fseek($h, $inicio);

        // Detetar separador pela primeira linha
        $primeira = (string) fgets($h);
        $delimitador = ';';
        $max = -1;

        foreach ([';', ',', "\t", '|'] as $candidato) {
            $n = substr_count($primeira, $candidato);

            if ($n > $max) {
                $max = $n;
                $delimitador = $candidato;
            }
        }

        fseek($h, $inicio);

        while (($linha = fgetcsv($h, 0, $delimitador, '"', '\\')) !== false) {
            yield $linha;
        }

        fclose($h);
    }

    public function handle(): int
    {
        ini_set('memory_limit', '512M');

        // O query log guarda todas as queries (e os seus bindings) em memória
        DB::connection(self::CONEXAO)->disableQueryLog();

        $ficheiro = storage_path('app/' . $this->option('ficheiro'));

        $this->line("A ler: {$ficheiro}");

        if (!file_exists($ficheiro)) {
            $this->error('Ficheiro não encontrado.');
            $this->line('Ficheiros em storage/app: ' . implode(', ', array_map('basename', glob(storage_path('app/*')) ?: [])));

            return self::FAILURE;
        }

        // Carregar todas as freguesias com município e distrito
        $freguesias = DB::connection(self::CONEXAO)
            ->table('freguesias as f')
            ->join('municipios as m', 'm.id', '=', 'f.municipio_id')
            ->join('distritos as d', 'd.id', '=', 'm.distrito_id')
            ->select('f.id', 'f.nome as freguesia', 'm.nome as municipio', 'd.nome as distrito')
            ->get();

        $this->line('Freguesias na BD: ' . $freguesias->count());

        if ($freguesias->isEmpty()) {
            $this->error('Não há freguesias na BD. Corre primeiro: php artisan localidades:importar');

            return self::FAILURE;
        }

        $porCompleta = [];
        $porMunicipio = [];

        foreach ($freguesias as $f) {
            $porCompleta[$this->chave($f->distrito, $f->municipio, $f->freguesia)] = $f->id;
            $porMunicipio[$this->chave($f->municipio, $f->freguesia)][] = $f->id;
        }

        if ($this->option('limpar')) {
            if (!$this->confirm('Isto apaga TODOS os códigos postais. Continuar?')) {
                return self::FAILURE;
            }

            DB::connection(self::CONEXAO)->table('codigos_postais')->truncate();
            $this->info('Tabela limpa.');
        }

        $paraGuardar = [];
        $naoEncontrados = [];
        $invalidos = 0;
        $aproximadas = 0;
        $lidas = 0;
        $cabecalho = null;
        $iCodigo = $iDistrito = $iConcelho = $iFreguesia = null;

        foreach ($this->lerLinhas($ficheiro) as $row) {
            // Primeira linha = cabeçalho
            if ($cabecalho === null) {
                $cabecalho = array_map(fn ($h) => $this->chave($this->limpar($h)), $row);

                $iCodigo = array_search('codigopostal', $cabecalho, true);
                $iDistrito = array_search('distrito', $cabecalho, true);
                $iConcelho = array_search('concelho', $cabecalho, true);
                $iFreguesia = array_search('freguesia', $cabecalho, true);

                if (in_array(false, [$iCodigo, $iDistrito, $iConcelho, $iFreguesia], true)) {
                    $this->error('Não encontrei as colunas CodigoPostal, Distrito, Concelho e Freguesia no cabeçalho.');
                    $this->line('Cabeçalhos lidos: ' . implode(' / ', $cabecalho));

                    return self::FAILURE;
                }

                $this->line("Colunas: código={$iCodigo}, distrito={$iDistrito}, concelho={$iConcelho}, freguesia={$iFreguesia}");

                continue;
            }

            $lidas++;

            if ($lidas % 50000 === 0) {
                $this->line("... {$lidas} linhas lidas");
            }

            $codigo = $this->formatarCodigo($row[$iCodigo] ?? '');
            $distrito = $this->limpar($row[$iDistrito] ?? '');
            $concelho = $this->limpar($row[$iConcelho] ?? '');
            $freguesia = $this->limpar($row[$iFreguesia] ?? '');

            if ($codigo === null || $distrito === '' || $concelho === '' || $freguesia === '') {
                $invalidos++;
                continue;
            }

            $freguesiaId = $porCompleta[$this->chave($distrito, $concelho, $freguesia)] ?? null;

            // Fallback 1: distrito escrito de forma diferente, mas município+freguesia únicos
            if ($freguesiaId === null) {
                $candidatas = $porMunicipio[$this->chave($concelho, $freguesia)] ?? [];

                if (count($candidatas) === 1) {
                    $freguesiaId = $candidatas[0];
                }
            }

            // Fallback 2: "União das freguesias de A e B" -> liga a A (ou B...)
            if ($freguesiaId === null) {
                $freguesiaId = $this->resolverUniao($concelho, $freguesia, $porMunicipio);

                if ($freguesiaId !== null) {
                    $aproximadas++;
                }
            }

            if ($freguesiaId === null) {
                $k = $distrito . ' | ' . $concelho . ' | ' . $freguesia;
                $naoEncontrados[$k] = ($naoEncontrados[$k] ?? 0) + 1;
                continue;
            }

            $paraGuardar[$codigo] = $freguesiaId;
        }

        if ($cabecalho === null) {
            $this->error('O ficheiro está vazio ou não foi possível lê-lo.');

            return self::FAILURE;
        }

        $this->line("Linhas lidas: {$lidas}. A guardar " . count($paraGuardar) . ' códigos postais...');

        DB::connection(self::CONEXAO)->transaction(function () use (&$paraGuardar) {
            foreach (array_chunk($paraGuardar, 500, true) as $bloco) {
                $linhas = [];

                foreach ($bloco as $codigo => $freguesiaId) {
                    $linhas[] = ['codigo' => (string) $codigo, 'freguesia_id' => $freguesiaId];
                }

                CodigoPostal::upsert($linhas, ['codigo'], ['freguesia_id']);
            }
        });

        $totalNaoLigados = array_sum($naoEncontrados);

        $this->info('Importação concluída!');
        $this->info('Códigos postais ligados: ' . count($paraGuardar));
        $this->info("  (dos quais ligados por aproximação a uma freguesia da união: {$aproximadas})");
        $this->info("Linhas sem freguesia correspondente: {$totalNaoLigados} (" . count($naoEncontrados) . ' freguesias diferentes)');
        $this->info("Linhas inválidas/vazias: {$invalidos}");

        if ($naoEncontrados) {
            arsort($naoEncontrados);

            $relatorio = storage_path('app/codigos-postais-nao-ligados.csv');
            $f = fopen($relatorio, 'w');
            fwrite($f, "\xEF\xBB\xBF"); 
            fputcsv($f, ['distrito | concelho | freguesia', 'linhas'], ';', '"', '\\');

            foreach ($naoEncontrados as $nome => $total) {
                fputcsv($f, [$nome, $total], ';', '"', '\\');
            }

            fclose($f);

            $this->warn("Relatório das não ligadas: {$relatorio}");
        }

        return self::SUCCESS;
    }
}