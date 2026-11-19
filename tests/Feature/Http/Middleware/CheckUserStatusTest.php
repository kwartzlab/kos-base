<?php

namespace Tests\Feature\Http\Middleware;

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Only active members and members on hiatus stay logged in. Anyone else is logged out on their
 * next page load, so a status change takes effect immediately.
 */
class CheckUserStatusTest extends TestCase
{
    use RefreshDatabase;

    public static function provideLoggedOutStatuses(): array
    {
        return [
            'inactive' => ['inactive'],
            'suspended' => ['suspended'],
            'terminated' => ['terminated'],
            'applicant' => ['applicant'],
        ];
    }

    /**
     * A member who is suspended or leaves while logged in must lose access straight away, not when
     * their session happens to expire.
     *
     * Expects: their next page redirects to /login with "Your account is no longer active." and they
     * are logged out.
     * Edge cases: inactive, suspended, terminated, and applicant (an applicant account must not get
     * into the members' area either).
     *
     * @dataProvider provideLoggedOutStatuses
     */
    public function test_a_member_who_is_no_longer_active_is_logged_out(string $status)
    {
        $user = $this->member('active');
        $this->actingAs($user)->get('/dashboard')->assertOk();

        $user->update(['status' => $status]);

        $this->get('/dashboard')->assertRedirect('/login')->assertSessionHas('error', 'Your account is no longer active.');
        $this->assertGuest();
    }

    /**
     * Members on hiatus keep website access (only their RFID keys stop, see KeysControllerTest).
     *
     * Expects: the dashboard loads and they stay logged in.
     */
    public function test_a_member_on_hiatus_stays_logged_in()
    {
        $this->actingAs($this->member('hiatus'))->get('/dashboard')->assertOk();
        $this->assertAuthenticated();
    }

    private function member(string $status): User
    {
        // Members on hiatus were active first; the page header shows the date they joined.
        $user = User::factory()->create(['status' => $status]);
        UserStatus::query()->create(['user_id' => $user->id, 'status' => 'active', 'updated_by' => 0, 'created_at' => now()->subYear()]);
        if ($status !== 'active') {
            UserStatus::query()->create(['user_id' => $user->id, 'status' => $status, 'updated_by' => 0]);
        }

        return $user;
    }
}
