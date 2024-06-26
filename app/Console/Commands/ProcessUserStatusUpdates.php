<?php

namespace App\Console\Commands;

use App\Traits\UserStatusTrait;
use Illuminate\Console\Command;

class ProcessUserStatusUpdates extends Command
{
    use UserStatusTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:userstatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes status updates for all users';

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
        $this->info('['.date('Y-m-d G:i:s').'] Processing user status...');

        // grab all users and start processing statuses
        $users = \App\Models\User::all();

        $bar = $this->output->createProgressBar(count($users));

        $changes = [];
        foreach ($users as $user) {
            $bar->advance();
            $result = $this->check_current_userstatus($user);
            if ($result != null) {
                $changes[] = $result;
            }
        }
        $bar->finish();
        $this->info(null);
        $this->info(null);
        if (count($changes) > 0) {
            $this->info('['.date('Y-m-d G:i:s').'] Processing complete - changes made:');
            foreach ($changes as $change) {
                $this->info($change);
            }
        } else {
            $this->info('['.date('Y-m-d G:i:s').'] Processing complete - no changes made');
        }
    }
}
