<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateUserStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:userstatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates User Status table from previous user dates';

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
        $this->info('Generating user status...');

        // ensure status table is empty, otherwise abort

        if (\DB::table('user_statuses')->count() != 0) {
            $this->error('User status table is not empty, aborting');

            return false;
        }

        // get all users
        $users = \App\User::all();

        foreach ($users as $user) {
            $this->info('Processing '.$user->get_name());

            if ($user->date_applied != null) {
                $rec = new \App\UserStatus([
                    'user_id' => $user->id,
                    'status' => 'applicant',
                    'updated_by' => 0,
                    'created_at' => $user->date_applied,
                    'updated_at' => Carbon::now(),
                ]);

                $rec->save(['timestamps' => false]);
                unset($rec);
            }

            if ($user->date_admitted != null) {
                $rec = new \App\UserStatus([
                    'user_id' => $user->id,
                    'status' => 'active',
                    'updated_by' => 0,
                    'created_at' => $user->date_admitted,
                    'updated_at' => Carbon::now(),
                ]);

                $rec->save(['timestamps' => false]);
                unset($rec);
            } else {
                if ($user->status != 'applicant') {
                    $this->error('No date admitted for '.$user->get_name());
                }
            }

            if ($user->date_withdrawn != null) {
                $rec = new \App\UserStatus([
                    'user_id' => $user->id,
                    'status' => 'inactive',
                    'updated_by' => 0,
                    'created_at' => $user->date_withdrawn,
                    'updated_at' => Carbon::now(),
                ]);

                $rec->save(['timestamps' => false]);
                unset($rec);
            }

            if (($user->date_hiatus_start != null) && ($user->date_hiatus_end != null)) {
                $rec = new \App\UserStatus([
                    'user_id' => $user->id,
                    'status' => 'hiatus',
                    'updated_by' => 0,
                    'created_at' => $user->date_hiatus_start,
                    'updated_at' => Carbon::now(),
                ]);

                $rec->save(['timestamps' => false]);

                $rec = new \App\UserStatus([
                    'user_id' => $user->id,
                    'status' => 'active',
                    'updated_by' => 0,
                    'created_at' => $user->date_hiatus_end,
                    'updated_at' => Carbon::now(),
                    'note' => 'Ending Hiatus',
                ]);

                $rec->save(['timestamps' => false]);

                unset($rec);
            }
        }
    }
}
