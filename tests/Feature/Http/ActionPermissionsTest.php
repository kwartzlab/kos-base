<?php

namespace Tests\Feature\Http;

use App\Models\Authorization;
use App\Models\Gatekeeper;
use App\Models\Key;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Team;
use App\Models\TeamAssignment;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The admin actions that save, change or delete data (members, keys, roles, gatekeepers, teams and
 * forms) must only work for people who are allowed to do them.
 *
 * PermissionsTest checks that admin pages cannot be opened. This file checks the actions behind
 * those pages, because hiding a page does not stop someone sending the request directly.
 *
 * How it works: each action in the list is sent twice. Once by an ordinary member, after which the
 * admin data must be exactly as it was. Once by a superuser, after which the data must have
 * changed, which proves the request in the list is a real, working one (otherwise a typo in the
 * list would look like a successful refusal).
 *
 * Not covered here, because they have their own tests: recording status changes (StatusChangesTest)
 * and training requests, team settings, tool access and the deploy webhook (AuthorizationFixesTest).
 * Also not covered: adding a key from the member edit page (POST /users/{id}/store_key). That
 * action currently fails with a server error for everyone, so it cannot be shown to work for an
 * admin; assigning a key at the kiosk is covered instead.
 *
 * Upgrade risk: these rules live partly in route middleware ("can:manage-...") and partly inside
 * controllers. Both are touched by the Laravel upgrade.
 */
class ActionPermissionsTest extends TestCase
{
    use RefreshDatabase;

    /** Every table an admin action in this file could change. */
    private const ADMIN_TABLES = [
        'users', 'user_statuses', 'user_flags', 'user_roles', 'roles', 'role_permissions', 'keys',
        'gatekeepers', 'authorizations', 'teams', 'team_assignments', 'forms',
    ];

    private User $target;

    private User $applicant;

    private Key $key;

    private Role $role;

    private Team $team;

    private Gatekeeper $tool;

    private TeamAssignment $trainer;

    private TeamAssignment $maintainer;

    private TeamAssignment $pendingTrainer;

    /**
     * Builds one of everything the actions act on: a member with a key, a role and tool access, an
     * applicant, a team with a tool, an approved trainer and maintainer, and a pending trainer.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Role id 1 is the superuser role; claim it so no other role created here gets it.
        $superuser = new Role(['name' => 'Superuser', 'description' => '']);
        $superuser->id = 1;
        $superuser->save();

        $this->target = $this->member();
        $this->applicant = User::factory()->create(['status' => 'applicant']);
        $this->key = Key::query()->forceCreate(['user_id' => $this->target->id, 'rfid' => 'existing-fob']);

        $this->role = Role::query()->create(['name' => 'Bookkeeper', 'description' => '']);
        RolePermission::query()->create(['role_id' => $this->role->id, 'object' => 'reports', 'operation' => 'manage']);
        UserRole::query()->create(['user_id' => $this->target->id, 'role_id' => $this->role->id]);

        $this->team = Team::query()->create(['name' => 'Woodworking', 'description' => '']);
        $this->tool = Gatekeeper::factory()->create(['name' => 'Table Saw', 'type' => 'lockout', 'team_id' => $this->team->id]);
        Authorization::query()->create(['user_id' => $this->target->id, 'gatekeeper_id' => $this->tool->id]);

        $this->trainer = $this->assignment('trainer', 'active');
        $this->maintainer = $this->assignment('maintainer', 'active');
        $this->pendingTrainer = $this->assignment('trainer', 'new');
    }

    public static function provideAdminActions(): array
    {
        return collect([
            'edit a member',
            'delete an applicant',
            'create a form',
            'delete a key from a member',
            'disable the keys of a member',
            'assign a key at the kiosk',
            'create a role',
            'edit a role',
            'delete a role',
            'give a member the superuser role',
            'remove a member from a role',
            'create a gatekeeper',
            'edit a gatekeeper',
            'delete a gatekeeper',
            'remove all access to a tool',
            'put a member forward as a trainer',
            'make a member a maintainer',
            'remove a trainer',
            'remove a maintainer',
            'approve a pending trainer',
            'remove a pending trainer',
            'create a team',
            'delete a team',
        ])->mapWithKeys(fn (string $action) => [$action => [$action]])->all();
    }

    /**
     * An ordinary active member, with no roles and no place on the team, sends each admin action
     * directly, as if they had worked out the address without being shown the page.
     *
     * Why: this is the kind of hole fixed in November 2026, where a page was hidden but the action
     * behind it was open. Several of these actions lead to door access (keys, roles, tool access),
     * so each one must be refused for a member who has not been given that permission.
     *
     * Expects: no server error, and every admin table exactly as it was before the request.
     *
     * @dataProvider provideAdminActions
     */
    public function test_an_ordinary_member_cannot_perform_an_admin_action(string $action)
    {
        $member = $this->member();
        $before = $this->adminData();

        $response = $this->send($member, $action);

        $this->assertLessThan(500, $response->getStatusCode(), "\"{$action}\" caused a server error for an ordinary member.");
        $this->assertSame($before, $this->adminData(), "An ordinary member was able to {$action}.");
    }

