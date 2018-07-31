<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class UsersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($filter = 'active')
    {
        if ((\Auth::user()->acl == 'admin') || (\Auth::user()->acl == 'keyadmin')) {

            switch ($filter) {
                case 'applicant': $users = \App\User::where('status','applicant')->orderby('first_name')->get();$title_filter = 'Applicants';break;
                case 'inactive': $users = \App\User::where('status','inactive')->orderby('first_name')->get();$title_filter = 'Withdrawn';break;
                case 'active': $users = \App\User::where('status','active')->orderby('first_name')->get();$title_filter = 'Active';break;
                case 'hiatus': $users = \App\User::where('status','hiatus')->orderby('first_name')->get();$title_filter = 'On Hiatus';break;
                default: $users = \App\User::orderby('first_name')->get();$title_filter = 'All';break;
            }

            $page_title = 'Membership Register - ' . $title_filter . ' (' . count($users) . ')';
            return view('users.index', compact('page_title','users'));

        }
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $page_title = 'Membership Application';
        return view('users.create', compact('page_title'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    
        $this->validate(request(),[
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|unique:users',
                'phone' => 'required',
                'address' => 'required',
                'city' => 'required',
                'province' => 'required',
                'postal' => 'required',
                'photo' => 'image|required|max:8000'
                ]);

        // process photo
        $photo = basename($request->file('photo')->store('public/photos'));

        // create the applicant user
        $user = \App\User::create([
            'first_name' => request('first_name'),
            'last_name' => request('last_name'),
            'email' => request('email'),
            'status' => 'applicant',
            'acl' => 'user',
            'date_applied' => date("Y-m-d"),
            'phone' => request('phone'),
            'address' => request('address'),
            'city' => request('city'),
            'province' => request('province'),
            'postal' => request('postal'),
            'password' => Hash::make($this->generate_random_password()),
            'member_id' => rand(1000,9999),
            'photo' => $photo
            ]);

        // build application form array for database
        $form_data = array();
        $form_data[] = array(
            'label' => 'Interviewing Members (three are required)',
            'value' => request('int_members')
        );
        $form_data[] = array(
            'label' => 'How did you hear about Kwartzlab? Were you referred by a member?',
            'value' => request('int_q1')
        );
        $form_data[] = array(
            'label' => 'Have you visited Kwartzlab before? If so, any particular events or just Tuesday Open Nights in general?',
            'value' => request('int_q2')
        );
        $form_data[] = array(
            'label' => 'Can you tell us a bit about yourself?',
            'value' => request('int_q3')
        );
        $form_data[] = array(
            'label' => 'Have you been a member of a similar organization before?',
            'value' => request('int_q4')
        );
        $form_data[] = array(
            'label' => 'What sort of projects are you looking to work on at Kwartzlab?',
            'value' => request('int_q5')
        );
        $form_data[] = array(
            'label' => 'Can you tell us about a project youve worked on in the past?',
            'value' => request('int_q6')
        );
        $form_data[] = array(
            'label' => 'Are you looking to develop any particular skills or tool experience as a Kwartzlab member?',
            'value' => request('int_q7')
        );
        $form_data[] = array(
            'label' => 'Kwartzlab is a member-run organization. As such, we all share some duties, such as clean up and general equipment maintenance. Are you OK with volunteering some of your time (generally 1-2 hours a month) to help keep Kwartzlab up and running?',
            'value' => request('int_q8')
        );
        $form_data[] = array(
            'label' => 'Do you have any special skills in this regard to help out? i.e tool/equipment experience, etc.',
            'value' => request('int_q9')
        );
        $form_data[] = array(
            'label' => 'There is an annual general meeting you would be expected to attend, where we vote on anything that concerns our space as a whole, like changing rules or electing board members. Are you OK with attending such a meeting?',
            'value' => request('int_q10')
        );
        $form_data[] = array(
            'label' => 'Have you read and agreed to the Kwartzlab Code of Conduct?',
            'value' => request('int_q11')
        );
        $form_data[] = array(
            'label' => 'Is there anything else you would like us or the membership to know?',
            'value' => request('int_q12')
        );
        $form_data[] = array(
            'label' => 'First Name',
            'value' => request('first_name')
        );
        $form_data[] = array(
            'label' => 'Last Name',
            'value' => request('last_name')
        );
        $form_data[] = array(
            'label' => 'Email Address',
            'value' => request('email')
        );
        $form_data[] = array(
            'label' => 'Phone Number',
            'value' => request('phone')
        );
        $form_data[] = array(
            'label' => 'Street Address',
            'value' => request('address')
        );
        $form_data[] = array(
            'label' => 'City',
            'value' => request('city')
        );
        $form_data[] = array(
            'label' => 'Province',
            'value' => request('province')
        );
        $form_data[] = array(
            'label' => 'Postal Code',
            'value' => request('postal')
        );

        // save application form data

        $application_form = \App\Form_submissions::create([
            'form_id' => '1',
            'form_name' => 'Membership Application',
            'submitted_by' => \Auth::user()->id,
            'user_id' => $user->id,
            'data' => json_encode($form_data)
        ]);

        // build array for email use
        $email_data = array(
            'name' => $user->first_name . ' ' . $user->last_name,
            'photo' =>  env('APP_URL') . '/storage/photos/' .  $user->photo,
            'form_data' => $form_data
        );

        // send email to admins (full contact info)

        $email_data['mail_attributes'] = array(
            'to' => config('kwartzlabos.membership_app.admin.to'),
            'cc' => config('kwartzlabos.membership_app.admin.cc'),
            'replyto' => config('kwartzlabos.membership_app.admin.replyto'),
            'subject' => 'New Member Application - ' . $user->first_name . ' ' . $user->last_name
        );

        \Mail::send(new \App\Mail\MemberApp($email_data,'admin'));            

        // send email to members (limited contact info)

        $email_data['mail_attributes'] = array(
            'to' => config('kwartzlabos.membership_app.members.to'),
            'cc' => config('kwartzlabos.membership_app.members.cc'),
            'replyto' => config('kwartzlabos.membership_app.members.replyto'),
            'subject' => 'New Member Application - ' . $user->first_name . ' ' . $user->last_name
        );

        \Mail::send(new \App\Mail\MemberApp($email_data,'members'));

        $message = "Application created & sent successfully.";
        return redirect('/users/create')->withMessage($message);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        if ((\Auth::user()->acl == 'admin') || (\Auth::user()->acl == 'keyadmin')) {

            $user = \App\User::find($id);

            $page_title = "Membership Register";
            return view('users.edit', compact('page_title','user'));

        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        if ((\Auth::user()->acl == 'admin') || (\Auth::user()->acl == 'keyadmin')) {

            $this->validate(request(),[
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required',
                'password' => 'confirmed',
                'photo' => 'max:8000'
            ]);
          
            $user = \App\User::find($id);

            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->email = $request->input('email');
            $user->status = $request->input('status');
            $user->acl = $request->input('acl');

            $user->phone = $request->input('phone');
            $user->address = $request->input('address');
            $user->city = $request->input('city');
            $user->province = $request->input('province');
            $user->postal = $request->input('postal');
            $user->notes = $request->input('notes');

            $user->date_applied = $request->input('date_applied');
            $user->date_admitted = $request->input('date_admitted');
            $user->date_hiatus_start = $request->input('date_hiatus_start');
            $user->date_hiatus_end = $request->input('date_hiatus_end');
            $user->date_withdrawn = $request->input('date_withdrawn');

            // if new photo was uploaded, process it and delete the old one
            if ($request->file('photo')) {
                $photo = basename($request->file('photo')->store('public/photos'));
                if ($photo != NULL) {
                    // delete old photo
                    if ($user->photo != NULL) {
                        \Storage::delete('public/photos/' . $user->photo);
                    }
                    // assign new photo
                    $user->photo = $photo;
                }
            }

            // if password was submitted, change it
            if ($request->input('password') != NULL) {
                $user->password = Hash::make(request('password'));
            }

            $user->save();
            $message = "User updated successfully.";
            if ($request->input('password') != NULL) {
                $message .= "<br />Password changed.";      }

            return redirect('/users/' . $user->id . '/edit' )->withMessage($message);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        if ((\Auth::user()->acl == 'admin') || (\Auth::user()->acl == 'keyadmin')) {

            
            $this->validate(request(),[
                'confirm' => 'required'
            ]);


            if ($id == \Auth::user()->id) {

                $message = "Cannot delete yourself.";
                return redirect('/users')->withErrors($message);
            } else {

                $user->clear_authorizations();

                $user = \App\User::find($id);
                $user->delete();

                $message = "User deleted.";
                return redirect('/users')->withMessage($message);
            }
        }

    }

    // get key add form
    public function create_key($user_id) {

        if ((\Auth::user()->acl == 'admin') || (\Auth::user()->acl == 'keyadmin')) {


            $user = \App\User::find($user_id);
            if (count($user) != 0) {
                $page_title = "Adding key for " . $user->name;

                return view('keys.create', compact('page_title','user'));
            }

        }

    }

    // post new key
    public function store_key($user_id) {
        
        if ((\Auth::user()->acl == 'admin') || (\Auth::user()->acl == 'keyadmin')) {
      
            $this->validate(request(),[
                    'rfid' => 'required'
                ]);

            $user = \App\Key::create([
                'user_id' => $user_id,
                'rfid' => md5(request('rfid')),
                'description' => request('description'),
                ]);

            $message = "Key added successfully.";
            return redirect('/users/' . $user_id . '/edit')->withMessage($message);

        }

    }   

    // delete single key
    public function destroy_key($user_id, $key_id) {

        if ((\Auth::user()->acl == 'admin') || (\Auth::user()->acl == 'keyadmin')) {

            $key = \App\Key::find($key_id);
            $key->delete();

            $message = "Key deleted successfully.";
            return redirect('/users/' . $user_id . '/edit')->withMessage($message);

        }

    }


}
