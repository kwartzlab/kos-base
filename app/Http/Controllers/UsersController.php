<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

class UsersController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($filter = 'active')
    {
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

        // See if we have an form designated as a new user application and redirect if needed
        $form = \App\Form::where('special_form','new_user_app')->first();
        if ($form != NULL) {
            return redirect('/forms/' . $form->id);
        } else {
            $page_title = 'Membership Application';
            return view('users.create', compact('page_title'));
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    
        /*

         $request->validate([
               'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|unique:users',
                'address' => 'required',
                'city' => 'required',
                'province' => 'required',
                'postal' => 'required',
                'photo' => 'image|required|mimes:jpeg,png,jpg|max:8000'
                ]);

        // process photo
        $photo = basename($request->file('photo')->store('public/photos'));

        // create the applicant user
        $user = \App\User::create([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'status' => 'applicant',
            'date_applied' => date("Y-m-d"),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'province' => $request->input('province'),
            'postal' => $request->input('postal'),
            'password' => Hash::make($this->generate_random_password()),
            'member_id' => rand(1000,9999),
            'photo' => $photo
            ]);

        // build application form array for database
        $form_data = array();
        $form_data[] = array(
            'label' => 'Interviewing Members (three are required)',
            'value' => $request->input('int_members')
        );
        $form_data[] = array(
            'label' => 'How did you hear about Kwartzlab? Were you referred by a member?',
            'value' => $request->input('int_q1')
        );
        $form_data[] = array(
            'label' => 'Have you visited Kwartzlab before? If so, any particular events or just Tuesday Open Nights in general?',
            'value' => $request->input('int_q2')
        );
        $form_data[] = array(
            'label' => 'Can you tell us a bit about yourself?',
            'value' => $request->input('int_q3')
        );
        $form_data[] = array(
            'label' => 'Have you been a member of an organization, club or association like Kwartzlab before?',
            'value' => $request->input('int_q4')
        );
        $form_data[] = array(
            'label' => 'What sort of projects are you looking to work on at Kwartzlab?',
            'value' => $request->input('int_q5')
        );
        $form_data[] = array(
            'label' => 'Can you tell us about a project youve worked on in the past?',
            'value' => $request->input('int_q6')
        );
        $form_data[] = array(
            'label' => 'Are you looking to develop any particular skills or tool experience as a Kwartzlab member?',
            'value' => $request->input('int_q7')
        );
        $form_data[] = array(
            'label' => 'Kwartzlab is a member-run organization. As such, we all share some duties, such as clean up and general equipment maintenance. Are you OK with volunteering some of your time (generally 1-2 hours a month) to help keep Kwartzlab up and running?',
            'value' => $request->input('int_q8')
        );
        $form_data[] = array(
            'label' => 'Do you have any special skills in this regard to help out? i.e tool/equipment experience, etc.',
            'value' => $request->input('int_q9')
        );
        $form_data[] = array(
            'label' => 'There is an annual general meeting you would be expected to attend, where we vote on anything that concerns our space as a whole, like changing rules or electing board members. Are you OK with attending such a meeting?',
            'value' => $request->input('int_q10')
        );
        $form_data[] = array(
            'label' => 'Kwartzlab members are responsible for their health and safety at all times - including observing all safety and training requirements for tools and other equipment. It is the responsibility of all Members and Guests to maintain and promote this culture in their own usage, as well as addressing inappropriate or unsafe use of tools by others. If you see another person operating tools in an unsafe or inappropriate manner, you are expected to address the issue. If you are not comfortable speaking with the person directly, you need to raise the issue immediately with the Team for that tool/area or the Board of Directors. Do you agree?',
            'value' => $request->input('int_q13')
        );
        $form_data[] = array(
            'label' => 'Have you read and agreed to the Kwartzlab Code of Conduct?',
            'value' => $request->input('int_q11')
        );
        $form_data[] = array(
            'label' => 'Is there anything else you would like us or the membership to know?',
            'value' => $request->input('int_q12')
        );
        $form_data[] = array(
            'label' => 'First Name',
            'value' => $request->input('first_name')
        );
        $form_data[] = array(
            'label' => 'Last Name',
            'value' => $request->input('last_name')
        );
        $form_data[] = array(
            'label' => 'Email Address',
            'value' => $request->input('email')
        );
        $form_data[] = array(
            'label' => 'Phone Number',
            'value' => $request->input('phone')
        );
        $form_data[] = array(
            'label' => 'Street Address',
            'value' => $request->input('address')
        );
        $form_data[] = array(
            'label' => 'City',
            'value' => $request->input('city')
        );
        $form_data[] = array(
            'label' => 'Province',
            'value' => $request->input('province')
        );
        $form_data[] = array(
            'label' => 'Postal Code',
            'value' => $request->input('postal')
        );

        // save application form data

        $application_form = \App\FormSubmissions::create([
            'form_id' => '1',
            'form_name' => 'Membership Application',
            'submitted_by' => \Auth::user()->id,
            'user_id' => $user->id,
            'data' => json_encode($form_data)
        ]);

        // build array for email use
        $email_data = array(
            'name' => $user->first_name . ' ' . $user->last_name,
            'photo' =>  str_replace( 'https://', 'http://', env('APP_URL')) . '/storage/photos/' .  $user->photo,
            'form_data' => $form_data
        );

        // send email to admins (full contact info)

        $email_data['mail_attributes'] = array(
            'to' => config('kwartzlabos.membership_app.admin.to'),
            'cc' => config('kwartzlabos.membership_app.admin.cc'),
            'replyto' => config('kwartzlabos.membership_app.admin.replyto'),
            'subject' => 'New Member App [BoD Version] - ' . $user->first_name . ' ' . $user->last_name
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
        */
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

        $user = \App\User::find($id);

        return view('users.edit', compact('user'));

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
        
        if (Gate::allows('manage-users')) {

            $request->validate([
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
                $user->password = Hash::make($request->input('password'));
            }

            $user->save();


            $message = "User updated successfully.";
            if ($request->input('password') != NULL) {
                $message .= " Password changed.";      }

            return redirect('/users/' . $user->id . '/edit' )->with('success', $message);
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

        if (Gate::allows('manage-users')) {

            $this->validate(request(),[
                'confirm' => 'required'
            ]);


            if ($id == \Auth::user()->id) {
                $message = "Cannot delete yourself.";
                return redirect('/users')->with('error', $message);
            } else {

                $user = \App\User::find($id);

                if ($user->status == "applicant") {

                    $user->delete();
                    $message = "User deleted.";
                    return redirect('/users')->with('success', $message);

                } else {

                    $message = "Only applicants can be deleted.";
                    return redirect('/users/' . $user->id . '/edit' )->with('error', $message);
                    
                }
            }
        }

    }

    // get key add form
    public function create_key($user_id) {

        $user = \App\User::find($user_id);
        if (count($user) != 0) {
            $page_title = "Adding key for " . $user->name;

            return view('keys.create', compact('page_title','user'));
        }


    }

    // post new key
    public function store_key($user_id) {
        
         $request->validate([
            'rfid' => 'required'
            ]);

        $user = \App\Key::create([
            'user_id' => $user_id,
            'rfid' => md5($request->input('rfid')),
            'description' => $request->input('description'),
            ]);

        $message = "Key added successfully.";
        return redirect('/users/' . $user_id . '/edit')->with('success', $message);

    }   

    // delete single key
    public function destroy_key($user_id, $key_id) {

        $key = \App\Key::find($key_id);
        $key->delete();

        $message = "Key deleted successfully.";
        return redirect('/users/' . $user_id . '/edit')->with('success', $message);

    }


}
