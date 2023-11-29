<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    public function run()
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
}
