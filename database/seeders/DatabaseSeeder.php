<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleTableSeeder::class);
    }
}

class RoleTableSeeder extends Seeder
{
    public function run()
    {

        // Create superuser role and give it to first user account
        DB::table('roles')->delete();
        \App\Models\Role::create(['name' => 'Superusers', 'description' => 'Access to all KwartzlabOS functions']);
        \App\Models\UserRole::create(['user_id' => '1', 'role_id' => '1']);
    }
}
