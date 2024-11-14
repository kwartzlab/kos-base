<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Gatekeeper;
use Illuminate\Database\Seeder;

class GatekeepersTableSeeder extends Seeder
{

    private array $names = ["bandsaw", "laser cutter"];
    public function run()
    {
     foreach ($this->names as $name) {
        $this->createGatekeeper(1, $name);
     } 
    }

    private function createGatekeeper(int $count = 1, string $name): void
    {
        GateKeeper::factory()->count($count)->create(["name" => $name]);//create(["name" => "bandsaw"])
    }
}