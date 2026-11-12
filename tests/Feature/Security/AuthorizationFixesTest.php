<?php

namespace Tests\Feature\Security;

use App\Models\Authorization;
use App\Models\Gatekeeper;
use App\Models\Team;
use App\Models\TeamAssignment;
use App\Models\TeamRequest;
use App\Models\User;
use App\Models\UserStatus;
use Database\Seeders\FormsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Regression tests for five authorization vulnerabilities that were identified and fixed in the
 * pull request of November 12, 2026.
 *
 * The five problems, as they were before the fix:
 *
 *  1. Training requests. The member who asked for training on a tool could mark their own request
 *     as passed, which also gave them access to the tool. Passing and failing were plain GET links,
 *     so simply following a link was enough.
 *
 *  2. Team settings. Any logged-in member could open and save the settings of any team, including
 *     making themselves a team lead.
 *
 *  3. Tool access. Any member of the team that owns a tool (gatekeeper) could grant or revoke
 *     access to that tool, for themselves or anyone else, without being a trainer for it.
 *
 *  4. Membership application. The application form accepted a member's record id from the browser
 *     and wrote the application over that record, so any member's account could be overwritten.
 *
 *  5. Deploy webhook. When no deploy secret was configured, anyone could forge a valid signature
 *     and trigger a deployment.
 *
 * Each test either replays one of those attacks and checks that it is now refused, or checks that
 * the legitimate action next to it still works, so that a fix is not "passing" simply because the
 * feature is broken for everyone.
 *
 * These tests exist primarily to defend against regressions. In particular they must keep passing
 * through the Laravel upgrade, where routing, validation and authorization code will be touched.
 * A failure here means one of the holes above may have re-opened; do not relax a test to make it
 * pass without understanding which problem it guards.
 */
class AuthorizationFixesTest extends TestCase
{
    use RefreshDatabase;

    private Team $team;

    private Gatekeeper $tool;

    private User $member;

    /**
     * Every test starts with one team (Woodshop), one tool owned by it (Table saw), and one
     * ordinary member who holds no roles on the team.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // role id 1 is the superuser role, so make sure no test user can land on it by accident
        DB::table('roles')->insert(['name' => 'Superusers', 'description' => '']);

        $this->team = Team::create(['name' => 'Woodshop', 'description' => 'Wood tools']);
        $this->tool = Gatekeeper::factory()->create(['name' => 'Table saw', 'team_id' => $this->team->id]);
        $this->member = User::factory()->create();
    }

    // gives a user a role on the Woodshop team; status 'new' means the role is still awaiting approval
    private function assign(User $user, string $role, ?Gatekeeper $tool = null, string $status = 'active'): TeamAssignment
    {
        return TeamAssignment::create([
            'user_id' => $user->id,
            'team_id' => $this->team->id,
            'team_role' => $role,
            'gatekeeper_id' => $tool ? $tool->id : 0,
            'status' => $status,
        ]);
    }

    // creates a user who is a trainer for a tool (default: an approved trainer for the Table saw)
    private function trainer(string $status = 'active', ?Gatekeeper $tool = null): User
    {
        $trainer = User::factory()->create();
        $this->assign($trainer, 'trainer', $tool ?? $this->tool, $status);

        return $trainer;
    }

    // creates a user holding the global manage-teams permission
    private function teamManager(): User
    {
        $manager = User::factory()->create();
        $roleId = DB::table('roles')->insertGetId(['name' => 'Team managers', 'description' => '']);
        DB::table('role_permissions')->insert(['role_id' => $roleId, 'object' => 'teams', 'operation' => 'manage']);
        DB::table('user_roles')->insert(['user_id' => $manager->id, 'role_id' => $roleId]);

        return $manager;
    }

    // creates an open training request for the Table saw on behalf of a user
    private function trainingRequest(User $for): TeamRequest
    {
        return TeamRequest::create([
            'request_type' => 'training',
            'status' => 'new',
            'user_id' => $for->id,
            'team_id' => $this->team->id,
            'gatekeeper_id' => $this->tool->id,
        ]);
    }

    // does the user currently have access to the Table saw?
    private function isAuthorized(User $user): bool
    {
        return Authorization::where(['user_id' => $user->id, 'gatekeeper_id' => $this->tool->id])->exists();
    }

    // ---- Problem 1: training requests ---------------------------------------------------------
    // Only an approved trainer for the tool may pass or fail a training request.

    /**
     * A member asks for training and then tries to mark their own request as passed.
     *
     * Why: this is the original attack. It let any member give themselves access to any tool
     * without ever being trained. The request must stay open and no access may be granted.
     */
    public function test_a_member_cannot_pass_their_own_training_request()
    {
        $request = $this->trainingRequest($this->member);

        $this->actingAs($this->member)->post('/teams/training_pass/'.$request->id)->assertJson(['status' => 'error']);

        $this->assertSame('new', $request->fresh()->status);
        $this->assertFalse($this->isAuthorized($this->member));
    }

