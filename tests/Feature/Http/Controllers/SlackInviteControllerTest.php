<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class SlackInviteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_not_found_if_slack_auto_invite_is_not_enabled(): void
    {
        config(['services.slack.auto_invite.enabled' => false]);

        $this->get('/slack/invite')->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_it_returns_unauthorized_without_a_user_token(): void
    {
        config(['services.slack.auto_invite.enabled' => true]);

        $this->get('/slack/invite')->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_returns_unauthorized_with_an_invalid_user_token(): void
    {
        config(['services.slack.auto_invite.enabled' => true]);

        $this->get('/slack/invite?t=0987654321234567890')->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_it_returns_unauthorized_with_a_valid_user_token_that_is_not_in_the_database(): void
    {
        config(['services.slack.auto_invite.enabled' => true]);
        $token = Crypt::encrypt(1234567890987654321);

        $this->get("/slack/invite?t={$token}")->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function provideNonActiveMemberStatuses(): array
    {
        return [
            UserStatus::STATUS_INACTIVE => ['status' => UserStatus::STATUS_INACTIVE],
            UserStatus::STATUS_SUSPENDED => ['status' => UserStatus::STATUS_SUSPENDED],
            UserStatus::STATUS_TERMINATED => ['status' => UserStatus::STATUS_TERMINATED],
            UserStatus::STATUS_INACTIVE_ABANDONED => ['status' => UserStatus::STATUS_INACTIVE_ABANDONED],
            UserStatus::STATUS_HIATUS => ['status' => UserStatus::STATUS_HIATUS],
            UserStatus::STATUS_APPLICANT_ABANDONED => ['status' => UserStatus::STATUS_APPLICANT_ABANDONED],
            UserStatus::STATUS_APPLICANT_DENIED => ['status' => UserStatus::STATUS_APPLICANT_DENIED],
            UserStatus::STATUS_APPLICANT => ['status' => UserStatus::STATUS_APPLICANT],
        ];
    }

    /** @dataProvider provideNonActiveMemberStatuses */
    public function test_it_returns_forbidden_with_a_valid_user_token_that_is_not_an_active_member(string $status): void
    {
        config(['services.slack.auto_invite.enabled' => true]);
        $user = User::factory()->create(['status' => $status]);
        $token = Crypt::encrypt($user->id);

        $this->get("/slack/invite?t={$token}")->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_it_redirects_to_the_configured_slack_invite_url_with_a_valid_user_token(): void
    {
        $redirectUrl = 'https://www.geralt.of/rivia';
        config(['services.slack.auto_invite.url' => $redirectUrl]);
        config(['services.slack.auto_invite.enabled' => true]);

        $user = User::factory()->create(['status' => UserStatus::STATUS_ACTIVE]);
        $token = Crypt::encrypt($user->id);

        $this->get("/slack/invite?t={$token}")->assertRedirect($redirectUrl);
    }
}
