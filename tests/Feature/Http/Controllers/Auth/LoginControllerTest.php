<?php

namespace Tests\Feature\Http\Controllers\Auth;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testItRendersTheLoginPage(): void
    {
        $this->get('/login')
            ->assertStatus(Response::HTTP_OK)
            ->assertSeeText('Sign in to start your session');
    }

    public function testItLogsInAValidUser(): void
    {
        $user = factory(User::class)
            ->create(['email' => 'geralt@rivia.of', 'password' => Hash::make('winds_howling')]);

        $this->post('/login', ['email' => 'geralt@rivia.of', 'password' => 'winds_howling'])
            ->assertRedirect('/dashboard');

        $this->assertEquals($user->id, Auth::user()->id);
    }

    public function testItRedirectsAnInvalidUserBackToLogin(): void
    {
        factory(User::class)
            ->create(['email' => 'geralt@rivia.of', 'password' => Hash::make('winds_howling')]);

        $this->post('/login', ['email' => 'yennefer@vengerberg.of', 'password' => 'damn_a_storm'])
            ->assertRedirect('/');

        $this->assertNull(Auth::user());
    }
}
