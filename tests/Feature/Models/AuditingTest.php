<?php

namespace Tests\Feature\Models;

use App\Models\Gatekeeper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OwenIt\Auditing\Models\Audit;
use Tests\TestCase;

/**
 * Changes to members and gatekeepers are written to the audit log, along with who made them.
 */
class AuditingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Auditing is off for console commands, and tests run from the console.
        config(['audit.console' => true]);
    }

    /**
     * The audit log is how admins find out who changed a member's details. This checks the
     * owen-it/laravel-auditing package is still wired up after package upgrades.
     *
     * Expects: one "updated" audit row with only the changed field (old and new value) and the id
     * of the logged-in member who made the change.
     */
    public function test_changing_a_member_is_audited_with_who_changed_it()
    {
        $admin = User::factory()->create();
        $member = User::factory()->create(['city' => 'Kitchener']);
        $this->actingAs($admin);

        $member->update(['city' => 'Waterloo']);

        $audit = Audit::query()->where('auditable_type', User::class)->where('auditable_id', $member->id)->where('event', 'updated')->firstOrFail();
        $this->assertSame(['city' => 'Kitchener'], $audit->old_values);
        $this->assertSame(['city' => 'Waterloo'], $audit->new_values);
        $this->assertSame($admin->id, (int) $audit->user_id);
    }

    /**
     * User::$auditExclude keeps the "remember me" token out of the audit log (it works like a
     * password for that browser).
     *
     * Expects: the audit row has the city change but no remember_token.
     * Note: the token is assigned directly because it is not mass-assignable; update() would
     * silently ignore it and the test would pass without proving anything.
     */
    public function test_the_remember_token_is_left_out_of_the_audit()
    {
        $member = User::factory()->create();

        $member->remember_token = 'new-token';
        $member->city = 'Cambridge';
        $member->save();

        $audit = Audit::query()->where('auditable_id', $member->id)->where('event', 'updated')->firstOrFail();
        $this->assertArrayNotHasKey('remember_token', $audit->new_values);
        $this->assertArrayHasKey('city', $audit->new_values);
    }

    /**
     * Gatekeepers (doors and tools) are audited too, so enabling or disabling one is traceable.
     *
     * Expects: an "updated" audit row with the old and new status.
     */
    public function test_changing_a_gatekeeper_is_audited()
    {
        $gatekeeper = Gatekeeper::factory()->create(['status' => 'enabled']);

        $gatekeeper->update(['status' => 'disabled']);

        $audit = Audit::query()->where('auditable_type', Gatekeeper::class)->where('event', 'updated')->firstOrFail();
        $this->assertSame(['status' => 'enabled'], $audit->old_values);
        $this->assertSame(['status' => 'disabled'], $audit->new_values);
    }
}
