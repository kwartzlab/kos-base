<?php

namespace Tests\Feature\Http\Controllers\TeamsController;

use App\Models\Gatekeeper;
use App\Models\Team;
use App\Models\TeamRequest;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Members ask for training on a tool from the training page; the request goes to the tool's team.
 *
 * Not covered here: who may pass, fail or cancel a request, and who may see the team's list of
 * requests. Those rules are tested in Tests\Feature\Security\AuthorizationFixesTest.
 */
class TrainingRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $member;

    private Team $team;

    private Gatekeeper $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->member = User::factory()->active()->create();
        UserStatus::query()->create(['user_id' => $this->member->id, 'status' => 'active', 'updated_by' => 0]);
        $this->team = Team::query()->create(['name' => 'Laser Cutting', 'description' => '']);
        $this->tool = Gatekeeper::factory()->create(['name' => 'Laser Cutter', 'team_id' => $this->team->id]);
    }

    /**
     * Expects: a "new" training request for this member and tool, assigned to the tool's team so
     * its trainers see it.
     */
    public function test_a_member_can_request_training_on_a_tool()
    {
        $this->actingAs($this->member)->get("/teams/training_request/{$this->tool->id}")->assertJson(['status' => 'success']);

        $request = TeamRequest::query()->sole();
        $this->assertSame(['training', 'new', $this->member->id, $this->team->id, $this->tool->id],
            [$request->request_type, $request->status, (int) $request->user_id, (int) $request->team_id, (int) $request->gatekeeper_id]);
    }

    /**
     * Prevents duplicate requests piling up for trainers.
     *
     * Expects: "Request already exists." and still only one request.
     */
    public function test_a_second_request_for_the_same_tool_is_refused_while_the_first_is_open()
    {
        $this->actingAs($this->member)->get("/teams/training_request/{$this->tool->id}");

        $this->actingAs($this->member)->get("/teams/training_request/{$this->tool->id}")->assertJson(['status' => 'error', 'message' => 'Request already exists.']);
        $this->assertSame(1, TeamRequest::query()->count());
    }

    /**
     * A cancelled request no longer counts as open (failed ones do not either).
     *
     * Expects: the member can cancel their own request and then make a new one.
     */
    public function test_a_member_can_ask_again_after_cancelling()
    {
        $this->actingAs($this->member)->get("/teams/training_request/{$this->tool->id}");
        $first = TeamRequest::query()->sole();

        $this->actingAs($this->member)->get("/teams/training_cancel/{$first->id}")->assertJson(['status' => 'success']);
        $this->assertSame('cancelled', $first->fresh()->status);

        $this->actingAs($this->member)->get("/teams/training_request/{$this->tool->id}")->assertJson(['status' => 'success']);
        $this->assertSame(2, TeamRequest::query()->count());
    }

    /**
     * A member follows a training link for a tool that has since been disabled, or changes the id
     * in the address to one that does not exist. No request may be created, because no trainer
     * could ever act on it.
     *
     * Expects: a redirect to the training page with "Unknown Gatekeeper." for a disabled tool and
     * for an id that does not exist, and no request created.
     */
    public function test_training_cannot_be_requested_on_a_disabled_or_unknown_tool()
    {
        $this->tool->update(['status' => 'disabled']);

        $this->actingAs($this->member)->get("/teams/training_request/{$this->tool->id}")->assertRedirect('/teams/training')->assertSessionHas('error', 'Unknown Gatekeeper.');
        $this->actingAs($this->member)->get('/teams/training_request/999999')->assertRedirect('/teams/training');
        $this->assertSame(0, TeamRequest::query()->count());
    }

    /**
     * Some tools are not assigned to a team yet (team_id 0).
     *
     * Expects: the request is still saved, with team 0, instead of failing.
     */
    public function test_a_tool_without_a_team_still_takes_requests()
    {
        $loneTool = Gatekeeper::factory()->create(['name' => 'Drill Press', 'team_id' => 0]);

        $this->actingAs($this->member)->get("/teams/training_request/{$loneTool->id}")->assertJson(['status' => 'success']);

        $this->assertSame(0, (int) TeamRequest::query()->sole()->team_id);
    }
}
