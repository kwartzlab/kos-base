<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicInfoControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider publicUserCountData
     */
    public function test_public_user_count(int $activeUsers, int $expectedUserCount, int $hiatusUsers = 0): void
    {
        User::factory($activeUsers)->active()->create();
        User::factory($hiatusUsers)->hiatus()->create();

        $this->get('/api/public-info')
            ->assertStatus(200)
            ->assertJson([
                'number_of_active_users' => $expectedUserCount,
            ]);
    }

    /**
     * @return int[][]
     */
    public function publicUserCountData(): array
    {
        return [
            // [activeUsers, expectedUserCount, hiatusUsers = 0]

            // does not fail when there are 0 users
            [0, 0],

            // rolls over to the next multiple of ten when the count is greater by 5
            [14, 0],
            [15, 10],

            // hiatus members don't affect count
            [15, 10, 10],
        ];
    }
}
