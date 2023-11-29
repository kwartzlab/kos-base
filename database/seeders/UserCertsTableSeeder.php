<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserCert;
use App\Models\UserStatus;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Database\Seeder;

class UserCertsTableSeeder extends Seeder
{
    private array $certs = [
        UserCert::CERT_FIRST_AID,
        UserCert::CERT_HEALTH_AND_SAFETY,
        UserCert::CERT_PROFESSIONAL,
        UserCert::CERT_OTHER,
    ];

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    public function run()
    {
        User::query()
            ->whereIn('status', [
                UserStatus::STATUS_ACTIVE,
                UserStatus::STATUS_HIATUS,
                UserStatus::STATUS_INACTIVE,
                UserStatus::STATUS_SUSPENDED,
                UserStatus::STATUS_TERMINATED,
            ])
            ->get()
            ->filter(fn() => random_int(1, 20) === 1)
            ->each(function (User $user) {
                UserCert::query()->create([
                    'user_id' => $user->id,
                    'type' => $this->certs[array_rand($this->certs)],
                    'name' => implode(' ', $this->faker->words(random_int(1, 3))),
                    'expiry_date' => random_int(1, 3) === 1
                        ? now()->addDays(random_int(-365, 1095))
                        : null,
                ]);
            });
    }
}
