<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserStatusesController extends Controller
{
    public function latest()
    {
        $status = UserStatus::query()->orderByDesc('id')->first(['id', 'created_at']);

        if ($status === null) {
            return response()->json(['id' => 0]);
        }

        return response()->json([
            'id' => $status->id,
            'created_at' => $status->created_at,
        ]);
    }

    public function index(Request $request)
    {
        $status_id = (int) $request->query('status_id', 0);

        $status = UserStatus::query()
            ->select([
                'id',
                'user_id',
                'status',
                'note',
                'updated_by',
                'created_at',
            ])
            ->where('id', '>=', $status_id)
            ->orderBy('id')
            ->first();

        if ($status === null) {
            return response()->json(['message' => 'No pending user statuses.'], 204);
        }

        return response()->json($status);
    }

    public function upcoming(Request $request)
    {
        $days = max(1, min((int) $request->query('days', 7), 90));
        $now = Carbon::now();

        $statuses = UserStatus::query()
            ->select(['id', 'user_id', 'status', 'note', 'updated_by', 'created_at'])
            ->where('created_at', '>', $now)
            ->where('created_at', '<=', $now->copy()->addDays($days))
            ->orderBy('created_at')
            ->get();

        return response()->json($statuses);
    }
}
