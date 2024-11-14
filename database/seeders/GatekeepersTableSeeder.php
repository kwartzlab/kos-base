<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Gatekeeper;
use Illuminate\Database\Seeder;

class GatekeepersTableSeeder extends Seeder
{
    public function run()
    {
        $this->createGatekeeper(1);
    }

    private function createGatekeeper(int $count = 1): void
    {
        GateKeeper::factory()->count($count)->create();
    }
}