<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserFlag;
use App\Models\UserStatus;
use Illuminate\Database\Seeder;

class UserFlagsTableSeeder extends Seeder
{
    public function run()
    {
        User::query()->where('status', UserStatus::STATUS_ACTIVE)
            ->get()
            ->each(fn (User $user) => UserFlag::query()->create([
                'user_id' => $user->id,
                'flag' => UserFlag::FLAG_COVID_VACCINE,
            ]));

        User::query()
            ->whereIn('status', [
                UserStatus::STATUS_INACTIVE,
                UserStatus::STATUS_TERMINATED,
                UserStatus::STATUS_INACTIVE_ABANDONED,
            ])
            ->get()
            ->each(fn (User $user) => UserFlag::query()->create([
                'user_id' => $user->id,
                'flag' => UserFlag::FLAG_KEYS_DISABLED,
            ]));
    }
}
