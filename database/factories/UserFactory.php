<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    private static int $memberIdIncrement = 1;

    public function definition(): array
    {
        static $password;

        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();

        self::$memberIdIncrement++;

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'first_preferred' => null,
            'last_preferred' => null,
            'email' => "$firstName.$lastName@example.com",
            'password' => $password ?: $password = bcrypt('secret'),
            'status' => 'active',
            'member_id' => self::$memberIdIncrement,
            'acl' => '',
            'date_applied' => now()->subDays(14),
            'date_admitted' => now()->subDays(7),
            'date_hiatus_start' => null,
            'date_hiatus_end' => null,
            'date_withdrawn' => null,
            'phone' => $this->faker->e164PhoneNumber(),
            'address' => $this->faker->streetAddress(),
            'city' => 'Kitchener',
            'province' => 'ON',
            'postal' => $this->faker->regexify('/[A-Z]\d[A-Z]\d[A-Z]\d/'),
            'google_account' => '',
            'photo' => '',
            'notes' => $this->faker->paragraph(),
            'remember_token' => $this->faker->lexify('???????????????'),
        ];
    }

    public function status(string $status)
    {
        if(method_exists($this, $status)) {
            $factory = $this->{$status}();
        }

        $factory = $factory ?? $this;
        return $factory->state(['status' => $status]);
    }

    public function active()
    {
        return $this->state(['status' => 'active', 'date_applied' => now()->subDays(7), 'date_admitted' => now()->subDays(3)]);
    }

    public function applied()
    {
        return $this->state(['status' => 'inactive', 'date_applied' => now()->subDays(7), 'date_admitted' => null]);
    }

    public function hiatus()
    {
        return $this->state(['status' => 'hiatus', 'date_hiatus_start' => now()->subDays(7)]);
    }
}
