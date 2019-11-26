<?php

use Illuminate\Database\Seeder;

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

// Seed Superuser role
class RoleTableSeeder extends Seeder {

    public function run()
    {
        DB::table('roles')->delete();
        \App\Role::create(['name' => 'Superusers', 'description' => 'Access to all KwartzlabOS functions']);
    }

}