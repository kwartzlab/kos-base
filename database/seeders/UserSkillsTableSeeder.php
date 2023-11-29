<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSkill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class UserSkillsTableSeeder extends Seeder
{
    private array $skills = [
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

    public function run()
    {
        User::all()->each(function (User $user) {
            if (random_int(0, 10) >= 2) {
                return;
            }

            $skillNumWeights = [1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 3];
            $numberOfSkills = $skillNumWeights[array_rand($skillNumWeights)];

            collect(Arr::only($this->skills, array_rand($this->skills, $numberOfSkills)))
                ->each(fn(string $skill) => UserSkill::query()->create(['user_id' => $user->id, 'skill' => $skill]));
        });
    }
}
