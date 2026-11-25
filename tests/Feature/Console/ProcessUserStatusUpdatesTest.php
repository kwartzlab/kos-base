<?php

namespace Tests\Feature\Console;

use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The nightly process:userstatus job. Status changes are recorded in advance with an effective date;
 * the job brings each member's current status up to date once that date arrives.
 *
 * How the code decides: a member's "current" status change is the latest one dated before midnight
 * tonight (User::current_status). If users.status differs from it, the job copies it over and sets
 * date_admitted or date_withdrawn where needed (UserStatusTrait::check_current_userstatus).
 *
 * Note: current_status reads the real clock (strtotime), not Laravel's test clock, so these tests
 * place their dates relative to now() instead of freezing time.
 */
class ProcessUserStatusUpdatesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The main job of the task: an approved applicant becomes a member on their admission date.
     *
     * Expects: status becomes active and date_admitted is the date of the active status change
     * (not the date the job ran).
     */
    public function test_an_applicant_becomes_active_and_gets_an_admission_date()
    {
        $user = $this->userWithHistory('applicant', [['applicant', now()->subDays(14)], ['active', now()->subDay()]]);

        $this->artisan('process:userstatus')->assertSuccessful();

        $user->refresh();
        $this->assertSame('active', $user->status);
        $this->assertSame(now()->subDay()->toDateString(), $user->date_admitted->toDateString());
    }

    /**
     * Changes take effect on their day, not at their exact time: the job runs once a night, so a
     * change dated 23:59 today must apply in tonight's run.
     *
     * Edge case: the latest possible time today. Paired with the midnight-tomorrow test below, this
     * pins the cut-off to exactly midnight.
     */
    public function test_a_change_dated_later_today_already_applies()
    {
        $user = $this->userWithHistory('active', [['active', now()->subYear()], ['hiatus', now()->endOfDay()->subMinute()]]);

        $this->artisan('process:userstatus');

        $this->assertSame('hiatus', $user->fresh()->status);
    }

    /**
     * Admins record changes ahead of time (for example a hiatus starting next week). Those must not
     * apply early.
     *
     * Expects: the member stays active until the hiatus date.
     */
    public function test_a_change_dated_in_the_future_waits()
    {
        $user = $this->userWithHistory('active', [['active', now()->subYear()], ['hiatus', now()->addDays(3)]]);

        $this->artisan('process:userstatus');

        $this->assertSame('active', $user->fresh()->status);
    }

    /**
     * The other side of the cut-off: a change dated exactly 00:00 tomorrow belongs to tomorrow.
     *
     * Edge case: the first possible moment of tomorrow. Without this, the cut-off could move a day
     * or more later and the "future" test (3 days ahead) would still pass.
     */
    public function test_a_change_dated_midnight_tomorrow_waits()
    {
        $user = $this->userWithHistory('active', [['active', now()->subYear()], ['hiatus', now()->addDay()->startOfDay()]]);

        $this->artisan('process:userstatus');

        $this->assertSame('active', $user->fresh()->status);
    }

    /**
     * date_admitted records when someone first became a member. Coming back from a hiatus is also a
     * change to active, but must not overwrite it; only applicant to active sets it.
     *
     * Expects: date_admitted is still a year ago after the member returns.
     */
    public function test_returning_from_hiatus_keeps_the_admission_date()
    {
        $user = $this->userWithHistory('hiatus', [['active', now()->subYear()], ['hiatus', now()->subMonths(2)], ['active', now()->subDay()]]);
        $user->forceFill(['date_admitted' => now()->subYear()->toDateString()])->save();

        $this->artisan('process:userstatus');

        $this->assertSame(now()->subYear()->toDateString(), $user->fresh()->date_admitted->toDateString());
    }

    /**
     * Recording a hiatus also records the return to active on its end date (see StatusChangesTest).
     * This checks the job carries out that return.
     *
     * Expects: a member on hiatus whose return date was yesterday is active again.
     */
    public function test_a_hiatus_ends_on_its_end_date()
    {
        $user = $this->userWithHistory('hiatus', [['active', now()->subYear()], ['hiatus', now()->subMonths(2)], ['active', now()->subDay()]]);

        $this->artisan('process:userstatus');

        $this->assertSame('active', $user->fresh()->status);
    }

    public static function provideWithdrawals(): array
    {
        return [
            'inactive' => ['inactive'],
            'inactive (abandoned)' => ['inactive-abandoned'],
            'inactive (in memoriam)' => ['inactive-in-memoriam'],
            'terminated' => ['terminated'],
        ];
    }

    /**
     * Every way of leaving Kwartzlab records the date the member left (used in reports).
     *
     * Expects: the status is applied and date_withdrawn is the date of the change.
     * Edge cases: all four leaving statuses.
     * Regression: inactive-in-memoriam used to be missed (the code listed "terminated" twice).
     *
     * @dataProvider provideWithdrawals
     */
    public function test_a_withdrawal_records_the_withdrawal_date(string $status)
    {
        $user = $this->userWithHistory('active', [['active', now()->subYear()], [$status, now()->subDays(2)]]);

        $this->artisan('process:userstatus');

        $user->refresh();
        $this->assertSame($status, $user->status);
        $this->assertSame(now()->subDays(2)->toDateString(), $user->date_withdrawn->toDateString());
    }

    /**
     * A suspension is temporary; the member has not left. This guards against a withdrawal list
     * that grows to include statuses it should not.
     *
     * Expects: status becomes suspended and date_withdrawn stays empty.
     */
    public function test_a_suspension_does_not_record_a_withdrawal_date()
    {
        $user = $this->userWithHistory('active', [['active', now()->subYear()], ['suspended', now()->subDay()]]);

        $this->artisan('process:userstatus');

        $user->refresh();
        $this->assertSame('suspended', $user->status);
        $this->assertNull($user->date_withdrawn);
    }

    /**
     * Most nights nothing changes. The job must not rewrite every member: each save would add a row
     * to the audit log and move updated_at.
     *
     * Expects: the "no changes made" message, and updated_at unchanged.
     */
    public function test_members_already_up_to_date_are_left_alone()
    {
        $user = $this->userWithHistory('active', [['active', now()->subYear()]]);
        $before = $user->fresh()->updated_at;

        $this->artisan('process:userstatus')->expectsOutputToContain('no changes made');

        $this->assertEquals($before, $user->fresh()->updated_at);
    }

    /**
     * Some older accounts have no status history at all. The job must skip them and carry on.
     *
     * Expects: the job succeeds and the member processed after the one without history is updated.
     * Regression: this used to crash the whole job ("property of null"), so nobody after that
     * member was updated.
     */
    public function test_a_member_without_status_history_does_not_stop_the_others()
    {
        User::factory()->create(['status' => 'active']);
        $user = $this->userWithHistory('applicant', [['applicant', now()->subDays(14)], ['active', now()->subDay()]]);

        $this->artisan('process:userstatus')->assertSuccessful();

        $this->assertSame('active', $user->fresh()->status);
    }

    /**
     * @param  array<int, array{0: string, 1: \Carbon\Carbon}>  $history  Status changes as [status, effective date].
     */
    private function userWithHistory(string $currentStatus, array $history): User
    {
        $user = User::factory()->create(['status' => $currentStatus, 'date_admitted' => null, 'date_withdrawn' => null]);
        foreach ($history as [$status, $date]) {
            UserStatus::query()->create(['user_id' => $user->id, 'status' => $status, 'updated_by' => 1, 'created_at' => $date, 'updated_at' => $date]);
        }

        return $user;
    }
}
