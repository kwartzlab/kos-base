<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNameTest extends TestCase
{
    use RefreshDatabase;

    private $unique_name = 'noenveriovfero';

    public function testGetNameWorksWithPreferredLastName(): void
    {
        $user = User::factory()->create();

        $user->first_preferred = null;
        $user->last_preferred = $this->unique_name;

        $expected_name = $user->first_name.' '.$this->unique_name;

        $this->assertStringEndsWith($this->unique_name, $user->get_name());
        $this->assertEquals($expected_name, $user->get_name());
    }

    public function testGetNameWorksWithPreferredFirstName(): void
    {
        $user = User::factory()->create();

        $user->first_preferred = $this->unique_name;
        $user->last_preferred = null;

        $expected_name = $this->unique_name.' '.$user->last_name;

        $this->assertStringStartsWith($this->unique_name, $user->get_name());
        $this->assertEquals($expected_name, $user->get_name());
    }

    public function testGetNameWorksWithNoPreferredName(): void
    {
        $user = User::factory()->create();

        $user->first_preferred = null;
        $user->last_preferred = null;

        $expected_name = $user->first_name.' '.$user->last_name;

        $this->assertStringNotContainsString($this->unique_name, $user->get_name());
        $this->assertEquals($expected_name, $user->get_name());
    }

    public function testGetNameWorksWithBothPreferredNames(): void
    {
        $user = User::factory()->create();

        $user->first_preferred = $this->unique_name;
        $user->last_preferred = $this->unique_name;

        $expected_name = $user->first_preferred.' '.$user->last_preferred;

        $this->assertStringContainsString($this->unique_name, $user->get_name());
        $this->assertEquals($expected_name, $user->get_name());
    }
}
