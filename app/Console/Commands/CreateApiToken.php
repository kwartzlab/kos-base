<?php

namespace App\Console\Commands;

use App\Models\ApiToken;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CreateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-token:create
                            {name : Friendly name for the token}
                            {--expires= : Expiration date (Y-m-d or any strtotime format)}
                            {--abilities=* : Token abilities for future use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an API token for external services';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $expiresAt = null;
        $expiresInput = $this->option('expires');

        if ($expiresInput) {
            try {
                $expiresAt = Carbon::parse($expiresInput);
            } catch (\Throwable $e) {
                $this->error('Invalid expiration date. Example: 2025-12-31');

                return self::FAILURE;
            }

            if ($expiresAt->isPast()) {
                $this->error('Expiration date must be in the future.');

                return self::FAILURE;
            }
        }

        $abilities = array_values(array_filter($this->option('abilities')));
        $plainToken = bin2hex(random_bytes(32));

        $apiToken = ApiToken::create([
            'name' => $this->argument('name'),
            'token_hash' => ApiToken::hashToken($plainToken),
            'abilities' => $abilities ?: null,
            'expires_at' => $expiresAt,
        ]);

        $this->info('API token created.');
        $this->line('Token ID: ' . $apiToken->id);
        $this->line('Token: ' . $plainToken);

        if ($expiresAt !== null) {
            $this->line('Expires: ' . $expiresAt->toDateTimeString());
        }

        $this->line('Store this token securely. It will not be shown again.');

        return self::SUCCESS;
    }
}