    /**
     * Even a legitimate trainer cannot pass or fail a request by visiting a link (a GET request).
     *
     * Why: these actions change data, and as GET links they could be triggered by tricking a
     * trainer into clicking a link or loading an image. They must only answer to a POST, which
     * carries a CSRF token. Guards against the routes being changed back to GET.
     */
    public function test_passing_and_failing_are_not_reachable_as_plain_links()
    {
        $request = $this->trainingRequest($this->member);

        $this->actingAs($this->trainer())->get('/teams/training_pass/'.$request->id)->assertStatus(405);
        $this->actingAs($this->trainer())->get('/teams/training_fail/'.$request->id)->assertStatus(405);

        $this->assertSame('new', $request->fresh()->status);
    }

    /**
     * An approved trainer for the tool passes a request, and the member gains access to the tool.
     *
     * Why: proves the fix did not break the normal training workflow. Without this, the refusal
     * tests above would still pass if passing a request were broken for everyone.
     */
    public function test_an_approved_trainer_for_the_tool_can_pass_a_request()
    {
        $request = $this->trainingRequest($this->member);

        $this->actingAs($this->trainer())->post('/teams/training_pass/'.$request->id)->assertJson(['status' => 'success']);

        $this->assertSame('completed', $request->fresh()->status);
        $this->assertTrue($this->isAuthorized($this->member));
    }

    /**
     * An approved trainer for the tool marks a request as not finished, and no access is granted.
     *
     * Why: failing a request goes through the same permission check as passing one, so the
     * legitimate path needs the same proof that it still works.
     */
    public function test_an_approved_trainer_for_the_tool_can_fail_a_request()
    {
        $request = $this->trainingRequest($this->member);

        $this->actingAs($this->trainer())->post('/teams/training_fail/'.$request->id)->assertJson(['status' => 'success']);

        $this->assertSame('failed', $request->fresh()->status);
        $this->assertFalse($this->isAuthorized($this->member));
    }

    /**
     * Someone who has been put forward as a trainer, but not yet approved, tries to pass a request.
     *
     * Why: trainer assignments need approval. Before the fix a pending trainer already counted as
     * a trainer, so the approval step could be skipped entirely.
     */
    public function test_a_trainer_still_awaiting_approval_cannot_pass_a_request()
    {
        $request = $this->trainingRequest($this->member);

        $this->actingAs($this->trainer('new'))->post('/teams/training_pass/'.$request->id)->assertJson(['status' => 'error']);

        $this->assertSame('new', $request->fresh()->status);
        $this->assertFalse($this->isAuthorized($this->member));
    }

    /**
     * A team lead who is not a trainer for the tool tries to pass and to fail a request.
     *
     * Why: leading a team is not the same as being qualified to train on a specific tool. The
     * agreed rule is that only trainers for that tool sign off training, and this pins it down.
     */
    public function test_a_team_lead_who_is_not_a_trainer_cannot_pass_a_request()
    {
        $lead = User::factory()->create();
        $this->assign($lead, 'lead');
        $request = $this->trainingRequest($this->member);

        $this->actingAs($lead)->post('/teams/training_pass/'.$request->id)->assertJson(['status' => 'error']);
        $this->actingAs($lead)->post('/teams/training_fail/'.$request->id)->assertJson(['status' => 'error']);

        $this->assertSame('new', $request->fresh()->status);
    }

