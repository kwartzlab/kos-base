<?php

namespace Tests\Feature\Http;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Admin areas are only open to members whose roles grant the matching permission. Pages refuse
 * either with a 403 or by redirecting to the dashboard with an error.
 *
 * How permissions work: a member has roles, a role has permissions (object + operation, such as
 * users/manage), and the gates in AuthServiceProvider check them. Role id 1 is the superuser role
 * and passes every gate.
 *
 * Limits: these tests load pages (GET requests) only. Actions that save data need their own tests.
 */
class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The only addresses meant to answer a visitor who is not logged in: the login and password
     * reset pages, the device and kiosk endpoints (which identify themselves with a device key or
     * a fob), the Slack invite link sent by email, the deploy webhook (which checks its own
     * secret), and the theme's dark mode switch.
     */
    private const PUBLIC_ROUTES = [
        'login', 'logout', 'register', 'password/email', 'password/reset', 'password/reset/{token}',
        'keys', 'kiosk', 'kiosk/authenticate', 'kiosk/logout',
        'slack/invite', 'deploy', 'adminlte/darkmode/toggle',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Role id 1 is the superuser role; claim it so the roles these tests create never get it.
        $role = new Role(['name' => 'Superuser', 'description' => '']);
        $role->id = 1;
        $role->save();
    }

    public static function provideAdminPages(): array
    {
        return [
            'membership register' => ['/users', 'users', 'manage'],
            'add member (admin view)' => ['/users/index', 'users', 'manage'],
            'roles' => ['/roles', 'roles', 'manage'],
            'new role' => ['/roles/create', 'roles', 'manage'],
            'reports' => ['/reports', 'reports', 'manage'],
            'key enrolment kiosk' => ['/kiosk/create_key', 'keys', 'manage'],
            'gatekeeper list' => ['/gatekeepers', 'gatekeepers', 'manage'],
            'new gatekeeper' => ['/gatekeepers/create', 'gatekeepers', 'manage'],
            'manage teams' => ['/teams/manage', 'teams', 'manage'],
            'new team' => ['/teams/create', 'teams', 'manage'],
        ];
    }

    /**
     * An active member with no roles must not reach any admin page.
     *
     * Expects: refused (403, or a redirect to the dashboard with an error) on every page above.
     * Regression: /teams/manage and /teams/create used to show a blank page instead of refusing.
     *
     * @dataProvider provideAdminPages
     */
    public function test_an_ordinary_member_is_refused(string $uri)
    {
        $this->assertDenied($this->actingAs($this->member())->get($uri));
    }

    /**
     * The other half of the test above: the right permission (and only that one) opens the page.
     * Without this, a page that refused everyone would also pass.
     *
     * Expects: 200 for a member whose only role grants the page's permission.
     *
     * @dataProvider provideAdminPages
     */
    public function test_the_matching_permission_opens_the_page(string $uri, string $object, string $operation)
    {
        $this->actingAs($this->member([[$object, $operation]]))->get($uri)->assertOk();
    }

    /**
     * A permission must not leak into other areas.
     *
     * Expects: a membership admin (users/manage) can open the register but is refused on roles,
     * gatekeepers and key enrolment.
     */
    public function test_a_permission_only_opens_its_own_area()
    {
        $userAdmin = $this->member([['users', 'manage']]);

        $this->actingAs($userAdmin)->get('/users')->assertOk();
        $this->assertDenied($this->actingAs($userAdmin)->get('/roles'));
        $this->assertDenied($this->actingAs($userAdmin)->get('/gatekeepers'));
        $this->assertDenied($this->actingAs($userAdmin)->get('/kiosk/create_key'));
    }

    /**
     * Reports need two permissions: reports/manage for the reports area, plus one per report,
     * because the reports hold personal data (member status) or entry history (activity).
     *
     * Expects: reports/manage alone opens neither report; reports/users opens only the status
     * report; reports/gatekeepers opens the activity report.
     */
    public function test_each_report_needs_its_own_permission_as_well_as_reports_access()
    {
        $reportsOnly = $this->member([['reports', 'manage']]);
        $this->assertDenied($this->actingAs($reportsOnly)->get('/reports/member-status-report'));
        $this->assertDenied($this->actingAs($reportsOnly)->get('/reports/member-activity-report'));

        $userReports = $this->member([['reports', 'manage'], ['reports', 'users']]);
        $this->actingAs($userReports)->get('/reports/member-status-report')->assertOk();
        $this->assertDenied($this->actingAs($userReports)->get('/reports/member-activity-report'));

        $gatekeeperReports = $this->member([['reports', 'manage'], ['reports', 'gatekeepers']]);
        $this->actingAs($gatekeeperReports)->get('/reports/member-activity-report')->assertOk();
    }

    /**
     * The manage-forms gate has no permission of its own, so only superusers pass it.
     *
     * Expects: a membership admin is refused /forms; a superuser can open it.
     */
    public function test_forms_are_managed_by_superusers_only()
    {
        $this->assertDenied($this->actingAs($this->member([['users', 'manage']]))->get('/forms'));
        $this->actingAs($this->superuser())->get('/forms')->assertOk();
    }

    public static function provideMemberPages(): array
    {
        return [
            'dashboard' => ['/dashboard'],
            'member directory' => ['/members'],
            'tool list' => ['/gatekeepers/tools'],
            'my training' => ['/teams/training'],
            'teams' => ['/teams'],
        ];
    }

    /**
     * Guards against the opposite mistake: locking members out of the pages meant for everyone.
     *
     * Expects: 200 for an active member with no roles on each page above.
     *
     * @dataProvider provideMemberPages
     */
    public function test_an_ordinary_member_can_use_the_member_pages(string $uri)
    {
        $this->actingAs($this->member())->get($uri)->assertOk();
    }

    /**
     * A visitor who is not logged in requests every address the site answers on, with every
     * method (view, save, delete). The addresses are read from the application's own route list,
     * so a page added later is checked without anyone remembering to add it here.
     *
     * Why: the login requirement is attached to each group of routes separately. A group added
     * without it, or the wiring being lost when the framework is upgraded, would open member data
     * to the internet without any error. The short list in PUBLIC_ROUTES names the only addresses
     * meant to work without a login; adding to it should be a deliberate, reviewed decision.
     *
     * Not covered here: addresses under /api, which use tokens instead of a login (see ApiTest).
     *
     * Expects: every address outside PUBLIC_ROUTES redirects to /login, and every entry in
     * PUBLIC_ROUTES still exists (so the list cannot quietly go out of date).
     */
    public function test_every_page_and_action_requires_a_login_unless_listed_as_public()
    {
        $open = [];
        $seen = [];
        $checked = 0;

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $seen[] = $route->uri();
            // Skip the token-protected API and the debugging pages of a developer-only package.
            if (str_starts_with($route->uri(), 'api/') || str_starts_with($route->uri(), '_') || in_array($route->uri(), self::PUBLIC_ROUTES)) {
                continue;
            }
            // Any id will do: the login check must happen before the record is looked up.
            $uri = '/'.ltrim(preg_replace('/\{\w+\??\}/', '1', $route->uri()), '/');

            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                $response = $this->call($method, $uri);
                $checked++;
                if (! $response->isRedirect(url('/login'))) {
                    $open[] = "{$method} {$uri}: {$response->getStatusCode()}";
                }
            }
        }

        $this->assertSame([], $open, 'These addresses answered a visitor who is not logged in.');
        $this->assertSame([], array_values(array_diff(self::PUBLIC_ROUTES, $seen)), 'PUBLIC_ROUTES lists addresses that no longer exist.');
        $this->assertGreaterThan(100, $checked, 'Far fewer addresses were checked than the site has; the route list is not being read.');
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $permissions  [object, operation] pairs granted through one role.
     */
    private function member(array $permissions = []): User
    {
        $user = User::factory()->active()->create();
        UserStatus::query()->create(['user_id' => $user->id, 'status' => UserStatus::STATUS_ACTIVE, 'updated_by' => 0]);

        if ($permissions) {
            $role = Role::query()->create(['name' => 'role-'.$user->id, 'description' => '']);
            foreach ($permissions as [$object, $operation]) {
                RolePermission::query()->create(['role_id' => $role->id, 'object' => $object, 'operation' => $operation]);
            }
            UserRole::query()->create(['user_id' => $user->id, 'role_id' => $role->id]);
        }

        return $user;
    }

    private function superuser(): User
    {
        $user = $this->member();
        UserRole::query()->create(['user_id' => $user->id, 'role_id' => 1]);

        return $user;
    }

    private function assertDenied(TestResponse $response): void
    {
        if ($response->getStatusCode() === 403) {
            $this->addToAssertionCount(1);

            return;
        }

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }
}