    /**
     * A superuser sends the same actions.
     *
     * Why: the test above passes when nothing changes. If a request in the list were wrong (a
     * mistyped address or a missing field), nothing would change for anyone and the refusal would
     * prove nothing. This shows each request really does its job for someone who is allowed, and
     * guards against an upgrade locking the admins out.
     *
     * Expects: no server error, and the admin data is different afterwards.
     *
     * @dataProvider provideAdminActions
     */
    public function test_a_superuser_can_perform_the_same_admin_action(string $action)
    {
        $superuser = $this->member();
        UserRole::query()->create(['user_id' => $superuser->id, 'role_id' => 1]);
        $before = $this->adminData();

        $response = $this->send($superuser, $action);

        $this->assertLessThan(500, $response->getStatusCode(), "\"{$action}\" caused a server error for a superuser.");
        $this->assertNotSame($before, $this->adminData(), "\"{$action}\" changed nothing for a superuser, so the request in this test is not a working one.");
    }

    /**
     * A member saves the profile form against another member's id, then against their own.
     *
     * Why: the profile page is open to every member, and the address contains the member's id.
     * Changing that id must not let one member rewrite another's contact details or password. This
     * rule has no permission that overrides it, so it is tested apart from the admin actions.
     *
     * Expects: the other member's record is unchanged; the member's own record is updated.
     */
    public function test_a_member_can_only_edit_their_own_profile()
    {
        $member = $this->member();
        $profile = fn (string $email) => ['email' => $email, 'address' => '1 New Street', 'city' => 'Waterloo', 'province' => 'ON', 'postal' => 'N2L 1A1'];

        $this->actingAs($member)->put("/members/{$this->target->id}", $profile('hijacked@example.com'));
        $this->assertNotSame('hijacked@example.com', $this->target->fresh()->email);
        $this->assertNotSame('Waterloo', $this->target->fresh()->city);

        $this->actingAs($member)->put("/members/{$member->id}", $profile('mine@example.com'));
        $this->assertSame(['mine@example.com', 'Waterloo'], [$member->fresh()->email, $member->fresh()->city]);
    }

