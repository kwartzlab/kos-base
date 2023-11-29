<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $superuserUser = User::factory()->active()->create([
            'first_name' => 'Superuser',
            'last_name' => 'Superuser',
            'email' => 'superuser-dev@kwartzlab.ca',
        ]);

        $superuserUser->roles(true)
            ->attach(Role::query()->where('name', Role::ROLE_SUPERUSER_NAME)->firstOrFail());

        $bodUser = User::factory()->active()->create([
            'first_name' => 'BoD',
            'last_name' => 'BoD',
            'email' => 'bod-dev@kwartzlab.ca',
        ]);

        $bodUser->roles(true)
            ->attach(Role::query()->where('name', Role::ROLE_BOD_NAME)->firstOrFail());

        $keyFobAssignerUser = User::factory()->active()->create([
            'first_name' => 'Key Fob Assigner',
            'last_name' => 'Key Fob Assigner',
            'email' => 'kfa-dev@kwartzlab.ca',
        ]);

        $keyFobAssignerUser->roles(true)
            ->attach(Role::query()->where('name', Role::ROLE_KEY_FOB_ASSIGNER_NAME)->firstOrFail());

        $bookkeeperUser = User::factory()->active()->create([
            'first_name' => 'Bookkeeper',
            'last_name' => 'Bookkeeper',
            'email' => 'bookkeeper-dev@kwartzlab.ca',
        ]);

        $bookkeeperUser->roles(true)
            ->attach(Role::query()->where('name', Role::ROLE_BOOKKEEPER_NAME)->firstOrFail());

        $this->createUsers(UserStatus::STATUS_ACTIVE, 50);
        $this->createUsers(UserStatus::STATUS_INACTIVE, 20);
        $this->createUsers(UserStatus::STATUS_SUSPENDED, 10);
        $this->createUsers(UserStatus::STATUS_TERMINATED, 10);
        $this->createUsers(UserStatus::STATUS_INACTIVE_ABANDONED, 5);
        $this->createUsers(UserStatus::STATUS_HIATUS, 5);
        $this->createUsers(UserStatus::STATUS_APPLICANT_ABANDONED, 10);
        $this->createUsers(UserStatus::STATUS_APPLICANT_DENIED, 5);
        $this->createUsers(UserStatus::STATUS_APPLICANT, 5);
    }

    private function createUsers(string $status, int $count = 1): void
    {
        User::factory()->count($count)->status($status)->create();
    }
}
