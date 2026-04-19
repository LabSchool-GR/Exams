<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\ExecutableFinder;

class SchemaDumpCommand extends Command
{
    protected $signature = 'app:schema-dump
                            {--database=* : Database connections to dump. Defaults to the current default connection}';

    protected $description = 'Create committed schema dump files without pruning historical migrations';

    public function handle(): int
    {
        $databases = $this->requestedDatabases();

        foreach ($databases as $database) {
            $connection = DB::connection($database);

            $this->prepareSchemaDumpEnvironment($connection->getDriverName());

            $this->components->info("Dumping schema for [{$database}]...");

            if ($this->call('schema:dump', ['--database' => $database]) !== self::SUCCESS) {
                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->components->info('Schema dump refresh completed successfully.');

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function requestedDatabases(): array
    {
        $databases = array_values(array_filter(array_map(
            static fn (mixed $database): string => trim((string) $database),
            (array) $this->option('database')
        )));

        if ($databases !== []) {
            return $databases;
        }

        return [(string) config('database.default')];
    }

    private function prepareSchemaDumpEnvironment(string $driver): void
    {
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->ensureExecutablesAvailable(['mysqldump', 'mysql'], [
                'C:\\xampp\\mysql\\bin',
            ]);

            return;
        }

        if ($driver === 'sqlite') {
            $this->ensureExecutablesAvailable(['sqlite3'], []);
        }
    }

    /**
     * @param  list<string>  $executables
     * @param  list<string>  $candidateDirectories
     */
    private function ensureExecutablesAvailable(array $executables, array $candidateDirectories): void
    {
        $finder = new ExecutableFinder;

        $allAvailable = collect($executables)->every(
            fn (string $executable): bool => $finder->find($executable) !== null
        );

        if ($allAvailable) {
            return;
        }

        $currentPath = (string) getenv('PATH');
        $existingCandidateDirectories = array_values(array_filter(
            $candidateDirectories,
            static fn (string $directory): bool => is_dir($directory)
        ));

        if ($existingCandidateDirectories !== []) {
            $newPath = implode(PATH_SEPARATOR, array_merge($existingCandidateDirectories, [$currentPath]));
            putenv('PATH=' . $newPath);
            $_SERVER['PATH'] = $newPath;
            $_ENV['PATH'] = $newPath;
        }

        $stillMissing = array_values(array_filter(
            $executables,
            static fn (string $executable): bool => $finder->find($executable) === null
        ));

        if ($stillMissing === []) {
            return;
        }

        $missingList = implode(', ', $stillMissing);

        throw new \RuntimeException(
            "Missing required schema dump executable(s): {$missingList}. Install them or add them to PATH."
        );
    }
}
