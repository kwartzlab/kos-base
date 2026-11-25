<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Gatekeeper;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The gatekeeper (door or tool) dashboard. Covers the heartbeat indicator that tells admins whether
 * a device is still checking in.
 */
class GatekeepersControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $superuser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = new Role(['name' => 'Superuser', 'description' => '']);
        $role->id = 1;
        $role->save();

        $this->superuser = User::factory()->active()->create();
        UserStatus::query()->create(['user_id' => $this->superuser->id, 'status' => UserStatus::STATUS_ACTIVE, 'updated_by' => 0]);
        UserRole::query()->create(['user_id' => $this->superuser->id, 'role_id' => $role->id]);
    }

    public static function provideHeartbeats(): array
    {
        return [
            'seen just now' => ['minutesAgo' => 0, 'expected' => 'success'],
            'seen 20 minutes ago' => ['minutesAgo' => 20, 'expected' => 'success'],
            'seen 21 minutes ago' => ['minutesAgo' => 21, 'expected' => 'danger'],
            'seen yesterday' => ['minutesAgo' => 24 * 60, 'expected' => 'danger'],
        ];
    }

    /**
     * The dashboard shows a device as offline once it has not checked in for more than 20 minutes.
     *
     * Expects: "success" (green) up to and including 20 minutes, "danger" (red) after.
     * Edge cases: exactly 20 and 21 minutes, with the clock frozen, so changing "> 20" to ">= 20"
     * or moving the limit is caught.
     * Upgrade risk: Carbon 3 (Laravel 11) makes now()->diffInMinutes($past) negative, which would
     * show every device as online. The 21-minute and yesterday cases catch that.
     *
     * @dataProvider provideHeartbeats
     */
    public function test_the_dashboard_heartbeat_shows_whether_the_device_is_checking_in(int $minutesAgo, string $expected)
    {
        // Freeze the clock so the 20 and 21 minute cases sit exactly on either side of the limit.
        $this->travelTo(now()->startOfSecond());
        $gatekeeper = Gatekeeper::factory()->create(['last_seen' => now()->subMinutes($minutesAgo)]);

        $this->actingAs($this->superuser)
            ->get("/gatekeepers/{$gatekeeper->id}/dashboard")
            ->assertOk()
            ->assertViewHas('heartbeat_status', $expected);
    }

    /**
     * A newly added device has never checked in, so there is no time to compare.
     *
     * Expects: "warning" (yellow) instead of an error or a green light.
     */
    public function test_the_dashboard_heartbeat_shows_a_warning_for_a_device_never_seen()
    {
        $gatekeeper = Gatekeeper::factory()->create(['last_seen' => null]);

        $this->actingAs($this->superuser)
            ->get("/gatekeepers/{$gatekeeper->id}/dashboard")
            ->assertViewHas('heartbeat_status', 'warning');
    }
}