    /**
     * A team lead who is also an approved trainer for the tool passes a request.
     *
     * Why: the other half of the rule above. Being a lead must not take away the rights a person
     * has as a trainer.
     */
    public function test_a_team_lead_who_is_also_a_trainer_for_the_tool_can_pass_a_request()
    {
        $lead = $this->trainer();
        $this->assign($lead, 'lead');
        $request = $this->trainingRequest($this->member);

        $this->actingAs($lead)->post('/teams/training_pass/'.$request->id)->assertJson(['status' => 'success']);

        $this->assertTrue($this->isAuthorized($this->member));
    }

    /**
     * An approved trainer for the Band saw tries to pass a request for the Table saw.
     *
     * Why: trainer rights are per tool. Checking only "is this person a trainer on the team"
     * would let a trainer sign people off on tools they are not qualified to teach.
     */
    public function test_a_trainer_for_a_different_tool_cannot_pass_a_request()
    {
        $otherTool = Gatekeeper::factory()->create(['name' => 'Band saw', 'team_id' => $this->team->id]);
        $request = $this->trainingRequest($this->member);

        $this->actingAs($this->trainer('active', $otherTool))->post('/teams/training_pass/'.$request->id)->assertJson(['status' => 'error']);

        $this->assertFalse($this->isAuthorized($this->member));
    }

    /**
     * A member cancels their own training request, then tries to cancel someone else's.
     *
     * Why: the fix removed the requester's right to pass or fail their own request, but they must
     * keep the right to withdraw it. They must not be able to withdraw another member's request.
     */
    public function test_a_member_can_still_cancel_their_own_request_but_not_someone_elses()
    {
        $mine = $this->trainingRequest($this->member);
        $theirs = $this->trainingRequest(User::factory()->create());

        $this->actingAs($this->member)->get('/teams/training_cancel/'.$mine->id)->assertJson(['status' => 'success']);
        $this->actingAs($this->member)->get('/teams/training_cancel/'.$theirs->id)->assertJson(['status' => 'error']);

        $this->assertSame('cancelled', $mine->fresh()->status);
        $this->assertSame('new', $theirs->fresh()->status);
    }

    /**
     * Someone put forward as a trainer, but not yet approved, opens the team's list of training
     * requests, then tries to cancel a member's request directly.
     *
     * Why: found during manual testing of the fix. A pending trainer is allowed to see who has
     * asked for training, but was also shown a Cancel button. Until they are approved they may
     * read the list and nothing more: no action buttons on the page, and the server must refuse
     * the action if they try it anyway.
     */
    public function test_a_trainer_still_awaiting_approval_can_see_training_requests_but_not_act_on_them()
    {
        $pending = $this->trainer('new');
        // the page layout reads the viewer's status history
        UserStatus::create(['user_id' => $pending->id, 'status' => 'active', 'updated_by' => 0]);
        $request = $this->trainingRequest($this->member);

        $this->actingAs($pending)->get('/teams/'.$this->team->id.'/requests/training')
            ->assertOk()
            ->assertSee($this->member->get_name())
            ->assertDontSee('Did Not Finish')
            ->assertDontSee('Cancel Request');

        $this->actingAs($pending)->get('/teams/training_cancel/'.$request->id)->assertJson(['status' => 'error']);

        $this->assertSame('new', $request->fresh()->status);
    }

    /**
     * An approved trainer for the tool opens the team's list of training requests, sees the
     * member's request with its action buttons, and cancels it.
     *
     * Why: proves the page and the cancel action still work for the people who run training.
     * Without this, the refusal test above would still pass if the page were closed to everyone.
     */
    public function test_an_approved_trainer_can_see_and_cancel_training_requests_for_their_tool()
    {
        $trainer = $this->trainer();
        // the page layout reads the viewer's status history
        UserStatus::create(['user_id' => $trainer->id, 'status' => 'active', 'updated_by' => 0]);
        $request = $this->trainingRequest($this->member);

        $this->actingAs($trainer)->get('/teams/'.$this->team->id.'/requests/training')
            ->assertOk()
            ->assertSee($this->member->get_name())
            ->assertSee('Did Not Finish')
            ->assertSee('Cancel Request');

        $this->actingAs($trainer)->get('/teams/training_cancel/'.$request->id)->assertJson(['status' => 'success']);

        $this->assertSame('cancelled', $request->fresh()->status);
    }