    /** Sends one named action as the given member. The requests match what the admin pages send. */
    private function send(User $as, string $action)
    {
        $gatekeeperForm = ['name' => 'Renamed Saw', 'status' => 'enabled', 'type' => 'lockout', 'auth_key' => 'new-auth-key', 'team_id' => $this->team->id, 'shared_auth' => 0];

        [$method, $uri, $data] = match ($action) {
            'edit a member' => ['put', "/users/{$this->target->id}", ['first_name' => 'Changed', 'last_name' => 'Name', 'first_preferred' => 'Changed', 'last_preferred' => 'Name', 'email' => 'changed@example.com']],
            'delete an applicant' => ['delete', "/users/{$this->applicant->id}", ['confirm' => 'on']],
            'create a form' => ['post', '/forms', ['name' => 'New form', 'description' => '', 'status' => 'enabled', 'element' => ['q1' => ['type' => 'input', 'label' => 'Question', 'name' => 'question', 'length' => 50, 'mask' => '']]]],
            'delete a key from a member' => ['get', "/users/{$this->target->id}/destroy_key/{$this->key->id}", []],
            'disable the keys of a member' => ['get', "/users/{$this->target->id}/toggle_flag/keys_disabled", []],
            'assign a key at the kiosk' => ['post', '/kiosk/store_key', ['yes' => '1', 'rfid' => 'kiosk-fob', 'user_id' => $as->id]],
            'create a role' => ['post', '/roles', ['name' => 'New role', 'description' => '']],
            'edit a role' => ['put', "/roles/{$this->role->id}", ['name' => 'Renamed role', 'description' => '']],
            'delete a role' => ['delete', "/roles/{$this->role->id}", ['confirm' => 'on']],
            'give a member the superuser role' => ['post', '/roles/1/add_user', ['assign-user' => $this->target->id]],
            'remove a member from a role' => ['get', "/roles/{$this->role->id}/remove_user/{$this->target->id}", []],
            'create a gatekeeper' => ['post', '/gatekeepers', ['name' => 'New Door'] + $gatekeeperForm],
            'edit a gatekeeper' => ['put', "/gatekeepers/{$this->tool->id}", $gatekeeperForm],
            'delete a gatekeeper' => ['delete', "/gatekeepers/{$this->tool->id}", ['confirm' => 'on']],
            'remove all access to a tool' => ['get', "/gatekeepers/{$this->tool->id}/revoke_all_auth", []],
            'put a member forward as a trainer' => ['post', "/gatekeepers/{$this->tool->id}/add_trainer/{$as->id}", []],
            'make a member a maintainer' => ['post', "/gatekeepers/{$this->tool->id}/add_maintainer/{$as->id}", []],
            'remove a trainer' => ['post', "/gatekeepers/{$this->tool->id}/remove_trainer/{$this->trainer->user_id}", []],
            'remove a maintainer' => ['post', "/gatekeepers/{$this->tool->id}/remove_maintainer/{$this->maintainer->user_id}", []],
            'approve a pending trainer' => ['post', "/gatekeepers/assignments/approve/{$this->pendingTrainer->id}", []],
            'remove a pending trainer' => ['post', "/gatekeepers/assignments/remove/{$this->pendingTrainer->id}", []],
            'create a team' => ['post', '/teams', ['name' => 'New team', 'description' => '']],
            'delete a team' => ['delete', "/teams/{$this->team->id}", []],
        };

        return $this->actingAs($as)->call(strtoupper($method), $uri, $data);
    }

    /** A snapshot of every admin table, used to tell whether a request changed anything. */
    private function adminData(): array
    {
        $snapshot = [];
        foreach (self::ADMIN_TABLES as $table) {
            $snapshot[$table] = DB::table($table)->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
        }

        return $snapshot;
    }

    /** An active member with the status history every real member has, and no roles. */
    private function member(): User
    {
        $user = User::factory()->active()->create();
        UserStatus::query()->create(['user_id' => $user->id, 'status' => UserStatus::STATUS_ACTIVE, 'updated_by' => 0]);

        return $user;
    }

    /** Gives a new member a role on the tool; status "new" means it is still awaiting approval. */
    private function assignment(string $role, string $status): TeamAssignment
    {
        return TeamAssignment::query()->create([
            'user_id' => $this->member()->id,
            'team_id' => $this->team->id,
            'team_role' => $role,
            'gatekeeper_id' => $this->tool->id,
            'status' => $status,
        ]);
    }
}
