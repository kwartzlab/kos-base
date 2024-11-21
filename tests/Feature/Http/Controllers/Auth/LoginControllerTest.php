<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_renders_the_login_page(): void
    {
        $this->get('/login')
            ->assertStatus(Response::HTTP_OK)
            ->assertSeeText('Sign in to start your session');
    }

    public function test_it_logs_in_a_valid_user(): void
    {
        $user = User::factory()
            ->create(['email' => 'geralt@rivia.of', 'password' => Hash::make('winds_howling')]);

        $this->post('/login', ['email' => 'geralt@rivia.of', 'password' => 'winds_howling'])
            ->assertRedirect('/dashboard');

        $this->assertEquals($user->id, Auth::user()->id);
    }

    public function test_it_redirects_an_invalid_user_back_to_login(): void
    {
        User::factory()
            ->create(['email' => 'geralt@rivia.of', 'password' => Hash::make('winds_howling')]);

        $this->post('/login', ['email' => 'yennefer@vengerberg.of', 'password' => 'damn_a_storm'])
            ->assertRedirect('/');

        $this->assertNull(Auth::user());
    }
}
