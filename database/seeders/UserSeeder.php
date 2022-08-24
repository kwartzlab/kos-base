<?php

namespace Database\Seeders;

use App\User;
use App\UserStatus;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create();
        UserStatus::factory()->create(['user_id' => $user->id]);
    }
}
