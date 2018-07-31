<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:members {importfile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs a members of active members with the database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function generate_random_password() {

         $characters = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=';

         $string = '';
         $max = strlen($characters) - 1;
         for ($i = 0; $i < 32; $i++) {
              $string .= $characters[mt_rand(0, 16)];
         }

         return $string;    
    }


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 
        $importfile = $this->argument('importfile');

        $this->info('Importing from ' . $importfile);

        if (file_exists($importfile)) {

            # open members file
            $members = array_map('str_getcsv', file($importfile));
            array_walk($members, function(&$a) use ($members) {
                $a = array_combine($members[0], $a);
            });
            array_shift($members); # remove column header

            // deactivate all members in the database
            \App\User::chunk(300, function($users)
            {
                foreach ($users as $user)
                {
                    $user->status = 'inactive';
                    $user->save();
                }
            });

            $membercount = count($members) - 1;
            $bar = $this->output->createProgressBar($membercount);

            // grab default authorizations now so we can assign them later
            $default_authorizations = \App\Gatekeeper::where('is_default', 1)->get();

            // use membership ID to match user records and reactivate
            foreach ($members as $member) {
                $bar->advance();
                $user = \App\User::where('member_id',$member['Membership Number'])->get()->first();

                if (count($user)>0) {
                    // update existing user

                    // check if user is active or not
                    if (substr_count(strtolower($member['Status']),'active')>0) {
                        $user->status = 'active';
                    } else if (substr_count(strtolower($member['Status']),'hiatus')>0) {                    
                        $user->status = 'hiatus';
                    } else {
                        $user->status = 'inactive';
                    }

                    $user->first_name = $member['Personal Name'];
                    $user->last_name = $member['Family Name'];
                    $user->email = $member['E-Mail'];
                    
                    if (strlen($member['Applied']) > 1) { $user->date_applied = date('Y-m-d', strtotime($member['Applied'])); }
                    if (strlen($member['Admitted']) > 1) { $user->date_admitted = date('Y-m-d', strtotime($member['Admitted'])); }
                    if (strlen($member['Hiatus Starts']) > 1) { $user->date_hiatus_start = date('Y-m-d', strtotime($member['Hiatus Starts'])); }
                    if (strlen($member['Hiatus Term']) > 1) { $user->date_hiatus_end = date('Y-m-d', strtotime($member['Hiatus Term'])); }
                    if (strlen($member['Withdrawn']) > 1) { $user->date_withdrawn = date('Y-m-d', strtotime($member['Withdrawn'])); }
                    
                    $user->phone = $member['Phone'];
                    $user->address = $member['Street Address'];
                    $user->city = $member['City'];
                    $user->province = $member['Province'];
                    $user->postal = $member['Postal Code'];

                    $user->save();
                } else {
                    // add new user

                    // check if user is active or not
                    if (substr_count(strtolower($member['Status']),'active')>0) {
                        $status = 'active';
                    } else if (substr_count(strtolower($member['Status']),'hiatus')>0) {                    
                        $status = 'hiatus';
                    } else {
                        $status = 'inactive';
                    }

                    // build array

                    $user_record = array();

                    if (strlen($member['Applied']) > 1) { $user_record['date_applied'] = date('Y-m-d', strtotime($member['Applied'])); } else { $user_record['date_applied'] = NULL; }
                    if (strlen($member['Admitted']) > 1) { $user_record['date_admitted'] = date('Y-m-d', strtotime($member['Admitted'])); } else { $user_record['date_admitted'] = NULL; }
                    if (strlen($member['Hiatus Starts']) > 1) { $user_record['date_hiatus_start'] = date('Y-m-d', strtotime($member['Hiatus Starts'])); } else { $user_record['date_hiatus_start'] = NULL; }
                    if (strlen($member['Hiatus Term']) > 1) { $user_record['date_hiatus_end'] = date('Y-m-d', strtotime($member['Hiatus Term'])); } else { $user_record['date_hiatus_end'] = NULL; }
                    if (strlen($member['Withdrawn']) > 1) { $user_record['date_withdrawn'] = date('Y-m-d', strtotime($member['Withdrawn'])); } else { $user_record['date_withdrawn'] = NULL; }

                    $user = \App\User::create([
                        'first_name' => $member['Personal Name'],
                        'last_name' => $member['Family Name'],
                        'email' => $member['E-Mail'],
                        'status' => $status,
                        'acl' => 'user',
                        'member_id' => $member['Membership Number'],
                        'password' => Hash::make($this->generate_random_password()),
                        'phone' => $member['Phone'],
                        'address' => $member['Street Address'],
                        'city' => $member['City'],
                        'province' => $member['Province'],
                        'postal' => $member['Postal Code'],
                        'date_applied' => $user_record['date_applied'],
                        'date_admitted' => $user_record['date_admitted'],
                        'date_hiatus_start' => $user_record['date_hiatus_start'],
                        'date_hiatus_end' => $user_record['date_hiatus_end'],
                        'date_withdrawn' => $user_record['date_withdrawn']
                    ]);

                    unset($user_record);

                    // add default authorizations
                    foreach ($default_authorizations as $authorization) {
                        $user->add_authorization($authorization->id);
                    }


                }

            }
            
            $bar->finish();
            $this->info("\nImport complete.");


//            print_r($members);

        } else {
            $this->error('File not found: ' . $importfile);
        }



    }
}
 