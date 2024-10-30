<?php

namespace App\Http\Controllers;

use App\Mail\SlackInvite;
use App\Models\UserStatus;
use App\Traits\UserStatusTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    use UserStatusTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($filter = 'active')
    {
        $user_status = config('kwartzlabos.user_status');

        // make sure we have a valid filter
        if ((! isset($user_status[$filter])) && ($filter != 'all')) {
            $filter = 'active';
        }

        if ($filter == 'all') {
            $users = \App\Models\User::orderby('first_preferred')->get();
        } else {
            $users = \App\Models\User::where('status', $filter)->orderby('first_preferred')->get();
        }

        // Add All to status options (for filtering purposes)
        $user_status = ['all' => ['name' => 'All']] + $user_status;

        return view('users.index', compact('users', 'filter', 'user_status'));
    }

    private function generate_random_password()
    {
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
        $form = \App\Models\Form::where('special_form', 'new_user_app')->first();
        if ($form != null) {
            \Session::flash('skip_individual_errors', 1);
            $form_fields = json_decode($form->fields);

            return view('forms.show', compact('form', 'form_fields'));
        } else {
            $page_title = 'Membership Application';
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {}

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
        $user = \App\Models\User::find($id);

        // less messy to deal with an array than the direct config values
        $user_status = config('kwartzlabos.user_status');

        return view('users.edit', compact('user', 'user_status'));
    }

    // (ajax) checks an email address and returns user data if it exists (used in new user process for returning users)
    public function check_attributes(Request $request)
    {
        if ($request->has('email')) {
            $user = \App\Models\User::where('email', $request->input('email'))->first();
        } elseif ($request->has('first_name') || $request->has('last_name')) {
            $user = \App\Models\User::where(['first_name' => $request->input('first_name'), 'last_name' => $request->input('last_name')])->first();
        }

        if ($user != null) {
            $response = [
                'user_id' => $user->id,
                'name' => $user->get_name(),
                'photo' => $user->photo,
                'status' => $user->last_status()->get()->first()->status,
            ];

            return response()->json($response);
        }

        return response()->json(['user_id' => 0]);
    }

    public function do_stuff(Request $request, $id)
    {
        return response()->json(['status' => 'wheeeee']);
    }

    // adds or modifies a user status update
    public function update_status(Request $request, $id)
    {
        $user = \App\Models\User::find($id);

        if ($user == null) {
            return view('errors.403', null, 403);
        }

        if ($request->isMethod('post')) {
            if (! $request->has('status_type')) {
                return view('errors.403', null, 403);
            }

            // create new status update
            $status = new \App\Models\UserStatus([
                'user_id' => $user->id,
                'updated_by' => \Auth::user()->id,
                'created_at' => $request->input('effective_date'),
                'updated_at' => date('Y-m-d'),
            ]);
            if ($request->has('note')) {
                $status->note = $request->input('note');
            }

            switch ($request->input('status_type')) {
                case 'active':
                    $oldStatus = $user->status;

                    $status->status = 'active';
                    $status->save();

                    if (in_array($oldStatus, UserStatus::STATUSES_TO_ACTIVE_SEND_INVITES)) {
                        if (config('services.slack.auto_invite.enabled')) {
                            Mail::send(new SlackInvite($user));
                        }
                    }

                    break;
                case 'inactive':
                    $status->status = 'inactive';
                    $status->save();
                    break;
                case 'inactive-abandoned':
                    $status->status = 'inactive-abandoned';
                    $status->save();
                    break;
                case 'hiatus':
                    // create both the hiatus record and the active record for when hiatus is over
                    $status->status = 'hiatus';
                    $status->save();
                    $status_end = new \App\Models\UserStatus([
                        'user_id' => $user->id,
                        'updated_by' => \Auth::user()->id,
                        'status' => 'active',
                        'note' => 'Ending Hiatus',
                        'created_at' => $request->input('effective_date_ending'),
                        'updated_at' => date('Y-m-d'),
                    ]);
                    $status_end->save(['timestamps' => false]);
                    break;
                case 'suspended':
                    $status->status = 'suspended';
                    $status->save();
                    break;
                case 'terminated':
                    $status->status = 'terminated';
                    $status->save();
                    break;
                case 'applicant-abandoned':
                    $status->status = 'applicant-abandoned';
                    $status->save();
                    break;
                case 'applicant-denied':
                    $status->status = 'applicant-denied';
                    $status->save();
                    break;
            }
        } elseif ($request->isMethod('delete')) {
            // find & delete status update
            if ($request->has('status_id')) {
                $status = \App\Models\UserStatus::find($request->input('status_id'));

                // ensure this update is related to supplied user and was not created by kOS (not deletable)
                if (($status->user_id == $user->id) && ($status->updated_by > 0)) {
                    $status->delete();
                } else {
                    return response()->json(['error' => 'error']);
                }
            } else {
                return response()->json(['error' => 'error']);
            }
        }

        // force a check to ensure user's current status is still up to date
        $this->check_current_userstatus($user);

        return response()->json(['ok' => 'ok']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (Gate::allows('manage-users')) {
            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'first_preferred' => 'required',
                'last_preferred' => 'required',
                'email' => 'required',
                'password' => 'confirmed',
            ]);

            $user = \App\Models\User::find($id);

            $user->first_name = $request->input('first_name');
            $user->last_name = $request->input('last_name');
            $user->first_preferred = $request->input('first_preferred');
            $user->last_preferred = $request->input('last_preferred');
            $user->pronouns = $request->input('pronouns');
            $user->email = $request->input('email');
            $user->phone = $request->input('phone');
            $user->address = $request->input('address');
            $user->city = $request->input('city');
            $user->province = $request->input('province');
            $user->postal = $request->input('postal');
            $user->notes = $request->input('notes');

            // if password was submitted, change it
            if ($request->input('password') != null) {
                $user->password = Hash::make($request->input('password'));
            }

            $user->save();

            $message = 'User updated successfully.';
            if ($request->input('password') != null) {
                $message .= ' Password changed.';
            }

            return redirect('/users/'.$user->id.'/edit')->with('success', $message);
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
            $this->validate(request(), [
                'confirm' => 'required',
            ]);

            if ($id == \Auth::user()->id) {
                $message = 'Cannot delete yourself.';

                return redirect('/users')->with('error', $message);
            } else {
                $user = \App\Models\User::find($id);

                if ($user->status == 'applicant') {

                    // remove related form entries
                    $result = \App\Models\FormSubmission::where(['user_id' => $user->id, 'special_form' => 'new_user_app'])->delete();

                    // remove user
                    $user->delete();

                    $message = 'User & application form deleted.';

                    return redirect('/users')->with('success', $message);
                } else {
                    $message = 'Only applicants can be deleted.';

                    return redirect('/users/'.$user->id.'/edit')->with('error', $message);
                }
            }
        }
    }

    // toggles a user flag
    public function toggle_flag($user_id, $flag)
    {

        // ensure specified flag exists
        if (! array_key_exists($flag, config('kwartzlabos.user_flags'))) {
            return response()->json('invalid', 500);
        } else {
            // get user
            $user = \App\Models\User::find($user_id);

            // check specified flag and toggle it
            if ($user->flags->contains('flag', $flag)) {
                $user->flags()->where('flag', $flag)->delete();
            } else {
                $flag = new \App\Models\UserFlag(['flag' => $flag]);
                $user->flags()->save($flag);
            }

            return response()->json(['status' => 'OK']);
        }
    }

    // get key add form
    public function create_key($user_id)
    {
        $user = \App\Models\User::find($user_id);
        if (count($user) != 0) {
            $page_title = 'Adding key for '.$user->name;

            return view('keys.create', compact('page_title', 'user'));
        }
    }

    // post new key
    public function store_key($user_id)
    {
        $request->validate([
            'rfid' => 'required',
        ]);

        $user = \App\Models\Key::create([
            'user_id' => $user_id,
            'rfid' => md5($request->input('rfid')),
            'description' => $request->input('description'),
        ]);

        $message = 'Key added successfully.';

        return redirect('/users/'.$user_id.'/edit')->with('success', $message);
    }

    // delete single key
    public function destroy_key($user_id, $key_id)
    {
        $key = \App\Models\Key::find($key_id);
        $key->delete();

        $message = 'Key deleted successfully.';

        return redirect('/users/'.$user_id.'/edit')->with('success', $message);
    }
}
