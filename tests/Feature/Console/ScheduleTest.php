<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

/**
 * The jobs kOS runs by itself on a timetable. The other tests check what each job does; this one
 * checks that the jobs are still on the timetable, and when.
 */
class ScheduleTest extends TestCase
{
    public static function provideScheduledJobs(): array
    {
        return [
            'nightly status changes, 1:00 every night' => ['process:userstatus', '0 1 * * *'],
            'active members email, 00:05 on the 1st of each month' => ['generate:recentmembersemail', '5 0 1 * *'],
        ];
    }

    /**
     * Looks the job up in the app's schedule and reads the time it is set to run.
     *
     * Why: if the nightly job silently stops being scheduled, status changes recorded in advance
     * never take effect. A member whose membership ends keeps a working fob, and a new member
     * never gets one, and nothing else in the test suite would notice.
     *
     * Expects: each job is scheduled exactly once, at the time shown (in cron notation).
     * Upgrade risk: Laravel 11 moves the schedule out of app/Console/Kernel.php. This test is
     * expected to catch a job that gets lost in that move.
     *
     * @dataProvider provideScheduledJobs
     */
    public function test_the_job_is_scheduled_at_the_expected_time(string $command, string $expression)
    {
        $matching = collect($this->app->make(Schedule::class)->events())
            ->filter(fn (Event $event) => str_contains((string) $event->command, $command))
            ->values();

        $this->assertCount(1, $matching, "{$command} should be scheduled exactly once.");
        $this->assertSame($expression, $matching->first()->expression);
    }
}
