<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class SendActiveMembersEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:recentmembersemail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates an emailed report of active members with 3 or more key events in the past month';

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
        $this->info('Generating list of active members...');

        // get all active users
        $users = \App\Models\User::where('status', 'active')->orderby('first_preferred')->orderby('last_preferred')->get();

        // find authentications from the past month, if more than 3 add them to the list
        $email_data['member_list'] = \App\Models\User::where('status', 'active')
            ->whereHas('authentications', function ($query) {
                $query->whereRelation('gatekeeper', 'type', 'doorway')
                    ->where('lock_in', '>=', Carbon::now()->subMonth()->toDateTimeString());
            }, '>', 3)
            ->orderby('first_preferred')
            ->orderby('last_preferred')
            ->get()
            ->map(function ($member) {
                return $member->get_name();
            });

        $date = \Carbon\Carbon::now();
        $email_data['month_reported'] = $date->subMonth()->format('F Y');

        // send email
        \Mail::send(new \App\Mail\RecentMembers($email_data));

        $this->info('Email sent.');
    }
}