    /**
     * A team lead who is not a trainer for the tool opens the team's list of training requests.
     *
     * Why: leads may see who is waiting for training on their team, but may not act on a request
     * for a tool they do not train on. The page must not offer them buttons that the server will
     * refuse, which is what made the pending-trainer problem look worse than it was.
     */
    public function test_a_team_lead_who_is_not_a_trainer_sees_requests_without_action_buttons()
    {
        $lead = User::factory()->create();
        $this->assign($lead, 'lead');
        // the page layout reads the viewer's status history
        UserStatus::create(['user_id' => $lead->id, 'status' => 'active', 'updated_by' => 0]);
        $this->trainingRequest($this->member);

        $this->actingAs($lead)->get('/teams/'.$this->team->id.'/requests/training')
            ->assertOk()
            ->assertSee($this->member->get_name())
            ->assertDontSee('Did Not Finish')
            ->assertDontSee('Cancel Request');
    }

    // ---- Problem 2: team settings -------------------------------------------------------------
    // Only a lead of the team, or a holder of manage-teams, may edit a team. Only manage-teams
    // may decide who the leads are.

    /**
     * A member with no role on a team tries to open its settings page, then to save a new team
     * name with themselves as lead.
     *
     * Why: this is the original attack. Any logged-in member could rename a team and take it
     * over. The team must be unchanged and the member must hold no role on it afterwards.
     */
    public function test_a_member_cannot_open_or_save_another_teams_settings()
    {
        $this->actingAs($this->member)->get('/teams/'.$this->team->id.'/edit')->assertRedirect('/teams');

        $this->actingAs($this->member)
            ->put('/teams/'.$this->team->id, ['name' => 'Hijacked', 'lead' => [$this->member->id]])
            ->assertRedirect('/teams');

        $this->assertSame('Woodshop', $this->team->fresh()->name);
        $this->assertFalse($this->team->is_lead($this->member->id));
        $this->assertFalse($this->team->is_member($this->member->id));
    }

    /**
     * A team lead opens their own team's settings and saves a new description and a new subject
     * matter expert, while also sending a forged "lead" field naming someone else.
     *
     * Why: three things in one. A lead must still be able to edit their own team. A lead must not
     * be able to appoint leads, even by forging the field the form does not show them. And saving
     * the form must not remove the existing lead: before the fix, a lead's save deleted every lead
     * on the team, because the missing field was read as "no leads".
     */
    public function test_a_team_lead_can_edit_their_team_but_cannot_change_who_leads_it()
    {
        $lead = User::factory()->create();
        $this->assign($lead, 'lead');
        // the page layout reads the viewer's status history
        UserStatus::create(['user_id' => $lead->id, 'status' => 'active', 'updated_by' => 0]);

        $this->actingAs($lead)->get('/teams/'.$this->team->id.'/edit')->assertOk();

        // the form a lead sees has no lead field; a forged one must be ignored too
        $this->actingAs($lead)
            ->put('/teams/'.$this->team->id, ['name' => 'Woodshop', 'description' => 'New words', 'lead' => [$this->member->id], 'sme' => [$this->member->id]])
            ->assertRedirect('/teams/'.$this->team->id.'/edit');

        $this->assertSame('New words', $this->team->fresh()->description);
        $this->assertTrue($this->team->is_lead($lead->id), 'saving the form must not remove the existing lead');
        $this->assertFalse($this->team->is_lead($this->member->id));
        $this->assertTrue(TeamAssignment::where(['team_id' => $this->team->id, 'user_id' => $this->member->id, 'team_role' => 'sme'])->exists());
    }

    /**
     * The lead of the Woodshop tries to rename the Metal shop.
     *
     * Why: a lead's rights stop at their own team. Checking only "is this person a lead of
     * something" would let any lead edit every team.
     */
    public function test_a_team_lead_cannot_edit_a_different_team()
    {
        $lead = User::factory()->create();
        $this->assign($lead, 'lead');
        $other = Team::create(['name' => 'Metal shop', 'description' => '']);

        $this->actingAs($lead)->put('/teams/'.$other->id, ['name' => 'Hijacked'])->assertRedirect('/teams');

        $this->assertSame('Metal shop', $other->fresh()->name);
    }

