<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserSkill;
use App\Models\UserStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->seedRolesTable();
        $this->seedUsersTable();
        $this->seedUserSkillsTable();
    }

    private function seedRolesTable(): void
    {
        Role::query()->create(['name' => Role::ROLE_SUPERUSER_NAME, 'description' => '']);
        $bodRole = Role::query()->create(['name' => Role::ROLE_BOD_NAME, 'description' => '']);
        $keyFobAssignerRole = Role::query()->create(['name' => Role::ROLE_KEY_FOB_ASSIGNER_NAME, 'description' => '']);
        $bookkeeperRole = Role::query()->create(['name' => Role::ROLE_BOOKKEEPER_NAME, 'description' => '']);

        RolePermission::query()->create(['role_id' => $bodRole->id, 'object' => 'keys', 'operation' => 'manage']);
        RolePermission::query()->create(['role_id' => $bodRole->id, 'object' => 'users', 'operation' => 'manage']);
        RolePermission::query()->create(['role_id' => $bodRole->id, 'object' => 'gatekeepers', 'operation' => 'manage']);
        RolePermission::query()->create(['role_id' => $bodRole->id, 'object' => 'teams', 'operation' => 'manage']);
        RolePermission::query()->create(['role_id' => $bodRole->id, 'object' => 'reports', 'operation' => 'manage']);
        RolePermission::query()->create(['role_id' => $bodRole->id, 'object' => 'roles', 'operation' => 'manage']);
        RolePermission::query()->create(['role_id' => $bodRole->id, 'object' => 'forms', 'operation' => 'manage']);
        RolePermission::query()->create(['role_id' => $keyFobAssignerRole->id, 'object' => 'keys', 'operation' => 'manage']);
        RolePermission::query()->create(['role_id' => $bookkeeperRole->id, 'object' => 'users', 'operation' => 'manage']);
    }

    private function seedUsersTable(): void
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

    private function seedUserSkillsTable(): void
    {
        $skills = [
            'Woodworking',
            '3D Printing',
            'Programming',
            'Welding',
            'Acting',
            'Electronics',
            'Painting',
            'Sewing',
            'Machining',
            'Dancing',
            'Slaying Monsters',
            'Sorcery',
            'Potion Mixing'
        ];

        User::all()->each(function (User $user) use ($skills) {
            if(random_int(0, 10) >= 2) {
                return;
            }

            $skillNumWeights = [1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 3];
            $numberOfSkills = $skillNumWeights[array_rand($skillNumWeights)];

            collect(Arr::only($skills, array_rand($skills, $numberOfSkills)))
                ->each(fn (string $skill) => UserSkill::query()->create(['user_id' => $user->id, 'skill' => $skill]));
        });
    }
}
