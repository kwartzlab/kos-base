<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class PublicInfoController extends Controller
{
    /**
     * Share information publically about Kwartzlab as json
     */
    public function index(): JsonResponse
    {
        $user_count = \App\Models\User::where('status', 'active')->count();

        $data = [
            'number_of_active_users' => $user_count,
        ];

        return response()->json($data);
    }
}