    /**
     * A holder of the manage-teams permission appoints a member as team lead.
     *
     * Why: proves the fix did not lock out the people who are supposed to manage teams. This is
     * now the only way a lead can be appointed, so it has to keep working.
     */
    public function test_a_team_manager_can_still_assign_the_lead()
    {
        $this->actingAs($this->teamManager())
            ->put('/teams/'.$this->team->id, ['name' => 'Woodshop', 'lead' => [$this->member->id]])
            ->assertRedirect('/teams/'.$this->team->id.'/edit');

        $this->assertTrue($this->team->is_lead($this->member->id));
    }

    // ---- Problem 3: granting and revoking tool access -----------------------------------------
    // Only an approved trainer for the tool (or a holder of manage-gatekeepers) may grant or
    // revoke access to it.

    /**
     * An ordinary member of the team that owns the tool tries to grant themselves access to it,
     * then to revoke another member's access.
     *
     * Why: this is the original attack. Belonging to the team in any role was enough to hand out
     * or take away tool access. Nothing may change: the attacker gains no access and the other
     * member keeps theirs.
     */
    public function test_a_plain_team_member_cannot_grant_or_revoke_tool_access()
    {
        $this->assign($this->member, 'sme');
        $victim = User::factory()->create();
        $existing = Authorization::create(['user_id' => $victim->id, 'gatekeeper_id' => $this->tool->id]);

        $this->actingAs($this->member)
            ->post('/gatekeepers/authorize', ['gatekeeper_id' => $this->tool->id, 'user_id' => $this->member->id])
            ->assertRedirect('/');
        $this->actingAs($this->member)->post('/gatekeepers/revoke/'.$existing->id)->assertJson(['status' => 'error']);

        $this->assertFalse($this->isAuthorized($this->member));
        $this->assertTrue($this->isAuthorized($victim));
    }

    /**
     * A team lead who is not a trainer for the tool tries to grant themselves access to it.
     *
     * Why: same rule as for passing training requests. Granting access is a statement that the
     * person is trained, so it belongs to trainers for that tool, not to leads.
     */
    public function test_a_team_lead_who_is_not_a_trainer_cannot_grant_tool_access()
    {
        $lead = User::factory()->create();
        $this->assign($lead, 'lead');

        $this->actingAs($lead)->post('/gatekeepers/authorize', ['gatekeeper_id' => $this->tool->id, 'user_id' => $lead->id]);

        $this->assertFalse($this->isAuthorized($lead));
    }

    /**
     * Someone put forward as a trainer, but not yet approved, tries to grant themselves access.
     *
     * Why: a pending trainer must not have a trainer's rights. Otherwise the approval step can
     * be skipped, as it could before the fix.
     */
    public function test_a_trainer_still_awaiting_approval_cannot_grant_tool_access()
    {
        $pending = $this->trainer('new');

        $this->actingAs($pending)->post('/gatekeepers/authorize', ['gatekeeper_id' => $this->tool->id, 'user_id' => $pending->id]);

        $this->assertFalse($this->isAuthorized($pending));
    }

    /**
     * An approved trainer for the tool grants a member access, then revokes it.
     *
     * Why: proves the fix did not break the normal way trainers manage who may use their tool.
     * Without this, the refusal tests above would still pass if granting were broken for everyone.
     */
    public function test_an_approved_trainer_for_the_tool_can_grant_and_revoke_access()
    {
        $trainer = $this->trainer();

        $this->actingAs($trainer)
            ->post('/gatekeepers/authorize', ['gatekeeper_id' => $this->tool->id, 'user_id' => $this->member->id])
            ->assertRedirect('/gatekeepers/'.$this->tool->id.'/dashboard');
        $this->assertTrue($this->isAuthorized($this->member));

        $authorization = Authorization::where(['user_id' => $this->member->id, 'gatekeeper_id' => $this->tool->id])->first();
        $this->actingAs($trainer)->post('/gatekeepers/revoke/'.$authorization->id)->assertJson(['status' => 'success']);
        $this->assertFalse($this->isAuthorized($this->member));
    }

