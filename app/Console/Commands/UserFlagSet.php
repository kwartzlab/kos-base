<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UserFlagSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'userflag:set {--email= : Set flag for specific user by email address (if omitted, flag will apply to all users)} {flag}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets a user flag for one or all users';

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

        // ensure user flag is defined in kos config
        if (array_key_exists($this->argument('flag'), config('kwartzlabos.user_flags'))) {
            if ($this->option('email')) {                   // specific user
                // lookup email address
                $user = \App\Models\User::where('email', $this->option('email'))->first();
                if ($user != null) {

                    // skip if flag already exists
                    if ($user->flags->contains('flag', $this->argument('flag'))) {
                        $this->info('Setting flag *'.$this->argument('flag').'* for '.$user->get_name().' - already exists, skipping');
                    } else {
                        $this->info('Setting flag *'.$this->argument('flag').'* for '.$user->get_name());
                        $flag = new \App\Models\UserFlag(['flag' => $this->argument('flag')]);
                        $user->flags()->save($flag);
                    }
                } else {
                    $this->error('No user found with email address '.$this->option('email'));
                }
            } else {                                        // all users

                if ($this->confirm('WARNING: You are about to set the user flag *'.$this->argument('flag').'* for ALL kOS users and will take effect immediately. Do you wish to continue?')) {
                    $this->info('Setting flag *'.$this->argument('flag').'* for all users...');

                    // get all users
                    $users = \App\Models\User::orderby('first_name')->orderby('last_name')->get();
                    foreach ($users as $user) {
                        // skip if flag already exists
                        if ($user->flags->contains('flag', $this->argument('flag'))) {
                            $this->info('Setting flag *'.$this->argument('flag').'* for '.$user->get_name().' - already exists, skipping');
                        } else {
                            $this->info('Setting flag *'.$this->argument('flag').'* for '.$user->get_name());
                            $flag = new \App\Models\UserFlag(['flag' => $this->argument('flag')]);
                            $user->flags()->save($flag);
                        }
                    }
                }
            }
        } else {
            $this->error('Unknown user flag: '.$this->argument('flag'));
        }
    }
}
