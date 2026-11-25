<?php

namespace Tests\Feature\Http\Controllers;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Authentication;
use App\Models\Authorization;
use App\Models\Gatekeeper;
use App\Models\GatekeeperStatus;
use App\Models\Key;
use App\Models\User;
use App\Models\UserFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

/**
 * The API the door and tool gatekeeper devices call: authenticate with the device's auth key, download
 * the keys allowed through, upload taps and sessions, and report status.
 */
class KeysControllerTest extends TestCase
{
    use RefreshDatabase;

    private Gatekeeper $door;

    private Gatekeeper $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->door = Gatekeeper::factory()->create(['name' => 'Door 1', 'type' => 'doorway', 'is_default' => 1]);
        $this->tool = Gatekeeper::factory()->create(['name' => 'Laser Cutter', 'type' => 'lockout']);
    }

    /**
     * Every device request needs both its auth key and the endpoint (action) it wants.
     *
     * Expects: code 400 if either is missing.
     * Note: this API always answers HTTP 200 and puts the result in a "code" field, because that is
     * what the device firmware reads. The tests check "code", not the HTTP status.
     */
    public function test_it_rejects_requests_without_an_auth_key_or_endpoint()
    {
        $this->post('/keys', ['endpoint' => 'ping'])->assertJson(['code' => '400']);
        $this->post('/keys', ['auth_key' => $this->door->auth_key])->assertJson(['code' => '400']);
    }

    /**
     * The auth key is the device's password. Disabling a gatekeeper in kOS must cut it off.
     *
     * Expects: code 403 for a key that matches no device, and for a disabled device's real key.
     */
    public function test_it_rejects_unknown_and_disabled_gatekeepers()
    {
        $this->post('/keys', ['auth_key' => 'not-a-real-key', 'endpoint' => 'ping'])->assertJson(['code' => '403']);

        $this->door->update(['status' => 'disabled']);
        $this->post('/keys', ['auth_key' => $this->door->auth_key, 'endpoint' => 'ping'])->assertJson(['code' => '403']);
    }

    /**
     * Gatekeeper devices name the action they want in each call. A name kOS does not know, such as
     * a typo or an unsupported request, must get a clear "not found" answer the device can handle.
     *
     * Expects: code 404 for an endpoint name that does not exist, even with a valid device key.
     */
    public function test_it_rejects_unknown_endpoints()
    {
        $this->post('/keys', ['auth_key' => $this->door->auth_key, 'endpoint' => 'self_destruct'])->assertJson(['code' => '404']);
    }

    /**
     * Devices ping regularly. Each request records when and from which IP the device was last
     * seen; the dashboard heartbeat (GatekeepersControllerTest) is built on last_seen.
     *
     * Expects: "PING? PONG!", last_seen within the last minute, and the request's IP stored.
     */
    public function test_ping_answers_and_records_when_and_where_the_device_was_seen()
    {
        $this->post('/keys', ['auth_key' => $this->door->auth_key, 'endpoint' => 'ping'], ['REMOTE_ADDR' => '10.0.0.42'])
            ->assertOk()
            ->assertJson(['code' => '0', 'text' => 'PING? PONG!']);

        $this->door->refresh();
        $this->assertNotNull($this->door->last_seen);
        $this->assertTrue($this->door->last_seen->gt(now()->subMinute()));
        $this->assertSame('10.0.0.42', $this->door->ip_address);
    }

    /**
     * routes/web.php registers /keys for both GET and POST, so devices may use either. Keeps the
     * GET route from being dropped by accident.
     *
     * Expects: a GET ping with query parameters succeeds like a POST one.
     */
    public function test_get_requests_work_the_same_as_post()
    {
        $this->get('/keys?auth_key='.$this->door->auth_key.'&endpoint=ping')->assertJson(['code' => '0']);
    }

    /**
     * The most important rule in kOS: which RFID keys open the doors. A door (a "default"
     * gatekeeper) downloads every key that should get in.
     *
     * Expects: exactly the keys of active members, including a member's second key.
     * Edge cases: members on hiatus and inactive members get no keys; an active member with the
     * keys_disabled flag gets no keys.
     */
    public function test_doors_get_the_keys_of_every_active_member_without_disabled_keys()
    {
        $active = $this->memberWithKey('active-key');
        $this->memberWithKey('hiatus-key', 'hiatus');
        $this->memberWithKey('inactive-key', 'inactive');
        $disabled = $this->memberWithKey('disabled-key');
        UserFlag::query()->create(['user_id' => $disabled->id, 'flag' => 'keys_disabled']);
        Key::query()->forceCreate(['user_id' => $active->id, 'rfid' => 'active-second-key']);

        $keys = $this->getKeys($this->door);

        $this->assertEqualsCanonicalizing(['active-key', 'active-second-key'], $keys);
    }

    /**
     * Tools (non-default gatekeepers) only unlock for members trained on them, which is a safety
     * rule.
     *
     * Expects: only the key of the active, trained member.
     * Edge cases: an untrained active member, a trained but inactive member, and a trained member
     * with keys_disabled all get nothing.
     */
    public function test_tools_only_get_the_keys_of_active_members_trained_on_them()
    {
        $trained = $this->memberWithKey('trained-key');
        $this->memberWithKey('untrained-key');
        $trainedButInactive = $this->memberWithKey('trained-inactive-key', 'inactive');
        $trainedButDisabled = $this->memberWithKey('trained-disabled-key');
        UserFlag::query()->create(['user_id' => $trainedButDisabled->id, 'flag' => 'keys_disabled']);
        foreach ([$trained, $trainedButInactive, $trainedButDisabled] as $user) {
            Authorization::query()->create(['user_id' => $user->id, 'gatekeeper_id' => $this->tool->id]);
        }

        $this->assertSame(['trained-key'], $this->getKeys($this->tool));
    }

    /**
     * Identical tools can share one training record (shared_auth), so training on one counts for
     * both.
     *
     * Expects: the second tool sends the key of a member trained only on the first.
     */
    public function test_a_tool_sharing_authorizations_uses_the_other_tools_training()
    {
        $shared = Gatekeeper::factory()->create(['name' => 'Laser Cutter 2', 'type' => 'lockout', 'shared_auth' => $this->tool->id]);
        $trained = $this->memberWithKey('trained-key');
        Authorization::query()->create(['user_id' => $trained->id, 'gatekeeper_id' => $this->tool->id]);

        $this->assertSame(['trained-key'], $this->getKeys($shared));
    }

    /**
     * Devices upload every key tap in batches. These rows are the entry history behind the
     * activity reports.
     *
     * Expects: each tap saved with the device, result and the time the device recorded (not the
     * upload time). A known key is linked to its member and key.
     * Edge cases: an unknown key (a stranger's fob) is still recorded, with user and key id 0.
     * Not covered: a malformed batch currently gives a 500 after saving the earlier rows; that is a
     * known issue, not tested behaviour.
     */
    public function test_send_auths_records_each_tap_against_the_member_who_owns_the_key()
    {
        $member = $this->memberWithKey('member-key');
        $payload = [
            ['rfid' => 'member-key', 'result' => 'granted', 'metadata' => 'door', 'lock_in' => '2026-10-20 19:05:00', 'lock_out' => null, 'created_at' => '2026-10-20 19:05:00'],
            ['rfid' => 'stranger-key', 'result' => 'denied', 'metadata' => null, 'lock_in' => '2026-10-20 19:06:00', 'lock_out' => null, 'created_at' => '2026-10-20 19:06:00'],
        ];

        $this->post('/keys', ['auth_key' => $this->door->auth_key, 'endpoint' => 'send_auths', 'payload' => json_encode($payload)])
            ->assertJson(['code' => '0', 'text' => 'OK']);

        $known = Authentication::query()->where('rfid', 'member-key')->firstOrFail();
        $this->assertSame($member->id, (int) $known->user_id);
        $this->assertSame($member->keys()->first()->id, (int) $known->key_id);
        $this->assertSame($this->door->id, (int) $known->gatekeeper_id);
        $this->assertSame('granted', $known->result);
        $this->assertSame('2026-10-20 19:05:00', (string) $known->created_at);

        $unknown = Authentication::query()->where('rfid', 'stranger-key')->firstOrFail();
        $this->assertSame(0, (int) $unknown->user_id);
        $this->assertSame(0, (int) $unknown->key_id);
        $this->assertSame('denied', $unknown->result);
    }

    /**
     * Tools also report how long they were used: lock_in is when the member started and lock_out
     * when they finished.
     *
     * Expects: both times saved exactly as sent.
     */
    public function test_send_auths_records_tool_sessions_with_their_end_time()
    {
        $this->memberWithKey('member-key');
        $payload = [['rfid' => 'member-key', 'result' => 'granted', 'metadata' => null, 'lock_in' => '2026-10-20 19:05:00', 'lock_out' => '2026-10-20 20:35:00', 'created_at' => '2026-10-20 19:05:00']];

        $this->post('/keys', ['auth_key' => $this->tool->auth_key, 'endpoint' => 'send_auths', 'payload' => json_encode($payload)]);

        $session = Authentication::query()->where('gatekeeper_id', $this->tool->id)->firstOrFail();
        $this->assertSame('2026-10-20 19:05:00', $session->lock_in);
        $this->assertSame('2026-10-20 20:35:00', $session->lock_out);
    }

    /**
     * A tool in use sends its status with the user's RFID, so kOS can show who is using it.
     *
     * Expects: a status row with "inuse", the status text, the member that key belongs to, and the
     * session start time.
     */
    public function test_a_status_report_records_who_is_using_a_tool()
    {
        $member = $this->memberWithKey('member-key');
        $status = ['status' => 'inuse', 'user_rfid' => 'member-key', 'user_lock_in' => '2026-10-20 19:05:00', 'status_text' => 'Cutting'];

        $this->post('/keys', ['auth_key' => $this->tool->auth_key, 'endpoint' => 'ping', 'status' => json_encode($status)])
            ->assertJson(['code' => '0']);

        $recorded = GatekeeperStatus::query()->where('gatekeeper_id', $this->tool->id)->firstOrFail();
        $this->assertSame('inuse', $recorded->status);
        $this->assertSame('Cutting', $recorded->status_text);
        $this->assertSame($member->id, (int) $recorded->user_id);
        $this->assertSame('2026-10-20 19:05:00', $recorded->lock_in->format('Y-m-d H:i:s'));
    }

    /**
     * An "inuse" report naming a key kOS does not know means something is wrong on the device.
     *
     * Expects: the whole request is refused with code 403 (the code treats it as untrusted).
     */
    public function test_a_status_report_with_an_unknown_key_is_rejected()
    {
        $status = ['status' => 'inuse', 'user_rfid' => 'stranger-key', 'user_lock_in' => '2026-10-20 19:05:00'];

        $this->post('/keys', ['auth_key' => $this->tool->auth_key, 'endpoint' => 'ping', 'status' => json_encode($status)])
            ->assertJson(['code' => '403']);
    }

    /**
     * Devices cannot send a CSRF token, so the device API must stay exempt from CSRF checks. The
     * framework skips these checks in tests, so this calls the middleware with that shortcut turned off.
     *
     * Expects: a POST to /keys passes the middleware, and a POST to an ordinary page is still
     * refused (so the exemption has not become "everything").
     * Upgrade risk: Laravel 13 renames this middleware (PreventRequestForgery); the /keys exemption
     * must move with it, or every door stops syncing.
     */
    public function test_devices_can_post_without_a_csrf_token()
    {
        $middleware = new class($this->app, $this->app['encrypter']) extends VerifyCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        };
        $handled = fn () => response('handled');

        $deviceRequest = Request::create('/keys', 'POST', ['auth_key' => $this->door->auth_key, 'endpoint' => 'ping']);
        $deviceRequest->setLaravelSession($this->app['session.store']);
        $this->assertSame('handled', $middleware->handle($deviceRequest, $handled)->getContent());

        $formRequest = Request::create('/users/check_attributes', 'POST');
        $formRequest->setLaravelSession($this->app['session.store']);
        $this->expectException(TokenMismatchException::class);
        $middleware->handle($formRequest, $handled);
    }

    private function memberWithKey(string $rfid, string $status = 'active'): User
    {
        $user = User::factory()->create(['status' => $status]);
        Key::query()->forceCreate(['user_id' => $user->id, 'rfid' => $rfid]);

        return $user;
    }

    private function getKeys(Gatekeeper $gatekeeper): array
    {
        return $this->post('/keys', ['auth_key' => $gatekeeper->auth_key, 'endpoint' => 'get_keys', 'payload' => '{}'])
            ->assertJson(['code' => '0', 'text' => 'OK'])
            ->json('payload') ?? [];
    }
}