    /**
     * A trainer asks to revoke an access record that does not exist.
     *
     * Why: before the fix this crashed with a server error, because the permission check read
     * from the missing record. The page expects a normal "error" answer it can show to the user.
     */
    public function test_revoking_an_unknown_authorization_is_an_error_not_a_crash()
    {
        $this->actingAs($this->trainer())->post('/gatekeepers/revoke/999999')->assertOk()->assertJson(['status' => 'error']);
    }

    // ---- Problem 4: the membership application ------------------------------------------------
    // An application may only re-use an existing member record when that record belongs to a
    // former or abandoned applicant and the email on the application matches it.

    // submits the membership application form as the ordinary member, with the given fields changed
    private function submitApplication(array $overrides)
    {
        $this->seed(FormsTableSeeder::class);
        Mail::fake();

        return $this->actingAs($this->member)->post('/forms/1/save', array_merge([
            'form_id' => 1,
            'element-cdf75849-ccb8-4cdf' => 'i',
            'first_name' => 'Mallory',
            'last_name' => 'Attacker',
            'email' => 'mallory@example.com',
            'phone' => '(123) 456-7890',
            'address' => '1 Any Street',
            'city' => 'Kitchener',
            'province' => 'ON',
            'postal' => 'A1A 1A1',
            'photo' => 'photo-file',
        ], $overrides));
    }

    /**
     * An application is submitted naming another person's record, with that person's correct
     * email, while their record is in a status that must never be re-used (run once per status:
     * current members, members on hiatus, suspended or terminated members, people already
     * applying, denied applicants, and memorial records).
     *
     * Why: this is the original attack. The application would overwrite the named record,
     * replacing its name, contact details and status. The application must be refused and the
     * record must be exactly as it was, including its password.
     *
     * @dataProvider protectedStatuses
     */
    public function test_an_application_cannot_overwrite_a_record_in_a_protected_status(string $status)
    {
        $victim = User::factory()->create(['status' => $status, 'first_name' => 'Victor', 'email' => 'victor@example.com']);
        $passwordBefore = $victim->password;

        // even with the victim's own email, a protected record is never re-used
        $this->submitApplication(['user_id' => $victim->id, 'email' => 'victor@example.com'])->assertSessionHasErrors('email');

        $victim->refresh();
        $this->assertSame(['Victor', $status, $passwordBefore], [$victim->first_name, $victim->status, $victim->password]);
    }

    // statuses whose records an application must never be allowed to re-use
    public function protectedStatuses(): array
    {
        return [['active'], ['hiatus'], ['suspended'], ['terminated'], ['applicant'], ['applicant-denied'], ['inactive-in-memoriam']];
    }

    /**
     * An application names a former member's record but gives a different email address.
     *
     * Why: former members' records may be re-used, so they need a second check. Without the email
     * match, anyone could take over a former member's record and put their own email on it. The
     * record must be unchanged and no account may be created for the attacker's email.
     */
    public function test_an_application_cannot_take_over_a_former_members_record_under_a_different_email()
    {
        $victim = User::factory()->create(['status' => 'inactive', 'first_name' => 'Victor', 'email' => 'victor@example.com']);
        $passwordBefore = $victim->password;

        $this->submitApplication(['user_id' => $victim->id, 'email' => 'mallory@example.com'])->assertSessionHasErrors('email');

        $victim->refresh();
        $this->assertSame(['Victor', 'inactive', $passwordBefore], [$victim->first_name, $victim->status, $victim->password]);
        $this->assertFalse(User::where('email', 'mallory@example.com')->exists());
    }

