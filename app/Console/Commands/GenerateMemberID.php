<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMemberID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:memberids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a unique membership ID for all users based on date_admitted (sorted alphabetically)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Generating membership IDs');

        // get all users
        $users = \App\User::where('date_admitted','!=',NULL)->orderby('date_admitted')->orderby('first_preferred')->get();
        
        $member_id = 1;
        foreach ($users as $user) {
            $this->info('Processing ' . $user->get_name());
            $user->member_id = $member_id;
            $user->save();
            $member_id++;
        }

    }
}
