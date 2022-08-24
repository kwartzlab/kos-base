<?php

use App\User;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;

/** @var Factory $factory */
$factory->define(App\UserStatus::class, function (Generator $faker, array $attributes) {
    return [
        'user_id' => array_key_exists('user_id', $attributes)
            ? $attributes['user_id']
            : factory(User::class)->create()->id,
        'status' => 'active',
        'note' => $faker->paragraph,
        'updated_by' => array_key_exists('updated_by', $attributes)
            ? $attributes['updated_by']
            : factory(User::class)->create()->id,
    ];
});
