<?php

use App\User;
use App\UserStatus;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = factory(User::class)->create();
        factory(UserStatus::class)->create(['user_id' => $user->id]);
    }
}
