<?php

namespace App\Console\Commands;

use App\Models\ApiToken;
use Illuminate\Console\Command;

class RevokeApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-token:revoke {id : Token ID to revoke}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoke an API token by ID';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tokenId = (int) $this->argument('id');
        $token = ApiToken::find($tokenId);

        if ($token === null) {
            $this->error('API token not found.');

            return self::FAILURE;
        }

        if ($token->revoked_at !== null) {
            $this->info('API token is already revoked.');

            return self::SUCCESS;
        }

        $token->forceFill(['revoked_at' => now()])->save();

        $this->info('API token revoked.');

        return self::SUCCESS;
    }
}