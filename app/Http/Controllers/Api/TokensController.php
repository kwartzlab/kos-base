<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TokensController extends Controller
{
    public function show(Request $request)
    {
        $apiToken = $request->attributes->get('api_token');

        return response()->json([
            'name' => $apiToken->name,
            'abilities' => $apiToken->abilities ?? [],
            'last_used_at' => optional($apiToken->last_used_at)->toDateTimeString(),
        ]);
    }
}
