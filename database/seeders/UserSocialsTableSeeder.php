<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSocial;
use Faker\Factory as Faker;
use Faker\Generator;
use Illuminate\Database\Seeder;

class UserSocialsTableSeeder extends Seeder
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    public function run()
    {
        User::all()->each(function (User $user) {
            if(random_int(0, 10) >= 2) {
                return;
            }

            $socialsToCreate = [
                ['instagram'],
                ['twitter'],
                ['facebook'],
                ['instagram', 'twitter'],
                ['twitter', 'facebook'],
                ['instagram', 'twitter', 'facebook']
            ];

            collect(collect($socialsToCreate)->random())
                ->each(function (string $service) use ($user) {
                    UserSocial::query()->create([
                        'user_id' => $user->id,
                        'service' => $service,
                        'profile' => $this->createLinkForService($service),
                    ]);
                });
        });
    }

    private function createLinkForService(string $service): string
    {
        switch ($service) {
            case 'twitter':
                return '@' . $this->faker->word;
            case 'facebook':
                return 'https://facebook.com/' . $this->faker->word;
            case 'instagram':
                return $this->faker->word;
            default:
                return '';
        }
    }
}
