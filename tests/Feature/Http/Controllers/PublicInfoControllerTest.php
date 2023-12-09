<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicInfoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testItReturnsActiveNumberOfUsers()
    {
        // return 0 if no active users
        $this->get('/api/public-info')
            ->assertStatus(200)
            ->assertJson([
                'number_of_active_users' => 0,
            ]);

        // return count if active users exist
        User::factory()->active()->create();
        $this->get('/api/public-info')
            ->assertStatus(200)
            ->assertJson([
                'number_of_active_users' => 1,
            ]);

        // do not count non-active users
        User::factory()->hiatus()->create();
        $this->get('/api/public-info')
            ->assertStatus(200)
            ->assertJson([
                'number_of_active_users' => 1,
            ]);
    }
}
