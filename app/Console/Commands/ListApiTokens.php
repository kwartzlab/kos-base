<?php

namespace App\Console\Commands;

use App\Models\ApiToken;
use Illuminate\Console\Command;

class ListApiTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-token:list {--revoked : Show revoked tokens only} {--active : Show active tokens only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List API tokens';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $query = ApiToken::query()->orderBy('id');

        if ($this->option('revoked')) {
            $query->whereNotNull('revoked_at');
        } elseif ($this->option('active')) {
            $query->whereNull('revoked_at');
        }

        $tokens = $query->get([
            'id',
            'name',
            'abilities',
            'last_used_at',
            'expires_at',
            'revoked_at',
            'created_at',
        ]);

        if ($tokens->isEmpty()) {
            $this->info('No API tokens found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Abilities', 'Last Used', 'Expires', 'Revoked', 'Created'],
            $tokens->map(function (ApiToken $token) {

                return [
                    $token->id,
                    $token->name,
                    $token->abilities ? implode(',', $token->abilities) : '',
                    optional($token->last_used_at)->toDateTimeString(),
                    optional($token->expires_at)->toDateTimeString(),
                    optional($token->revoked_at)->toDateTimeString(),
                    optional($token->created_at)->toDateTimeString(),
                ];
            })->all()
        );

        return self::SUCCESS;
    }
}
