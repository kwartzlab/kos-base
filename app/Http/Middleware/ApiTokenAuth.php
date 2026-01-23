<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if ($token === null) {
            $token = $request->header('X-Api-Token');
        }

        if (! $token) {
            return response()->json(['message' => 'API token required.'], 401);
        }

        $tokenHash = ApiToken::hashToken($token);
        $apiToken = ApiToken::where('token_hash', $tokenHash)->first();

        if ($apiToken === null || ! $apiToken->isActive()) {
            return response()->json(['message' => 'Invalid API token.'], 401);
        }

        $apiToken->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('api_token', $apiToken);

        return $next($request);
    }
}
