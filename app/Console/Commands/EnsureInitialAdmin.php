<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureInitialAdmin extends Command
{
    protected $signature = 'app:setup-admin
                            {--email=admin@exams.gr : The administrator email address}
                            {--name=System Administrator : The administrator display name}
                            {--password= : The administrator password}';

    protected $description = 'Create or update the initial verified administrator account';

    public function handle(): int
    {
        $email = (string) $this->option('email');
        $name = (string) $this->option('name');
        $password = (string) ($this->option('password') ?: '');

        if ($password === '') {
            $password = (string) $this->secret('Administrator password');
            $passwordConfirmation = (string) $this->secret('Confirm administrator password');

            if ($password === '' || $passwordConfirmation === '') {
                $this->components->error('Password entry was cancelled or left empty.');

                return self::FAILURE;
            }

            if ($password !== $passwordConfirmation) {
                $this->components->error('The password confirmation does not match.');

                return self::FAILURE;
            }
        }

        if (strlen($password) < 12) {
            $this->components->error('Use a password with at least 12 characters for the initial administrator.');

            return self::FAILURE;
        }

        $user = User::query()->firstOrNew([
            'email' => $email,
        ]);

        // Keep the initial admin setup idempotent for repeatable deployments.
        $user->name = $name;
        $user->password = Hash::make($password);
        $user->role = 'admin';
        $user->email_verified_at = now();
        $user->save();

        $action = $user->wasRecentlyCreated ? 'created' : 'updated';

        $this->components->info("Initial administrator {$action} successfully.");
        $this->line("Email: {$user->email}");
        $this->line("Name: {$user->name}");
        $this->line("Role: {$user->role}");
        $this->line('Email verified: yes');

        return self::SUCCESS;
    }
}
