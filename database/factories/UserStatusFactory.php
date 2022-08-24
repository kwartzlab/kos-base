<?php

namespace Database\Factories;

use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => array_key_exists('user_id', $attributes)
                ? $attributes['user_id']
                : User::factory()->create()->id,
            'status' => 'active',
            'note' => $this->faker->paragraph,
            'updated_by' => array_key_exists('updated_by', $attributes)
                ? $attributes['updated_by']
                : User::factory()->create()->id,
        ];
    }
}
