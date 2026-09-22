<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use RuntimeException;

class ImportSqlite extends Command
{
    protected $signature = 'database:import-sqlite {snapshot : Path to a SQLite backup}';

    protected $description = 'Import a SQLite snapshot into an empty, migrated PostgreSQL database';

    public function handle(): int
    {
        $path = realpath($this->argument('snapshot'));
        if (! $path || ! is_file($path)) {
            $this->error('SQLite snapshot not found.');

            return self::FAILURE;
        }
        $target = DB::connection();
        if ($target->getDriverName() !== 'pgsql') {
            $this->error('The default connection must be PostgreSQL.');

            return self::FAILURE;
        }
        $source = new PDO('sqlite:'.$path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $source->exec('PRAGMA query_only = ON');
        if ($source->query('PRAGMA integrity_check')->fetchColumn() !== 'ok'
            || $source->query('PRAGMA foreign_key_check')->fetch()) {
            throw new RuntimeException('SQLite integrity check failed.');
        }
        // Parents precede children. Operational tables are preserved too.
        $tables = ['users', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks',
            'jobs', 'job_batches', 'failed_jobs', 'tournaments', 'tournament_registrations',
            'card_listings', 'community_topics', 'community_comments', 'community_reactions'];
        $actual = $source->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);
        if (array_diff($actual, [...$tables, 'migrations']) || array_diff($tables, $actual)) {
            throw new RuntimeException('Unexpected SQLite schema; review the table list before importing.');
        }
        $target->transaction(function () use ($target, $source, $tables) {
            foreach ($tables as $table) {
                $target->statement('LOCK TABLE "'.$table.'" IN ACCESS EXCLUSIVE MODE');
                if ($target->table($table)->exists()) {
                    throw new RuntimeException("Target table {$table} is not empty. No data imported.");
                }
            }
            foreach ($tables as $table) {
                $columns = $target->select('SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = current_schema() AND table_name = ?', [$table]);
                $types = array_column($columns, 'data_type', 'column_name');
                $sourceColumns = array_column($source->query('PRAGMA table_info("'.$table.'")')->fetchAll(PDO::FETCH_ASSOC), 'name');
                if (array_diff($sourceColumns, array_keys($types)) || array_diff(array_keys($types), $sourceColumns)) {
                    throw new RuntimeException("Column mismatch in {$table}.");
                }
                $sourceRows = $source->query('SELECT * FROM "'.$table.'"')->fetchAll(PDO::FETCH_ASSOC);
                foreach (array_chunk($sourceRows, 250) as $chunk) {
                    foreach ($chunk as &$row) {
                        foreach ($row as $key => &$value) {
                            if ($types[$key] === 'boolean' && $value !== null) {
                                if (! in_array($value, [0, 1, '0', '1'], true)) {
                                    throw new RuntimeException("Invalid boolean in {$table}.{$key}");
                                }
                                $value = (bool) $value;
                            }
                        }
                        unset($value);
                    }
                    unset($row);
                    $target->table($table)->insert($chunk);
                }
                $targetRows = array_map(fn ($row) => (array) $row, $target->table($table)->get()->all());
                if ($this->fingerprints($sourceRows, $types) !== $this->fingerprints($targetRows, $types)) {
                    throw new RuntimeException("Data verification failed in {$table}; rolling back.");
                }
                if (isset($types['id'])) {
                    $sequence = $target->selectOne('SELECT pg_get_serial_sequence(?, ?) AS name', [$table, 'id'])->name;
                    if ($sequence) {
                        $max = $target->table($table)->max('id');
                        $target->select('SELECT setval(?::regclass, ?, ?)', [$sequence, max(1, (int) $max), $max !== null]);
                    }
                }
                $this->line($table.': '.count($sourceRows).' rows verified');
            }
        });
        $this->info('Import committed. All rows verified; sequences synchronized.');

        return self::SUCCESS;
    }

    private function fingerprints(array $rows, array $types): array
    {
        $hashes = [];
        foreach ($rows as $row) {
            ksort($row);
            foreach ($row as $key => &$value) {
                if ($value === null) {
                    continue;
                }
                $value = match ($types[$key]) {
                    'boolean' => in_array($value, [true, 1, '1', 't', 'true'], true) ? '1' : '0',
                    'numeric', 'real', 'double precision' => sprintf('%.10F', (float) $value),
                    default => (string) $value,
                };
            }
            unset($value);
            $hashes[] = hash('sha256', json_encode($row, JSON_THROW_ON_ERROR));
        }
        sort($hashes);

        return $hashes;
    }
}
