<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class ImportLegacySqlite extends Command
{
    protected $signature = 'eldritch:import-sqlite
        {--source= : Caminho para o arquivo SQLite antigo}
        {--connection= : Conexão de destino; por padrão usa DB_CONNECTION}
        {--replace : Apaga os dados de domínio existentes antes da importação}';

    protected $description = 'Importa os dados do protótipo SQLite para o banco configurado';

    /**
     * Tabelas ordenadas para respeitar suas chaves estrangeiras.
     *
     * @var list<string>
     */
    private array $tables = [
        'users',
        'tournaments',
        'tournament_registrations',
        'card_listings',
        'community_topics',
        'community_comments',
        'community_reactions',
    ];

    public function handle(): int
    {
        $sourcePath = $this->resolveSourcePath();
        $targetName = (string) ($this->option('connection') ?: config('database.default'));

        if (! is_file($sourcePath)) {
            $this->components->error("Arquivo SQLite não encontrado: {$sourcePath}");

            return self::FAILURE;
        }

        if ($targetName === 'legacy_sqlite') {
            $this->components->error('A conexão de destino não pode ser legacy_sqlite.');

            return self::FAILURE;
        }

        config(['database.connections.legacy_sqlite.database' => $sourcePath]);
        DB::purge('legacy_sqlite');

        try {
            $source = DB::connection('legacy_sqlite');
            $target = DB::connection($targetName);
            $tables = $this->importableTables($source, $target);

            if ($tables === []) {
                throw new RuntimeException('Nenhuma tabela de domínio compatível foi encontrada no SQLite.');
            }

            if (! in_array('users', $tables, true)) {
                throw new RuntimeException('A tabela users é obrigatória para preservar os relacionamentos.');
            }

            if ($this->targetHasData($target, $this->tables) && ! $this->option('replace')) {
                $this->components->error(
                    'O banco de destino já contém dados. Faça um backup e execute novamente com --replace se deseja substituí-los.'
                );

                return self::FAILURE;
            }

            $counts = $target->transaction(function () use ($source, $target, $tables): array {
                if ($this->option('replace')) {
                    foreach (array_reverse($this->tables) as $table) {
                        $target->table($table)->delete();
                    }
                }

                $counts = [];

                foreach ($tables as $table) {
                    $counts[$table] = $this->copyTable($source, $target, $table);
                }

                $this->resetPostgresSequences($target, $tables);

                return $counts;
            });

            $this->newLine();
            $this->components->info('Importação concluída com sucesso.');
            $this->table(
                ['Tabela', 'Registros importados'],
                collect($counts)->map(fn (int $count, string $table) => [$table, $count])->values()->all()
            );
            $this->components->warn('Os arquivos enviados devem ser copiados separadamente de storage/app/public.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->components->error('Falha na importação: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            DB::purge('legacy_sqlite');
        }
    }

    private function resolveSourcePath(): string
    {
        $configuredPath = (string) ($this->option('source') ?: config('database.connections.legacy_sqlite.database'));

        if ($configuredPath === '') {
            return database_path('database.sqlite');
        }

        if (preg_match('/^(?:[A-Za-z]:[\\\\\/]|[\\\\\/])/', $configuredPath) === 1) {
            return $configuredPath;
        }

        return base_path($configuredPath);
    }

    /**
     * @return list<string>
     */
    private function importableTables(Connection $source, Connection $target): array
    {
        return array_values(array_filter($this->tables, function (string $table) use ($source, $target): bool {
            if (! Schema::connection($target->getName())->hasTable($table)) {
                throw new RuntimeException("A tabela {$table} ainda não existe no destino. Execute as migrations primeiro.");
            }

            if (! Schema::connection($source->getName())->hasTable($table)) {
                $this->components->warn("Tabela {$table} ausente no SQLite; ela será ignorada.");

                return false;
            }

            return true;
        }));
    }

    /**
     * @param  list<string>  $tables
     */
    private function targetHasData(Connection $target, array $tables): bool
    {
        foreach ($tables as $table) {
            if ($target->table($table)->exists()) {
                return true;
            }
        }

        return false;
    }

    private function copyTable(Connection $source, Connection $target, string $table): int
    {
        $sourceColumns = Schema::connection($source->getName())->getColumnListing($table);
        $targetColumns = Schema::connection($target->getName())->getColumnListing($table);
        $columns = array_values(array_intersect($sourceColumns, $targetColumns));

        if ($columns === []) {
            return 0;
        }

        $count = 0;

        $source->table($table)
            ->select($columns)
            ->orderBy('id')
            ->chunk(250, function ($rows) use ($target, $table, &$count): void {
                $payload = $rows->map(fn (object $row) => (array) $row)->all();

                if ($payload !== []) {
                    $target->table($table)->insert($payload);
                    $count += count($payload);
                }
            });

        return $count;
    }

    /**
     * @param  list<string>  $tables
     */
    private function resetPostgresSequences(Connection $target, array $tables): void
    {
        if ($target->getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($tables as $table) {
            $quotedTable = $target->getQueryGrammar()->wrapTable($table);
            $target->statement(
                "SELECT setval(pg_get_serial_sequence(?, 'id'), COALESCE(MAX(id), 1), MAX(id) IS NOT NULL) FROM {$quotedTable}",
                [$table]
            );
        }
    }
}
