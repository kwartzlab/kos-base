<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

//It might be worthwhile to randomize the 'status' and 'last seen' attributes

class GatekeeperFactory extends Factory
{

    public function definition(): array
    {

        return[
            "name" => "name",
            "status" => "enabled",
            "type" => "lockout",
            "is_default" => 0,
            "ip_address" => NULL,
            "auth_key" => $this->generate_auth_key(),
            "shared_auth" => 0,
            "auth_expires" => 0,
            "auth_expiry_type" => "revoke",
            "team_id" => 0,
            "training_desc" => NULL,
            "training_eta" => NULL,
            "training_prereq" => 0,
            "photo" => NULL,
            "last_seen" => NULL,
            "created_at" => now(),
            "updated_at" => now(),
            "wiki_page" => NULL
        ];
    }

    // generates a pseudo-random authentication key for gatekeeper devices
    // copied from GatekeepersController.php
    public function generate_auth_key()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $string = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < 32; $i++) {
            $string .= $characters[mt_rand(0, $max)];
        }

        return $string;
    }
}