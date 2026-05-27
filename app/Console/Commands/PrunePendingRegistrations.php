<?php

namespace App\Console\Commands;

use App\Models\PendingRegistration;
use Illuminate\Console\Command;

class PrunePendingRegistrations extends Command
{
    protected $signature = 'registrations:prune-pending';

    protected $description = 'Delete expired pending teacher registration requests';

    public function handle(): int
    {
        // Expired pending records cannot create accounts anymore and should not accumulate.
        $deleted = PendingRegistration::query()
            ->where('expires_at', '<=', now())
            ->delete();

        $this->components->info("Deleted {$deleted} expired pending registration(s).");

        return self::SUCCESS;
    }
}
