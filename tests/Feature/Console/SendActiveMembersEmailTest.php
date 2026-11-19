<?php

namespace Tests\Feature\Console;

use App\Mail\RecentMembers;
use App\Models\Authentication;
use App\Models\Gatekeeper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The monthly generate:recentmembersemail job. It emails the membership admins a list of the
 * active members who have been coming in, counted from taps on the doors over the past month.
 *
 * Mail is faked, so nothing is sent.
 */
class SendActiveMembersEmailTest extends TestCase
{
    use RefreshDatabase;

    private Gatekeeper $door;

    private Gatekeeper $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->door = Gatekeeper::factory()->create(['name' => 'Front Door', 'type' => 'doorway']);
        $this->tool = Gatekeeper::factory()->create(['name' => 'Table Saw', 'type' => 'lockout']);
    }

    /**
     * Runs the job against members with different visit histories and reads the list it emails.
     *
     * Why: the admins use this list to see who is actually using the space. It depends on date
     * arithmetic ("the past month") and on a count, both of which are easy to break in a
     * framework or date-library upgrade without any error appearing.
     *
     * Expects: one email, titled with last month's name, listing only the active member with four
     * door taps in the past month.
     * Edge cases, all left off the list: a member with exactly three taps (the code asks for more
     * than three); a member whose taps were on a tool, not a door; a member whose taps are older
     * than a month; and a member who is no longer active.
     * Note: the job's own description says "3 or more" but the code counts "more than 3". This
     * test records what the code does today.
     */
    public function test_it_emails_the_active_members_with_more_than_three_door_taps_in_the_past_month()
    {
        Mail::fake();

        $regular = $this->memberWithTaps('Regular', 'active', $this->door, 4, now()->subDays(3));
        $this->memberWithTaps('Occasional', 'active', $this->door, 3, now()->subDays(3));
        $this->memberWithTaps('Toolonly', 'active', $this->tool, 4, now()->subDays(3));
        $this->memberWithTaps('Lapsed', 'active', $this->door, 4, now()->subMonths(2));
        $this->memberWithTaps('Departed', 'inactive', $this->door, 4, now()->subDays(3));

        $this->artisan('generate:recentmembersemail')->assertSuccessful();

        Mail::assertSent(RecentMembers::class, 1);
        Mail::assertSent(RecentMembers::class, function (RecentMembers $mail) use ($regular) {
            return $mail->member_list->all() === [$regular->get_name()]
                && $mail->month_reported === now()->subMonth()->format('F Y');
        });
    }

    /** Creates a member and records the given number of taps for them on one gatekeeper. */
    private function memberWithTaps(string $firstName, string $status, Gatekeeper $gatekeeper, int $taps, $when): User
    {
        $user = User::factory()->create(['status' => $status, 'first_name' => $firstName, 'first_preferred' => $firstName]);
        for ($i = 0; $i < $taps; $i++) {
            Authentication::query()->forceCreate([
                'user_id' => $user->id,
                'key_id' => 0,
                'gatekeeper_id' => $gatekeeper->id,
                'rfid' => 'fob-'.$user->id,
                'result' => 'granted',
                'lock_in' => $when->copy()->addMinutes($i)->toDateTimeString(),
            ]);
        }

        return $user;
    }
}
