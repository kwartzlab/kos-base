<?php

namespace Tests\Feature\Http\Controllers\Reports;

use App\User;
use DOMElement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class FobUsageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(factory(User::class)->states(['active', 'admin'])->create());
    }

    public function testItRendersTheFobUsageReportPage(): void
    {
        $this->get('/reports/fob-usage')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<h1>Fob Usage Report</h1>');
    }
}
