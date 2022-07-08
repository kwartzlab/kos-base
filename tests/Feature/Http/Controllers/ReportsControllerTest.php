<?php

namespace Tests\Feature\Http\Controllers;

use App\User;
use DOMElement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\DomCrawler\Crawler;
use Tests\TestCase;

class ReportsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(factory(User::class)->states(['active', 'admin'])->create());
    }

    public function testItRendersTheReportPage(): void
    {
        $this->get('/reports')
            ->assertStatus(Response::HTTP_OK)
            ->assertSee('<h1>Reports</h1>');
    }

    public function testItRendersTheFormToGenerateAFobUsageReport(): void
    {
        factory(User::class)->times(5)->create();
        $allUsers = User::all();

        $html = $this->get('/reports')->getContent();

        $crawler = new Crawler($html);
        $this->assertCount(1, $crawler->filter('form#fob-usage-form input#from'));
        $this->assertCount(1, $crawler->filter('form#fob-usage-form input#to'));
        $this->assertCount(1, $crawler->filter('form#fob-usage-form select#member'));
        $allUsers->each(function (User $user) use ($crawler) {
            $selectOptions = collect($crawler->filter('form#fob-usage-form select#member option'))
                ->filter(function (DOMElement $option) use ($user) {
                    return ((int) $option->getAttribute('value')) === $user->id;
                });

            $this->assertCount(1, $selectOptions);
        });
    }
}
