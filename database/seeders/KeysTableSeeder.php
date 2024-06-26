<?php

namespace Database\Seeders;

use App\Models\Key;
use App\Models\User;
use Illuminate\Database\Seeder;

class KeysTableSeeder extends Seeder
{
    public function run()
    {
        User::all()->each(function (User $user) {
            do {
                $key = \App\Models\Key::create([
                    'user_id' => $user->id,
                    'rfid' => md5(random_int(0, 9999999999)),
                    'description' => 'Added via seed',
                ]);
            } while (! $key); // retry if key fails insertion
        });
    }
}
