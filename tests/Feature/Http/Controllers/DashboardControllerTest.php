<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\GoogleCalendar\Event;
use Tests\Fakes\Event as FakeEvent;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->active()->create();
    }

    public function testItLoadsCalendarEventsInProductionEnvironment()
    {
        config(['app.env' => 'production']);
        $this->app->bind(Event::class, fn () => new FakeEvent());

        $this->followingRedirects()->actingAs($this->user)->get('/');

        $this->assertEquals(1, FakeEvent::$callCount);
    }

    public function provideNonProductionEnvironments(): array
    {
        return [
            'testing' => ['environment' => 'testing'],
            'local' => ['environment' => 'local'],
            'dev' => ['environment' => 'dev'],
        ];
    }

    /**
     * @dataProvider provideNonProductionEnvironments
     */
    public function testItDoesNotLoadCalendarEventsInNonProductionEnvironment(string $environment)
    {
        config(['app.env' => $environment]);
        $this->app->bind(Event::class, fn () => new FakeEvent());

        $this->actingAs($this->user)->get('/');

        $this->assertEquals(0, FakeEvent::$callCount);
    }

    protected function tearDown(): void
    {
        FakeEvent::$callCount = 0;
        parent::tearDown();
    }
}
