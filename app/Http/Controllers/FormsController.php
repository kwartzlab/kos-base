<?php

namespace App\Http\Controllers;

use App\Mail\MemberAppInterviewConfirmation;
use App\Services\Slack\KosBot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FormsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Gate::allows('manage-forms')) {
            $forms = \App\Models\Form::orderby('name')->get();

            return view('forms.index', compact('forms'));
        } else {
            $message = 'You do not have access to that resource.';

            return redirect('/')->with('error', $message);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Gate::allows('manage-forms')) {

            // set up special forms default
            if (old('special_form')) {
                $selected_assignment = old('selected_assignment');
            } else {
                $selected_assignment = '0';
            }

            return view('forms.create', compact('selected_assignment'));
        } else {
            $message = 'You do not have access to that resource.';

            return redirect('/')->with('error', $message);
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
        if (\Gate::allows('manage-forms')) {
            // build our field array and convert to json
            if ($request->input('element')) {
                $field_array = [];
                foreach ($request->input('element') as $field_uuid => $field) {
                    if (array_key_exists('required', $field)) {
                        $field_required = 1;
                    } else {
                        $field_required = 0;
                    }

                    $options_array = [];
                    if (array_key_exists('options', $field)) {
                        foreach ($field['options'] as $option_uuid => $option) {
                            // only add option if name is filled in
                            if ($option['name'] != null) {
                                $options_array[$option_uuid] = [
                                    'name' => $option['name'],
                                    'value' => $option['value'],
                                ];
                            }
                        }
                    }

                    switch ($field['type']) {
                        case 'input':
                            $field_array[$field_uuid] = [
                                'type' => $field['type'],
                                'label' => $field['label'],
                                'name' => $field['name'],
                                'length' => $field['length'],
                                'mask' => $field['mask'],
                                'required' => $field_required,
                            ];
                            break;
                        case 'textarea':
                            if (array_key_exists('usehtml', $field)) {
                                $field_usehtml = 1;
                            } else {
                                $field_usehtml = 0;
                            }
                            $field_array[$field_uuid] = [
                                'type' => $field['type'],
                                'label' => $field['label'],
                                'name' => $field['name'],
                                'length' => $field['length'],
                                'usehtml' => $field_usehtml,
                                'required' => $field_required,
                            ];
                            break;
                        case 'switch':
                            $field_array[$field_uuid] = [
                                'type' => $field['type'],
                                'label' => $field['label'],
                                'name' => $field['name'],
                                'on' => $field['on'],
                                'off' => $field['off'],
                                'required' => $field_required,
                            ];
                            break;
                        case 'dropdown':
                            $field_array[$field_uuid] = [
                                'type' => $field['type'],
                                'label' => $field['label'],
                                'name' => $field['name'],
                                'options' => $options_array,
                                'required' => $field_required,
                            ];
                            break;
                        case 'radio':
                            $field_array[$field_uuid] = [
                                'type' => $field['type'],
                                'label' => $field['label'],
                                'name' => $field['name'],
                                'options' => $options_array,
                                'required' => $field_required,
                            ];
                            break;
                        case 'checkbox':
                            $field_array[$field_uuid] = [
                                'type' => $field['type'],
                                'label' => $field['label'],
                                'name' => $field['name'],
                                'options' => $options_array,
                                'required' => $field_required,
                            ];
                            break;
                        case 'upload':
                            if (array_key_exists('multiupload', $field)) {
                                $field_multiupload = 1;
                            } else {
                                $field_multiupload = 0;
                            }
                            $field_array[$field_uuid] = [
                                'type' => $field['type'],
                                'label' => $field['label'],
                                'name' => $field['name'],
                                'multiupload' => $field_multiupload,
                                'required' => $field_required,
                            ];
                            break;
                    }
                }
            }

            $form = \App\Models\Form::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'status' => $request->input('status'),
                'special_form' => $request->input('special_form'),
                'fields' => json_encode($field_array),
            ]);

            $message = 'Form created successfully.';

            return redirect('/forms/'.$form->id.'/edit')->with('success', $message);
        } else {
            $message = 'You do not have access to that resource.';

            return redirect('/')->with('error', $message);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $form = \App\Models\Form::find($id);
        \Session::flash('skip_individual_errors', 1);

        if ($form != null) {
            $form_fields = json_decode($form->fields);

            // determine any form conditions
            if ($form->conditions != null) {
                $form_conditions = json_decode($form->conditions);
                $message = null;

                // loop through conditions
                foreach ($form_conditions as $key => $form_condition) {
                    switch ($form_condition->condition) {
                        // has form opened yet?
                        case 'form_opens':
                            $form_opens = new Carbon($form_condition->value);
                            if ($form_opens->gt(Carbon::now())) {
                                $message .= 'The '.$form->name.' form does not open until '.$form_opens->format('Y-m-d').' at '.$form_opens->format('g:ia T');

                                return redirect('/')->with('error', $message);
                            }
                            break;
                            // user must have a speciifc status to continue
                        case 'user_status':
                            if (\Auth::user()->status != $form_condition->value) {
                                $message .= ' '.$form_condition->text.' ['.$form->name.']';

                                return redirect('/')->with('error', $message);
                            }
                            break;
                    }
                }
            }

            return view('forms.show', compact('form', 'form_fields'));
        }
    }

    private function generate_random_password()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_+-=';

        $string = '';
        $max = strlen($characters) - 1;
        for ($i = 0; $i < 12; $i++) {
            $string .= $characters[mt_rand(0, 16)];
        }

        return $string;
    }

    // saves a form submission
    public function save(Request $request)
    {

        // clean up last photo upload filename (dont need it at this point)
        session(['last_image_upload' => null]);

        $form = \App\Models\Form::find($request->input('form_id'));

        if ($form != null) {
            $form_fields = json_decode($form->fields);

            // build our validation array
            $validation_list = [];
            foreach ($form_fields as $field_uuid => $form_field) {
                if ($form_field->required) {
                    if ($form_field->name == null) {
                        $element_name = 'element-'.$field_uuid;
                    } else {
                        $element_name = 'element-'.$form_field->name;
                    }
                    $validation_list = array_merge($validation_list, [$element_name => 'required']);
                }
            }

            // if we're a special form, add the extra fields for validation
            switch ($form->special_form) {

                case 'new_user_app':
                    if (($request->has('user_id')) && ($request->input('user_id') > 0)) {
                        $user_id = $request->user_id;
                    } else {
                        $user_id = 0;
                    }

                    $validation_list = array_merge($validation_list, [
                        'first_name' => 'required',
                        'last_name' => 'required',
                        'address' => 'required',
                        'city' => 'required',
                        'province' => 'required',
                        'postal' => 'required',
                        'photo' => 'required',
                    ]);
                    break;

                    if ($user_id > 0) {
                        $validation_list = array_merge($validation_list, [
                            'email' => 'required',
                        ]);
                        break;
                    } else {
                        $validation_list = array_merge($validation_list, [
                            'email' => 'required|unique:users',
                        ]);
                        break;
                    }

            }

            if (count($validation_list) > 0) {
                $request->validate($validation_list);
            }

            // build our responses array
            $responses = [];

            foreach ($form_fields as $field_uuid => $form_field) {
                if ($form_field->name == null) {
                    $element_name = 'element-'.$field_uuid;
                } else {
                    $element_name = 'element-'.$form_field->name;
                }

                $field_value = null;
                switch ($form_field->type) {
                    case 'switch':
                        if ($request->has($element_name)) {
                            $field_value = $form_field->on;
                        } else {
                            $field_value = $form_field->off;
                        }
                        break;
                    case 'checkbox':
                        if ($request->has($element_name)) {
                            $field_value = [];
                            foreach ($request->input($element_name) as $key => $choice) {
                                $field_value[] = $choice;
                            }
                        } else {
                            $field_value[] = 'None selected';
                        }
                        break;
                    case 'upload':

                        break;

                    default:
                        $field_value = $request->input($element_name);
                }

                $responses[$field_uuid] = [
                    'label' => $form_field->label,
                    'value' => $field_value,
                    'type' => $form_field->type,
                ];
            }

            $append_message = null;

            // check and run any extra form actions
            if ($form->actions != null) {
                $form_actions = json_decode($form->actions);

                // loop through actions
                foreach ($form_actions as $key => $form_action) {
                    switch ($form_action->action) {
                        // remove a user flag from form submitter
                        case 'userflag_remove':
                            $user = \App\Models\User::where('id', \Auth::user()->id)->first();
                            if ($user->flags->contains('flag', $form_action->value)) {
                                $user->flags()->where('flag', $form_action->value)->delete();
                                $append_message .= ' '.$form_action->text;
                            }
                            break;
                    }
                }
            }

            // do anything that special forms require
            switch ($form->special_form) {
                case 'new_user_app':


                    // add hard-coded responses from new user form
                    $responses['first_name'] = ['label' => 'First Name', 'value' => $request->input('first_name'), 'type' => 'input'];
                    $responses['last_name'] = ['label' => 'Last Name', 'value' => $request->input('last_name'), 'type' => 'input'];
                    $responses['first_preferred'] = ['label' => 'Preferred First Name', 'value' => $request->input('first_preferred'), 'type' => 'input'];
                    $responses['last_preferred'] = ['label' => 'Preferred Last Name', 'value' => $request->input('last_preferred'), 'type' => 'input'];
                    $responses['pronouns'] = ['label' => 'Preferred Pronouns', 'value' => $request->input('pronouns'), 'type' => 'input'];
                    $responses['email'] = ['label' => 'Email Address', 'value' => $request->input('email'), 'type' => 'input'];
                    $responses['phone'] = ['label' => 'Phone Number', 'value' => $request->input('phone'), 'type' => 'input'];
                    $responses['address'] = ['label' => 'Street Address', 'value' => $request->input('address'), 'type' => 'input'];
                    $responses['city'] = ['label' => 'City', 'value' => $request->input('city'), 'type' => 'input'];
                    $responses['province'] = ['label' => 'Province', 'value' => $request->input('province'), 'type' => 'input'];
                    $responses['postal'] = ['label' => 'Postal Code', 'value' => $request->input('postal'), 'type' => 'input'];
                    $responses['photo'] = ['label' => 'Photo', 'value' => $request->input('photo'), 'type' => 'input'];

                    if ($request->input('first_preferred') == null) {
                        $first_preferred = $request->input('first_name');
                        $last_preferred = $request->input('last_name');
                    } else {
                        $first_preferred = $request->input('first_preferred');
                        $last_preferred = $request->input('last_preferred');
                    }

                    // returning user
                    if ($user_id > 0) {

                        // load up the previous user and update fields
                        $user = \App\Models\User::find($user_id);

                        $user->first_name = $request->input('first_name');
                        $user->last_name = $request->input('last_name');
                        $user->first_preferred = $first_preferred;
                        $user->last_preferred = $last_preferred;
                        $user->pronouns = $request->input('pronouns');
                        $user->status = 'applicant';
                        $user->phone = $request->input('phone');
                        $user->address = $request->input('address');
                        $user->city = $request->input('city');
                        $user->province = $request->input('province');
                        $user->postal = $request->input('postal');
                        $user->password = Hash::make($this->generate_random_password());
                        $user->photo = $request->input('photo');
                        $user->date_applied = date('Y-m-d');

                        $user->save();

                    } else {

                        // create the applicant user
                        $user = \App\Models\User::create([
                            'first_name' => $request->input('first_name'),
                            'last_name' => $request->input('last_name'),
                            'first_preferred' => $first_preferred,
                            'last_preferred' => $last_preferred,
                            'pronouns' => $request->input('pronouns'),
                            'email' => $request->input('email'),
                            'status' => 'applicant',
                            'date_applied' => date('Y-m-d'),
                            'phone' => $request->input('phone'),
                            'address' => $request->input('address'),
                            'city' => $request->input('city'),
                            'province' => $request->input('province'),
                            'postal' => $request->input('postal'),
                            'password' => Hash::make($this->generate_random_password()),
                            'member_id' => rand(1000, 9999),
                            'photo' => $request->input('photo'),
                        ]);

                    }

                    // create user's initial status record
                    $user_status = \App\Models\UserStatus::create([
                        'status' => 'applicant',
                        'user_id' => $user->id,
                        'updated_by' => 0,
                        'created_at' => date('Y-m-d'),
                        'updated_at' => date('Y-m-d'),
                    ]);


                    // build array for email use
                    $email_data = [
                        'name' => $user->get_name(),
                        'photo' => \URL::to('/storage/images/users/'.$user->photo.'.jpeg'),
                        'skip_fields' => ['first_name', 'last_name', 'first_preferred', 'last_preferred', 'email', 'phone', 'address', 'city', 'province', 'postal', 'photo', 'pronouns'],
                        'form_data' => $responses,
                    ];

                    // send email to members (limited contact info)
                    \Mail::send(new \App\Mail\MemberApp($email_data, 'members'));

                    // send email to admins (full contact info)
                    \Mail::send(new \App\Mail\MemberApp($email_data, 'admin'));

                    \Mail::send(new MemberAppInterviewConfirmation($email_data));

                    app(KosBot::class)->postNewAppplicantMessage($user);

                    $user_id = $user->id;
                    $message = 'Application created & sent successfully.';
                    $form_name = $form->name.' - '.$user->get_name();
                    break;

                default:
                    $user_id = 0;
                    $message = 'Form sent successfully.';
                    $form_name = $form->name;
            }

            // save the form submission
            $form_submission = \App\Models\FormSubmission::create([
                'form_id' => $form->id,
                'form_name' => $form_name,
                'special_form' => $form->special_form,
                'submitted_by' => \Auth::user()->id,
                'submitter_ip' => \Request::ip(),
                'submitter_agent' => substr($request->header('User-Agent'), 0, 250),
                'user_id' => $user_id,
                'data' => json_encode($responses),
            ]);

            // append text from form actions to $message
            if ($append_message != null) {
                $message .= $append_message;
            }

            return redirect('/forms/'.$form->id)->with('success', $message);
        }
    }

    // displays a form submission
    public function submission($id)
    {
        $submission = \App\Models\FormSubmission::find($id);
        if ($submission != null) {
            if ($submission->canview()) {
                $skip_fields = [];
                // if we should skip any fields for display, set them
                switch ($submission->special_form) {
                    case 'new_user_app':
                        $skip_fields = ['first_name', 'last_name', 'first_preferred', 'last_preferred', 'email', 'phone', 'address', 'city', 'province', 'postal', 'photo','pronouns'];
                        break;
                }

                return view('forms.submission_full', compact('submission', 'skip_fields'));
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
