<?php

namespace Database\Seeders;

use App\Models\Gatekeeper;
use Illuminate\Database\Seeder;

class GatekeepersTableSeeder extends Seeder
{
    private array $tool_names = ['Band Saw', 'Laser Cutter', 'Table Saw', '3D Printer'];

    private array $door_names = ['Door 1', 'Door 2', 'Door 3', 'Door 4'];

    public function run()
    {
        foreach ($this->tool_names as $name) {
            $this->createGatekeeper(1, $name, 'lockout');
        }
        foreach ($this->door_names as $name) {
            $this->createGatekeeper(1, $name, 'doorway');
        }
    }

    private function createGatekeeper(int $count, string $name, string $type): void
    {
        GateKeeper::factory()->count($count)->create(['name' => $name, 'type' => $type]);
    }
}
