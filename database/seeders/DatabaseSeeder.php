<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolesTableSeeder::class,
            UsersTableSeeder::class,
            UserSkillsTableSeeder::class,
            UserFlagsTableSeeder::class,
            UserSocialsTableSeeder::class,
            UsersStatusesTableSeeder::class,
            UserCertsTableSeeder::class,
            KeysTableSeeder::class,
            FormsTableSeeder::class,
            GatekeepersTableSeeder::class
        ]);
    }
}
