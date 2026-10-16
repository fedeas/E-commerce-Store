<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdminUser extends Command
{
    protected $signature = 'user:make-admin {email} {--revoke : Remove admin access instead of granting it}';

    protected $description = 'Grant or revoke admin panel access for an existing user.';

    public function handle(): int
    {
        $user = User::query()->where('email', (string) $this->argument('email'))->first();

        if (! $user) {
            $this->error('User not found: ' . $this->argument('email'));

            return self::FAILURE;
        }

        $user->forceFill(['is_admin' => ! $this->option('revoke')])->save();

        $this->info($user->email . ($user->is_admin ? ' can now access the admin panel.' : ' no longer has admin access.'));

        return self::SUCCESS;
    }
}
