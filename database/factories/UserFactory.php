<?php

use App\Role;
use App\User;
use App\UserRole;
use App\UserStatus;
use Illuminate\Database\Eloquent\Factory;
use Faker\Generator;

/** @var Factory $factory */
$factory->define(App\User::class, function (Generator $faker) {
    static $password;

    $firstName = $faker->firstName;
    $lastName = $faker->lastName;

    return [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'first_preferred' => $firstName,
        'last_preferred' => $lastName,
        'email' => "$firstName.$lastName@example.com",
        'password' => $password ?: $password = bcrypt('secret'),
        'status' => '',
        'member_id' => User::query()->max('member_id') + 1,
        'acl' => '',
        'date_applied' => now()->subDays(14),
        'date_admitted' => now()->subDays(7),
        'date_hiatus_start' => null,
        'date_hiatus_end' => null,
        'date_withdrawn' => null,
        'phone' => $faker->e164PhoneNumber,
        'address' => $faker->streetAddress,
        'city' => 'Kitchener',
        'province' => 'ON',
        'postal' => $faker->regexify('/[A-Z]\d[A-Z]\d[A-Z]\d/'),
        'google_account' => '',
        'photo' => '',
        'notes' => $faker->paragraph,
        'remember_token' => $faker->lexify('???????????????'),
    ];
});

$factory
    ->state(
        User::class,
        'active',
        [
            'status' => 'active',
            'date_applied' => now()->subDays(7),
            'date_admitted' => null,
        ]
    )
    ->afterCreatingState(User::class, 'active', function (User $user) {
        factory(UserStatus::class)->create(['user_id' => $user->id, 'status' => 'active']);
    });

$factory
    ->state(
        User::class,
        'applied',
        [
            'status' => 'inactive',
            'date_applied' => now()->subDays(7),
            'date_admitted' => null,
        ]
    )
    ->afterCreatingState(User::class, 'applied', function (User $user) {
        factory(UserStatus::class)->create(['user_id' => $user->id, 'status' => 'inactive']);
    });

$factory
    ->state(
        User::class,
        'hiatus', [
            'status' => 'hiatus',
            'date_hiatus_start' => now()->subDays(7)
        ]
    )
    ->afterCreatingState(User::class, 'hiatus', function (User $user) {
        factory(UserStatus::class)->create(['user_id' => $user->id, 'status' => 'inactive']);
    });

$factory
    ->state(
        User::class,
        'admin',
        function (Generator $faker) {
            return [];
        }
    )
    ->afterCreatingState(
        User::class,
        'admin',
        function (User $user) {
            $adminRole = Role::updateOrCreate(['name' => 'Admin']);
            UserRole::create(['user_id' => $user->id, 'role_id' => $adminRole->id]);
        }
    );
