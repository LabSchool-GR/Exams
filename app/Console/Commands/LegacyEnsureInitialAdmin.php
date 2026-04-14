<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class LegacyEnsureInitialAdmin extends Command
{
    protected $signature = 'user:ensure-initial-admin
                            {--email=admin@exams.gr : The administrator email address}
                            {--name=System Administrator : The administrator display name}
                            {--password= : The administrator password}';

    protected $description = 'Deprecated alias for app:setup-admin';

    public function handle(): int
    {
        $this->components->warn('The command "user:ensure-initial-admin" is deprecated. Use "app:setup-admin" instead.');

        return (int) $this->call('app:setup-admin', [
            '--email' => (string) $this->option('email'),
            '--name' => (string) $this->option('name'),
            '--password' => (string) ($this->option('password') ?: ''),
        ]);
    }
}
