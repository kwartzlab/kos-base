<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 50);
        $perPage = max(1, min($perPage, 200));

        return User::query()
            ->select([
                'id',
                'first_name',
                'last_name',
                'first_preferred',
                'last_preferred',
                'email',
                'status',
                'member_id',
            ])
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function show(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'first_preferred' => $user->first_preferred,
            'last_preferred' => $user->last_preferred,
            'email' => $user->email,
            'status' => $user->status,
            'member_id' => $user->member_id,
        ]);
    }
}
