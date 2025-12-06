<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RememberTokenService;

class PurgeExpiredRememberTokens extends Command
{
    protected $signature = 'remember:purge-expired';
    protected $description = 'Purge expired remember me tokens';

    public function handle()
    {
        app(RememberTokenService::class)->purgeExpired();
        $this->info('Expired remember tokens purged.');
    }
}
