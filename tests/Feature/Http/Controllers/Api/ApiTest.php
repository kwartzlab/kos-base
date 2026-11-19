<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\ApiToken;
use App\Models\FormSubmission;
use App\Models\FormSubmissionOutbox;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

/**
 * The token-protected API used by outside services: membership coordinator tools read members,
 * form submissions (through an outbox they mark as processed) and status changes.
 */
class ApiTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-api-token';

    /** The only member fields the API may return; anything else (phone, address, notes, password) is private. */
    private const DIRECTORY_FIELDS = ['id', 'first_name', 'last_name', 'first_preferred', 'last_preferred', 'email', 'status', 'member_id'];

    private ApiToken $apiToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiToken = ApiToken::query()->forceCreate(['name' => 'coordinator', 'token_hash' => ApiToken::hashToken(self::TOKEN)]);
    }

    /** API routes that are meant to be open to anyone. Every other route under /api must ask for a token. */
    private const PUBLIC_ROUTES = ['api/public-info'];

    /**
     * Every route under /api, taken from the app's own route list, is called without a token.
     *
     * Why: the API returns member names, emails and status changes, and one route lets the caller
     * mark form submissions as processed. Reading the route list, instead of keeping a list in
     * this test, means a new or moved API route is checked automatically and cannot be left open
     * by forgetting to add it here.
     *
     * Expects: 401 "API token required." for every route and method, except PUBLIC_ROUTES. The
     * failure message lists each route that answered anything else.
     * Note: the routes that name a record are called with a record that exists. For an id that
     * does not exist they currently answer 404 before the token is checked, which is not tested
     * here.
     * Upgrade risk: Laravel 11 changes how routes/api.php and the api middleware are registered.
     */
    public function test_every_api_route_requires_a_token()
    {
        // Real records for the routes that name one, so the answer comes from the token check
        // and not from "no such record".
        $submission = $this->submission();
        $ids = [
            'user' => User::factory()->create()->id,
            'form_submission' => $submission->id,
            'form_submission_outbox' => $this->outboxEntry($submission)->id,
        ];
        $unprotected = [];
        $checked = 0;

        foreach (RouteFacade::getRoutes()->getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/') || in_array($route->uri(), self::PUBLIC_ROUTES)) {
                continue;
            }
            $uri = '/'.preg_replace_callback('/\{(\w+)\??\}/', fn (array $match) => $ids[$match[1]], $route->uri());

            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                $response = $this->json($method, $uri);
                $checked++;
                if ($response->getStatusCode() !== 401 || $response->json('message') !== 'API token required.') {
                    $unprotected[] = "{$method} {$uri}: {$response->getStatusCode()}";
                }
            }
        }

        $this->assertGreaterThan(10, $checked, 'Expected to find the API routes.');
        $this->assertSame([], $unprotected, 'API routes that answered without a token');
    }

    /**
     * A caller sends a token that was never issued.
     *
     * Why: asking for a token is not enough; it has to be one kOS issued. The test above already
     * shows every route goes through the token check, so one route is enough here.
     *
     * Expects: 401 "Invalid API token." for a token that is not in api_tokens.
     */
    public function test_an_unknown_token_is_rejected()
    {
        $this->withToken('not-a-real-token')->getJson('/api/token')->assertUnauthorized()->assertJson(['message' => 'Invalid API token.']);
    }

    /**
     * Revoking (php artisan api-token:revoke) must stop a leaked token at once.
     *
     * Expects: 401 for a token whose revoked_at is set.
     */
    public function test_a_revoked_token_is_rejected()
    {
        $this->apiToken->forceFill(['revoked_at' => now()->subMinute()])->save();

        $this->withToken(self::TOKEN)->getJson('/api/token')->assertUnauthorized();
    }

    /**
     * Tokens can have an expiry date.
     *
     * Expects: accepted while expires_at is in the future, 401 once it has passed.
     * Edge case: both sides are checked with the same token, so an inverted date comparison fails.
     */
    public function test_an_expired_token_is_rejected_and_an_unexpired_one_accepted()
    {
        $this->apiToken->forceFill(['expires_at' => now()->addDay()])->save();
        $this->withToken(self::TOKEN)->getJson('/api/token')->assertOk();

        $this->apiToken->forceFill(['expires_at' => now()->subMinute()])->save();
        $this->withToken(self::TOKEN)->getJson('/api/token')->assertUnauthorized();
    }

    /**
     * Clients may send the token as "Authorization: Bearer ..." (the other tests) or in an
     * X-Api-Token header.
     *
     * Expects: the header works, and /api/token reports which token was used.
     */
    public function test_the_token_can_also_be_sent_in_the_x_api_token_header()
    {
        $this->withHeaders(['X-Api-Token' => self::TOKEN])->getJson('/api/token')->assertOk()->assertJson(['name' => 'coordinator']);
    }

    /**
     * last_used_at (shown by api-token:list) tells admins whether a token is still in use before
     * they revoke it.
     *
     * Expects: last_used_at is set after a request.
     */
    public function test_using_a_token_records_when_it_was_last_used()
    {
        $this->withToken(self::TOKEN)->getJson('/api/token')->assertOk();

        $this->assertNotNull($this->apiToken->fresh()->last_used_at);
    }

    /**
     * Outside tools may read the member directory, but not private details.
     *
     * Expects: each member has exactly the DIRECTORY_FIELDS, no more.
     * Note: checking the exact field list (rather than "password is missing") means any new column
     * that starts leaking, such as phone or address, fails the test.
     */
    public function test_users_are_listed_without_private_details()
    {
        $member = User::factory()->create(['first_name' => 'Ada', 'status' => 'active']);

        $response = $this->withToken(self::TOKEN)->getJson('/api/users')->assertOk();

        $response->assertJsonPath('data.0.id', $member->id);
        $response->assertJsonPath('data.0.first_name', 'Ada');
        $this->assertEqualsCanonicalizing(self::DIRECTORY_FIELDS, array_keys($response->json('data.0')));
    }

    /**
     * Same rule for one member.
     *
     * Expects: the member's directory fields only, and 404 for an id that does not exist.
     * Note: the exact field check matters here. The model already hides password, so a check for
     * "no password" alone would pass even if this returned the whole member record.
     */
    public function test_a_single_user_is_returned_without_private_details()
    {
        $member = User::factory()->create(['status' => 'hiatus']);

        $response = $this->withToken(self::TOKEN)->getJson("/api/users/{$member->id}")->assertOk();

        $response->assertJson(['id' => $member->id, 'email' => $member->email, 'status' => 'hiatus']);
        $this->assertEqualsCanonicalizing(self::DIRECTORY_FIELDS, array_keys($response->json()));
        $this->withToken(self::TOKEN)->getJson('/api/users/999999')->assertNotFound();
    }

    /**
     * Clients choose the page size with per_page, within limits, so one request cannot dump
     * thousands of rows.
     *
     * Expects: per_page is honoured, capped at 200.
     * Edge cases: 5000 (capped to 200) and 0 (raised to 1).
     */
    public function test_list_page_size_is_capped()
    {
        User::factory(3)->create();

        $this->withToken(self::TOKEN)->getJson('/api/users?per_page=2')->assertJsonPath('per_page', 2)->assertJsonCount(2, 'data');
        $this->withToken(self::TOKEN)->getJson('/api/users?per_page=5000')->assertJsonPath('per_page', 200);
        $this->withToken(self::TOKEN)->getJson('/api/users?per_page=0')->assertJsonPath('per_page', 1);
    }

    /**
     * Form answers are stored as a JSON string. The API returns them as real JSON so clients do not
     * decode twice.
     *
     * Expects: the submission with "data" as an object.
     */
    public function test_a_form_submission_is_returned_with_its_data_decoded()
    {
        $submission = $this->submission(['answer' => 'yes']);

        $this->withToken(self::TOKEN)->getJson("/api/form_submissions/{$submission->id}")
            ->assertOk()
            ->assertJson(['form_submission_id' => $submission->id, 'form_name' => 'Membership Application', 'data' => ['answer' => 'yes']]);
    }

    /**
     * The outbox is a queue of new form submissions waiting for an outside service (for example
     * the membership coordinators' tools) to pick them up.
     *
     * Expects: the list shows processed and unprocessed entries, and per_page is capped at 200.
     * Regression: this URL always answered 404, because the /form_submissions/{id} route was
     * registered first and took "outbox" as an id.
     */
    public function test_the_outbox_lists_every_entry()
    {
        $this->outboxEntry($this->submission());
        $this->outboxEntry($this->submission(), ['processed_at' => now()]);

        $this->withToken(self::TOKEN)->getJson('/api/form_submissions/outbox')->assertOk()->assertJsonCount(2, 'data');
        $this->withToken(self::TOKEN)->getJson('/api/form_submissions/outbox?per_page=5000')->assertJsonPath('per_page', 200);
    }

    /**
     * Clients work through the outbox one entry at a time, oldest first.
     *
     * Expects: the oldest unprocessed entry, with its submission data.
     * Edge case: an even older entry that is already processed is skipped.
     */
    public function test_next_returns_the_oldest_unprocessed_submission()
    {
        $this->outboxEntry($this->submission(), ['processed_at' => now(), 'created_at' => now()->subDays(3)]);
        $oldest = $this->outboxEntry($this->submission(['name' => 'oldest']), ['created_at' => now()->subDays(2)]);
        $this->outboxEntry($this->submission(), ['created_at' => now()->subDay()]);

        $this->withToken(self::TOKEN)->getJson('/api/form_submissions/outbox/next')
            ->assertOk()
            ->assertJson(['outbox_id' => $oldest->id, 'data' => ['name' => 'oldest']]);
    }

    /**
     * An entry the client failed to process waits 5 minutes before it is offered again, so one bad
     * entry cannot block the queue.
     *
     * Expects: the entry that failed exactly 5 minutes ago is offered (with its last error); the
     * older one that failed 4 minutes 59 seconds ago is skipped.
     * Edge case: both sides of the 5-minute limit, with the clock frozen.
     */
    public function test_next_skips_an_entry_that_failed_in_the_last_five_minutes()
    {
        $this->travelTo(now()->startOfSecond());
        $this->outboxEntry($this->submission(), ['created_at' => now()->subDays(2), 'last_error' => 'Slack down', 'last_error_at' => now()->subMinutes(5)->addSecond()]);
        $retryable = $this->outboxEntry($this->submission(), ['created_at' => now()->subDay(), 'last_error' => 'Slack down', 'last_error_at' => now()->subMinutes(5)]);

        $this->withToken(self::TOKEN)->getJson('/api/form_submissions/outbox/next')->assertJson(['outbox_id' => $retryable->id, 'last_error' => 'Slack down']);
    }

    /**
     * Outside tools ask for the next form submission to process. Most of the time there is none,
     * and that must read as "nothing to do", not as a failure the tool would report.
     *
     * Expects: 204 No Content (not an error) when the outbox is empty.
     */
    public function test_next_returns_no_content_when_nothing_is_waiting()
    {
        $this->withToken(self::TOKEN)->getJson('/api/form_submissions/outbox/next')->assertNoContent();
    }

    /**
     * After handling an entry, the client marks it done.
     *
     * Expects: processed_at is set and "next" no longer offers it.
     */
    public function test_an_outbox_entry_can_be_marked_processed()
    {
        $entry = $this->outboxEntry($this->submission());

        $this->withToken(self::TOKEN)->postJson("/api/form_submissions/outbox/{$entry->id}")->assertOk()->assertJson(['outbox_id' => $entry->id]);

        $this->assertNotNull($entry->fresh()->processed_at);
        $this->withToken(self::TOKEN)->getJson('/api/form_submissions/outbox/next')->assertNoContent();
    }

    /**
     * Sending last_error with the same request marks a failure instead.
     *
     * Expects: still unprocessed, with the error message and time saved (which start the 5-minute
     * wait tested above).
     */
    public function test_an_outbox_entry_can_be_marked_failed()
    {
        $entry = $this->outboxEntry($this->submission());

        $this->withToken(self::TOKEN)->postJson("/api/form_submissions/outbox/{$entry->id}", ['last_error' => 'Mailbox full'])->assertOk();

        $entry->refresh();
        $this->assertNull($entry->processed_at);
        $this->assertSame('Mailbox full', $entry->last_error);
        $this->assertNotNull($entry->last_error_at);
    }

    /**
     * A tool can fetch one outbox entry again by its id, for example to retry a submission it
     * failed to process. The entry must come back with the submission's data.
     *
     * Expects: one outbox entry by id, with its submission data.
     */
    public function test_a_single_outbox_entry_is_returned()
    {
        $entry = $this->outboxEntry($this->submission(['name' => 'Ada']));

        $this->withToken(self::TOKEN)->getJson("/api/form_submissions/outbox/{$entry->id}")->assertOk()->assertJson(['outbox_id' => $entry->id, 'data' => ['name' => 'Ada']]);
    }

    /**
     * Clients poll status changes by id: "latest" tells them where the list currently ends.
     *
     * Expects: {"id": 0} when there are none, otherwise the highest id.
     */
    public function test_latest_returns_the_newest_status_change_or_zero()
    {
        $this->withToken(self::TOKEN)->getJson('/api/user_statuses/latest')->assertExactJson(['id' => 0]);

        $user = User::factory()->create();
        $this->status($user, 'applicant', now()->subDays(10));
        $newest = $this->status($user, 'active', now()->subDays(3));

        $this->withToken(self::TOKEN)->getJson('/api/user_statuses/latest')->assertJson(['id' => $newest->id]);
    }

    /**
     * Clients then step through status changes one at a time, starting from the id they reached.
     *
     * Expects: the first change when no id is given; the change with exactly that id when one is
     * given (the starting id is included); 204 past the end.
     */
    public function test_index_returns_the_first_status_change_from_the_given_id()
    {
        $user = User::factory()->create();
        $first = $this->status($user, 'applicant', now()->subDays(10));
        $second = $this->status($user, 'active', now()->subDays(3));

        $this->withToken(self::TOKEN)->getJson('/api/user_statuses')->assertJson(['id' => $first->id, 'status' => 'applicant']);
        $this->withToken(self::TOKEN)->getJson("/api/user_statuses?status_id={$second->id}")->assertJson(['id' => $second->id, 'status' => 'active']);
        $this->withToken(self::TOKEN)->getJson('/api/user_statuses?status_id='.($second->id + 1))->assertNoContent();
    }

    /**
     * "upcoming" lists changes recorded for the coming days (for example hiatuses about to start),
     * so coordinators can prepare.
     *
     * Expects: by default only changes within 7 days; ?days=30 widens the window.
     * Edge case: a change in the past is never included.
     */
    public function test_upcoming_returns_status_changes_in_the_next_days_only()
    {
        $user = User::factory()->create();
        $this->status($user, 'active', now()->subDay());
        $soon = $this->status($user, 'hiatus', now()->addDays(3));
        $later = $this->status($user, 'active', now()->addDays(20));

        $this->withToken(self::TOKEN)->getJson('/api/user_statuses/upcoming')->assertJsonCount(1)->assertJsonPath('0.id', $soon->id);
        $this->withToken(self::TOKEN)->getJson('/api/user_statuses/upcoming?days=30')->assertJsonCount(2)->assertJsonPath('1.id', $later->id);
    }

    /**
     * The exact limits of the upcoming window.
     *
     * Expects: a change exactly 7 days ahead is included by default and one a second later is not;
     * ?days=5000 is capped at 90 days, so a change 90 days and a second ahead is left out.
     * Edge case: both sides of each limit, with the clock frozen.
     */
    public function test_upcoming_looks_seven_days_ahead_by_default_and_at_most_ninety()
    {
        $this->travelTo(now()->startOfSecond());
        $user = User::factory()->create();
        $lastDay = $this->status($user, 'hiatus', now()->addDays(7));
        $this->status($user, 'active', now()->addDays(7)->addSecond());
        $this->status($user, 'inactive', now()->addDays(90)->addSecond());

        $this->withToken(self::TOKEN)->getJson('/api/user_statuses/upcoming')->assertJsonCount(1)->assertJsonPath('0.id', $lastDay->id);
        $this->withToken(self::TOKEN)->getJson('/api/user_statuses/upcoming?days=5000')->assertJsonCount(2);
    }

    private function submission(array $data = []): FormSubmission
    {
        return FormSubmission::query()->forceCreate([
            'form_id' => 1,
            'form_name' => 'Membership Application',
            'special_form' => 'new_user_app',
            'submitted_by' => 0,
            'user_id' => 0,
            'data' => json_encode($data),
        ]);
    }

    private function outboxEntry(FormSubmission $submission, array $attributes = []): FormSubmissionOutbox
    {
        $entry = new FormSubmissionOutbox(['form_submission_id' => $submission->id]);
        $entry->forceFill(array_merge(['created_at' => now()], $attributes))->save();

        return $entry;
    }

    private function status(User $user, string $status, $date): UserStatus
    {
        return UserStatus::query()->create(['user_id' => $user->id, 'status' => $status, 'updated_by' => 0, 'created_at' => $date, 'updated_at' => $date]);
    }
}