    /**
     * A former member or abandoned applicant applies again with their own email (typed with
     * different capital letters), run once per status that is allowed to be re-used.
     *
     * Why: proves the fix did not break re-applying, which is a normal thing for a returning
     * member to do. Their old record must become an applicant again, with the new details, and
     * no second record may be created for the same person.
     *
     * @dataProvider returningStatuses
     */
    public function test_a_returning_applicant_re_uses_their_old_record(string $status)
    {
        $returning = User::factory()->create(['status' => $status, 'first_name' => 'Old', 'email' => 'returning@example.com']);

        $this->submitApplication(['user_id' => $returning->id, 'email' => 'Returning@Example.com', 'first_name' => 'Renewed'])
            ->assertSessionHasNoErrors();

        $returning->refresh();
        $this->assertSame(['Renewed', 'applicant'], [$returning->first_name, $returning->status]);
        $this->assertSame(1, User::where('email', 'returning@example.com')->count());
    }

    // statuses whose records a returning applicant is allowed to re-use
    public function returningStatuses(): array
    {
        return [['inactive'], ['inactive-abandoned'], ['applicant-abandoned']];
    }

    /**
     * A brand new application (no existing record named) uses an email that already belongs to
     * a member.
     *
     * Why: the check for duplicate emails existed in the code but could never run. Without it, a
     * second account could be created under an existing member's email. The application must be
     * refused and the existing member must be untouched.
     */
    public function test_a_new_application_with_an_email_already_in_use_is_refused()
    {
        $existing = User::factory()->create(['email' => 'taken@example.com']);

        $this->submitApplication(['email' => 'taken@example.com'])->assertSessionHasErrors('email');

        $this->assertSame(1, User::where('email', 'taken@example.com')->count());
        $this->assertSame('active', $existing->fresh()->status);
    }

    /**
     * A brand new application with a fresh email is accepted and creates an applicant.
     *
     * Why: proves the ordinary application path still works after the extra checks were added.
     * Without this, the refusal tests above would still pass if every application were refused.
     */
    public function test_a_new_application_still_creates_an_applicant()
    {
        $this->submitApplication([])->assertSessionHasNoErrors();

        $this->assertSame('applicant', User::where('email', 'mallory@example.com')->value('status'));
    }

    // ---- Problem 5: the deploy webhook --------------------------------------------------------
    // The webhook that deploys the site must refuse any request it cannot verify.
    //
    // Caution: on code without the fix, the first test below really does start the deploy script
    // (deploy.sh) in whatever environment the tests are running in. Do not run these tests against
    // unfixed code anywhere a deployment could do harm.

    /**
     * With no deploy secret configured (unset, or set to an empty string), a request arrives
     * carrying a signature calculated with an empty secret.
     *
     * Why: this is the original attack. With no secret, anyone can calculate a signature the
     * server accepts, and so trigger a deployment. A server with no secret must refuse everything.
     *
     * @dataProvider blankSecrets
     */
    public function test_the_deploy_webhook_refuses_everything_when_no_secret_is_configured($secret)
    {
        config(['app.deploy_secret' => $secret]);
        $body = '{"ref":"refs/heads/main"}';
        $forged = 'sha256='.hash_hmac('sha256', $body, '', false);

        $this->call('POST', '/deploy', [], [], [], ['HTTP_X-Hub-Signature-256' => $forged, 'CONTENT_TYPE' => 'application/json'], $body)
            ->assertStatus(403);
    }

    // the two ways a deploy secret can be missing: not set at all, or set to nothing
    public function blankSecrets(): array
    {
        return [[null], ['']];
    }

    /**
     * With a real secret configured, a request arrives with no signature at all.
     *
     * Why: before the fix this caused a server error instead of a clean refusal. An unsigned
     * request must be turned away with "forbidden" and must never reach the deploy step.
     */
    public function test_the_deploy_webhook_refuses_a_request_with_no_signature()
    {
        config(['app.deploy_secret' => 'a-real-secret']);

        $this->post('/deploy', [])->assertStatus(403);
    }

    /**
     * With a real secret configured, a request arrives with a signature that does not match.
     *
     * Why: before the fix a wrong signature was silently ignored with a "success" answer, which
     * hides failed attempts and misconfigured webhooks. It must be an explicit "forbidden".
     */
    public function test_the_deploy_webhook_refuses_a_wrong_signature()
    {
        config(['app.deploy_secret' => 'a-real-secret']);

        $this->call('POST', '/deploy', [], [], [], ['HTTP_X-Hub-Signature-256' => 'sha256=deadbeef'], '{}')->assertStatus(403);
    }
}
