<?php

namespace Tests\Feature\Http\Controllers\UsersController;

use App\Mail\SlackInvite;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UpdateStatusTest extends TestCase
{
    use RefreshDatabase;

    public function provideValidSlackInviteEmailStatuses(): array
    {
        return [
            UserStatus::STATUS_INACTIVE => [
                'status' => UserStatus::STATUS_INACTIVE,
                'shouldSend' => true,
            ],
            UserStatus::STATUS_TERMINATED => [
                'status' => UserStatus::STATUS_TERMINATED,
                'shouldSend' => true,
            ],
            UserStatus::STATUS_INACTIVE_ABANDONED => [
                'status' => UserStatus::STATUS_INACTIVE_ABANDONED,
                'shouldSend' => true,
            ],
            UserStatus::STATUS_APPLICANT_ABANDONED => [
                'status' => UserStatus::STATUS_APPLICANT_ABANDONED,
                'shouldSend' => true,
            ],
            UserStatus::STATUS_APPLICANT_DENIED => [
                'status' => UserStatus::STATUS_APPLICANT_DENIED,
                'shouldSend' => true,
            ],
            UserStatus::STATUS_APPLICANT => [
                'status' => UserStatus::STATUS_APPLICANT,
                'shouldSend' => true,
            ],
            UserStatus::STATUS_ACTIVE => [
                'status' => UserStatus::STATUS_ACTIVE,
                'shouldSend' => false,
            ],
            UserStatus::STATUS_SUSPENDED => [
                'status' => UserStatus::STATUS_SUSPENDED,
                'shouldSend' => false,
            ],
            UserStatus::STATUS_HIATUS => [
                'status' => UserStatus::STATUS_HIATUS,
                'shouldSend' => false,
            ],
        ];
    }

    /**
     * @dataProvider provideValidSlackInviteEmailStatuses
     * @runInSeparateProcess
     */
    public function testItQueuesSlackInviteEmailWhenUserIsMovedFromValidStatusToActive(
        string $status,
        bool $shouldSend
    ): void
    {
        Mail::fake();

        $role = Role::query()->create(['name' => 'test-admin', 'description' => '']);
        RolePermission::query()->create([
            'role_id' => $role->id,
            'object' => 'users',
            'operation' => 'manage',
        ]);
        /** @var User $adminUser */
        $adminUser = User::factory()->create(['status' => UserStatus::STATUS_ACTIVE]);
        $adminUser->roles(true)->attach($role);

        $user = User::factory()->create(['status' => $status]);

        $response = $this->actingAs($adminUser)
            ->post("/users/{$user->id}/status", [
                'status_type' => UserStatus::STATUS_ACTIVE,
                'effective_date' => now()->format('Y-m-d'),
                'effective_date_ending' => now()->format('Y-m-d')
            ]);

        $response->assertOk();

        if ($shouldSend) {
            Mail::assertQueued(SlackInvite::class, function (SlackInvite $mail) use ($user) {
                return $user->id === $mail->user->id;
            });
            return;
        }

        Mail::assertNotQueued(SlackInvite::class);
    }
}
