<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class PublicInfoController extends Controller
{
    /**
     * Share information publicly about Kwartzlab as json
     */
    public function index(): JsonResponse
    {
        $data = [
            'number_of_active_users' => $this->getPublicUserCount(),
        ];

        return response()->json($data);
    }

    /**
     * We want to share the number of active users publicly, but we don't want people to obsess over the ebbs and flows of our active member count.
     * We'll remove 10 from the count and round to the nearest 10, this will give us a conservative but useful number.
     *
     * Justification for the obfuscation: https://kwartzlab.slack.com/archives/CBT57TWN4/p1702126237514579
     */
    public function getPublicUserCount(): int
    {
        $count = User::where('status', 'active')->count();
        $count = max(0, $count - 10); // Ensure count is not negative

        return round($count / 10) * 10;
    }
}
