<?php

namespace Tests\Feature\Database;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Checks columns of a freshly migrated database that later migrations modified. From Laravel 11, a
 * column change drops any attribute the migration does not repeat, such as nullable.
 */
class SchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Migration 2019_12_02_104131_modify_forms_table changes forms.description with ->change() and
     * does not repeat nullable(). Laravel 9 and 10 keep the old nullable; Laravel 11 drops it, so
     * forms without a description would fail to save on a fresh database.
     *
     * Expects: inserting a form with a null description works.
     * Upgrade risk: expected to FAIL after the Laravel 11 upgrade until the migration (or a new one)
     * says nullable() explicitly. That failure is the point of the test.
     */
    public function test_a_form_can_be_saved_without_a_description()
    {
        DB::table('forms')->insert(['name' => 'No description', 'description' => null]);

        $this->assertNull(DB::table('forms')->where('name', 'No description')->value('description'));
    }

    /**
     * The other ->change() migration (2018_08_09_102956_modify_users_phone) makes users.phone
     * nullable. Phone is optional on the membership application, so this must keep working.
     *
     * Expects: inserting a member with a null phone works. This is a control case: the migration
     * already says nullable(), so it should pass on every Laravel version.
     * Note: these two are the only ->change() calls in database/migrations; add a test here for
     * any new one.
     */
    public function test_a_member_can_be_saved_without_a_phone_number()
    {
        DB::table('users')->insert(User::factory()->raw(['email' => 'no.phone@example.com', 'phone' => null]));

        $this->assertNull(DB::table('users')->where('email', 'no.phone@example.com')->value('phone'));
    }
}
