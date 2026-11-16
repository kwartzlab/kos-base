<?php

namespace Tests\Feature\Http\Controllers\UsersController;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Membership admins record status changes with an effective date. Changes effective today apply
 * at once; later ones wait for the nightly job. Only changes made by a person can be deleted.
 */
class StatusChangesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $superuser = new Role(['name' => 'Superuser', 'description' => '']);
        $superuser->id = 1;
        $superuser->save();
        $role = Role::query()->create(['name' => 'membership', 'description' => '']);
        RolePermission::query()->create(['role_id' => $role->id, 'object' => 'users', 'operation' => 'manage']);

        $this->admin = $this->activeMember();
        UserRole::query()->create(['user_id' => $this->admin->id, 'role_id' => $role->id]);
        $this->member = $this->activeMember();
    }

    /**
     * A change dated today applies at once, without waiting for the nightly job.
     *
     * Expects: a status history row with the status, the admin's note and the admin's id, and the
     * member's status already updated.
     */
    public function test_a_change_effective_today_is_recorded_and_applied()
    {
        $this->actingAs($this->admin)
            ->post("/users/{$this->member->id}/status", ['status_type' => 'suspended', 'effective_date' => now()->toDateString(), 'note' => 'Unpaid dues'])
            ->assertOk();

        $status = $this->member->status_history()->get()->last();
        $this->assertSame('suspended', $status->status);
        $this->assertSame('Unpaid dues', $status->note);
        $this->assertSame($this->admin->id, (int) $status->updated_by);
        $this->assertSame('suspended', $this->member->fresh()->status);
    }

    /**
     * A change dated later is saved for the nightly job (ProcessUserStatusUpdatesTest) to apply.
     *
     * Expects: a history row dated next week, and the member still active today.
     */
    public function test_a_change_effective_later_is_recorded_but_not_applied_yet()
    {
        $this->actingAs($this->admin)
            ->post("/users/{$this->member->id}/status", ['status_type' => 'inactive', 'effective_date' => now()->addWeek()->toDateString()])
            ->assertOk();

        $status = $this->member->status_history()->get()->last();
        $this->assertSame('inactive', $status->status);
        $this->assertSame(now()->addWeek()->toDateString(), $status->created_at->toDateString());
        $this->assertSame('active', $this->member->fresh()->status);
    }

    /**
     * A hiatus is entered once, with a start and an end date, and creates two changes.
     *
     * Expects: a hiatus change on the start date, and an active change on the end date noted
     * "Ending Hiatus - <note>".
     * Edge case: the admin's note goes on the return row only; the hiatus row has no note.
     */
    public function test_a_hiatus_records_its_start_and_its_return_to_active()
    {
        $start = now()->addDays(2)->toDateString();
        $end = now()->addMonths(2)->toDateString();

        $this->actingAs($this->admin)
            ->post("/users/{$this->member->id}/status", ['status_type' => 'hiatus', 'effective_date' => $start, 'effective_date_ending' => $end, 'note' => 'Travelling'])
            ->assertOk();

        [$hiatus, $return] = $this->member->status_history()->get()->slice(-2)->values()->all();
        $this->assertSame(['hiatus', $start, null], [$hiatus->status, $hiatus->created_at->toDateString(), $hiatus->note]);
        $this->assertSame(['active', $end, 'Ending Hiatus - Travelling'], [$return->status, $return->created_at->toDateString(), $return->note]);
    }

    /**
     * An admin's status form arrives incomplete, or names a member who does not exist (for example
     * from a stale page). kOS must refuse it cleanly, so the admin sees a normal error and not a
     * crash.
     *
     * Expects: 403 when the status is missing, and 403 for a member id that does not exist.
     * Regression: both used to crash with a 500 (the code passed null where the error page expects
     * data).
     */
    public function test_a_change_without_a_status_or_for_an_unknown_member_is_refused()
    {
        $this->actingAs($this->admin)->post("/users/{$this->member->id}/status", ['effective_date' => now()->toDateString()])->assertForbidden();
        $this->actingAs($this->admin)->post('/users/999999/status', ['status_type' => 'active', 'effective_date' => now()->toDateString()])->assertForbidden();
    }

    /**
     * A status that is not one of the real membership statuses must never be accepted, and the
     * admin must be told so. The member's history must be left as it was.
     *
     * Expects: 422 for a status the form does not offer, and nothing saved.
     * Regression: it used to answer "ok" while saving nothing, so the admin thought it had worked.
     */
    public function test_an_unknown_status_type_is_refused()
    {
        $this->actingAs($this->admin)
            ->post("/users/{$this->member->id}/status", ['status_type' => 'banana', 'effective_date' => now()->toDateString()])
            ->assertStatus(422);

        $this->assertSame(1, $this->member->status_history()->count());
    }

    /**
     * An admin deletes a status change that is already gone. The page relies on getting its normal
     * error answer so it can tell the admin.
     *
     * Expects: the usual {"error": "error"} answer when deleting a status change that does not
     * exist (for example one deleted in another tab).
     * Regression: this used to crash with a 500.
     */
    public function test_deleting_an_unknown_status_change_is_refused()
    {
        $this->actingAs($this->admin)->delete("/users/{$this->member->id}/status", ['status_id' => 999999])->assertJson(['error' => 'error']);
    }

    /**
     * A member's status decides whether their fob opens the door, so only membership admins may
     * change it. Here a member without that permission tries to terminate someone.
     *
     * Expects: 403 for a member without users/manage, and the target's status unchanged.
     */
    public function test_an_ordinary_member_cannot_change_statuses()
    {
        $this->actingAs($this->activeMember())
            ->post("/users/{$this->member->id}/status", ['status_type' => 'terminated', 'effective_date' => now()->toDateString()])
            ->assertForbidden();

        $this->assertSame('active', $this->member->fresh()->status);
    }

    /**
     * Admins can undo a change they (or another admin) entered by mistake.
     *
     * Expects: the row is deleted.
     */
    public function test_a_change_made_by_a_person_can_be_deleted()
    {
        $status = $this->status($this->member, 'inactive', now()->addWeek(), $this->admin->id);

        $this->actingAs($this->admin)->delete("/users/{$this->member->id}/status", ['status_id' => $status->id])->assertOk();

        $this->assertNull(UserStatus::query()->find($status->id));
    }

    /**
     * Changes made by kOS itself (updated_by 0, such as the "applicant" row from the membership
     * application) are part of the record and cannot be deleted.
     *
     * Expects: an error answer, and the row still exists.
     */
    public function test_a_change_made_by_kos_cannot_be_deleted()
    {
        $status = $this->status($this->member, 'inactive', now()->addWeek(), 0);

        $this->actingAs($this->admin)->delete("/users/{$this->member->id}/status", ['status_id' => $status->id])->assertJson(['error' => 'error']);

        $this->assertNotNull(UserStatus::query()->find($status->id));
    }

    /**
     * The delete URL names a member and the request names a status id. They must match, so a
     * change cannot be deleted through the wrong member's page.
     *
     * Expects: an error answer, and the other member's row still exists.
     */
    public function test_a_change_cannot_be_deleted_through_another_member()
    {
        $other = $this->activeMember();
        $status = $this->status($other, 'inactive', now()->addWeek(), $this->admin->id);

        $this->actingAs($this->admin)->delete("/users/{$this->member->id}/status", ['status_id' => $status->id])->assertJson(['error' => 'error']);

        $this->assertNotNull(UserStatus::query()->find($status->id));
    }

    private function activeMember(): User
    {
        $user = User::factory()->active()->create();
        $this->status($user, 'active', now()->subYear(), 0);

        return $user;
    }

    private function status(User $user, string $status, $date, int $updatedBy): UserStatus
    {
        return UserStatus::query()->create(['user_id' => $user->id, 'status' => $status, 'updated_by' => $updatedBy, 'created_at' => $date, 'updated_at' => $date]);
    }
}
