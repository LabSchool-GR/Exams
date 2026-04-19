<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallApplication extends Command
{
    protected $signature = 'app:install
                            {--admin-email=admin@exams.gr : The administrator email address}
                            {--admin-name=System Administrator : The administrator display name}
                            {--admin-password= : The administrator password}
                            {--demo : Seed the shared example dataset}
                            {--dev : Seed local-only development helpers}
                            {--skip-admin : Skip initial administrator setup}
                            {--skip-storage-link : Skip creation of the public storage link}
                            {--force : Pass --force to production-sensitive subcommands}';

    protected $description = 'Install the application with one explicit, repeatable setup flow';

    public function handle(): int
    {
        if (!$this->ensureApplicationKey()) {
            return self::FAILURE;
        }

        if ($this->call('migrate', $this->forwardForceOption()) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if ($this->call('db:seed', array_merge($this->forwardForceOption(), [
            '--class' => 'Database\\Seeders\\CoreSeeder',
        ])) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (!$this->option('skip-admin')) {
            if ($this->call('app:setup-admin', [
                '--email' => (string) $this->option('admin-email'),
                '--name' => (string) $this->option('admin-name'),
                '--password' => (string) ($this->option('admin-password') ?: ''),
            ]) !== self::SUCCESS) {
                return self::FAILURE;
            }
        }

        if ($this->option('demo')) {
            if ($this->call('db:seed', array_merge($this->forwardForceOption(), [
                '--class' => 'Database\\Seeders\\DemoSeeder',
            ])) !== self::SUCCESS) {
                return self::FAILURE;
            }
        }

        if ($this->option('dev')) {
            if ($this->call('db:seed', array_merge($this->forwardForceOption(), [
                '--class' => 'Database\\Seeders\\DevSeeder',
            ])) !== self::SUCCESS) {
                return self::FAILURE;
            }
        }

        if (!$this->option('skip-storage-link')) {
            $this->ensureStorageLink();
        }

        $this->newLine();
        $this->components->info('Application installation completed successfully.');

        return self::SUCCESS;
    }

    private function ensureApplicationKey(): bool
    {
        if (filled((string) config('app.key'))) {
            return true;
        }

        $this->components->info('Generating application key...');

        return $this->call('key:generate', ['--force' => true]) === self::SUCCESS;
    }

    private function ensureStorageLink(): void
    {
        $publicStoragePath = public_path('storage');

        if (is_link($publicStoragePath) || File::exists($publicStoragePath)) {
            $this->components->info('Public storage link already present, skipping.');

            return;
        }

        $this->call('storage:link');
    }

    private function forwardForceOption(): array
    {
        return $this->option('force') ? ['--force' => true] : [];
    }
}
