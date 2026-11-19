<?php

namespace Tests\Feature\Http;

use App\Models\Form;
use App\Models\Gatekeeper;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

/**
 * Loads every page a logged in superuser can reach and fails if any of them errors. A broad safety
 * net for framework and package upgrades rather than a test of what each page shows.
 *
 * Limits: only a server error (500 or above) fails. A blank page, a 404 or a redirect passes. Being
 * a superuser skips every permission check, so this says nothing about permissions (see
 * PermissionsTest).
 */
class PageSmokeTest extends TestCase
{
    use RefreshDatabase;

    /** GET routes that change data, log out, or call outside services, so they are not loaded here. */
    private const SKIPPED = [
        'keys',
        'logout',
        'register',
        'password/reset',
        'slack/invite',
        'kiosk/logout',
        'kiosk/unlock',
        'image/lastupload',
    ];

    private User $superuser;

    protected function setUp(): void
    {
        parent::setUp();

        $role = new Role(['name' => 'Superuser', 'description' => '']);
        $role->id = 1;
        $role->save();

        $this->superuser = $this->member();
        UserRole::query()->create(['user_id' => $this->superuser->id, 'role_id' => $role->id]);
    }

    /**
     * Builds the page list from the app's routes, so new pages are covered automatically.
     *
     * Expects: no page errors. If one does, the failure message lists every failing URL with its
     * error.
     * Edge cases: optional URL parameters are dropped (/users/index/{filter?} loads as
     * /users/index). Routes that need a parameter are covered by the next test. API routes,
     * debug-tool routes (starting with _) and the SKIPPED list are left out.
     */
    public function test_every_page_without_parameters_loads()
    {
        $uris = collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route) => in_array('GET', $route->methods()))
            ->reject(fn (Route $route) => str_starts_with($route->uri(), 'api/') || str_starts_with($route->uri(), '_'))
            ->reject(fn (Route $route) => in_array($route->uri(), self::SKIPPED))
            ->map(fn (Route $route) => preg_replace('/\/?\{[^}]+\?\}/', '', $route->uri()))
            ->reject(fn (string $uri) => str_contains($uri, '{'))
            ->unique()
            ->values();

        $this->assertNotEmpty($uris);
        $this->assertPagesLoad($uris->all());
    }

    /**
     * The pages that show one record (a gatekeeper, member, team, form or role).
     *
     * Expects: none of them errors.
     * Note: this list is written by hand. Add new record pages here.
     */
    public function test_pages_for_existing_records_load()
    {
        $team = Team::query()->create(['name' => 'Woodworking', 'description' => 'Wood things']);
        $gatekeeper = Gatekeeper::factory()->create(['name' => 'Table Saw', 'team_id' => $team->id, 'last_seen' => now()]);
        $member = $this->member();
        $form = Form::query()->forceCreate(['name' => 'Feedback', 'description' => 'Tell us', 'status' => 'enabled', 'fields' => '[]']);

        $this->assertPagesLoad([
            "gatekeepers/{$gatekeeper->id}",
            "gatekeepers/{$gatekeeper->id}/edit",
            "gatekeepers/{$gatekeeper->id}/dashboard",
            "users/{$member->id}",
            "users/{$member->id}/edit",
            "members/{$member->id}",
            "members/{$member->id}/profile",
            "teams/{$team->id}",
            "teams/{$team->id}/edit",
            "teams/{$team->id}/dashboard",
            "teams/{$team->id}/requests/training",
            "forms/{$form->id}",
            "forms/{$form->id}/edit",
            'roles/1',
            'roles/1/edit',
        ]);
    }

    /** An active member with the status history every real member has. */
    private function member(): User
    {
        $user = User::factory()->active()->create();
        UserStatus::query()->create(['user_id' => $user->id, 'status' => UserStatus::STATUS_ACTIVE, 'updated_by' => 0]);

        return $user;
    }

    private function assertPagesLoad(array $uris): void
    {
        $failures = [];
        foreach ($uris as $uri) {
            $response = $this->actingAs($this->superuser)->get('/'.ltrim($uri, '/'));
            if ($response->getStatusCode() >= 500) {
                $failures[] = "/{$uri}: {$response->getStatusCode()} ".($response->exception?->getMessage() ?? '');
            }
        }

        $this->assertSame([], $failures, 'Pages failed to load');
    }
}
