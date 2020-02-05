<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GatekeepersController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (\Gate::allows('manage-gatekeepers')) {
            $gatekeepers = \App\Gatekeeper::orderby('name')->get();
            return view('gatekeeper.index', compact('gatekeepers'));
        } else {
            $message = "You do not have access to that resource.";
            return redirect('/')->with('error',$message);
        }
    }

    // generates a pseudo-random authentication key for gatekeeper devices
    public function generate_auth_key() {

         $characters = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

         $string = '';
         $max = strlen($characters) - 1;
         for ($i = 0; $i < 32; $i++) {
              $string .= $characters[mt_rand(0, $max)];
         }

         return $string;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {

        if (\Gate::allows('manage-gatekeepers')) {
        
            // generate auth key
            $auth_key = $this->generate_auth_key();

            // set up teams dropdown
            $teams = \App\Team::orderby('name')->get();
            if (old('team_id')) {
                $selected_team = old('team_id');
            } else {
                $selected_team = 0;
            }

            // grab list of gatekeepers (for shared auth field)
            $gatekeepers = \App\Gatekeeper::where(['status' => 'enabled', 'is_default' => '0'])->where('type','!=','training')->orderby('name')->get();
            if (old('shared_auth')) {
                $shared_auth = old('shared_auth');
            } else {
                $shared_auth = 0;
            }

            $page_title = 'Add Gatekeeper';
            return view('gatekeeper.create', compact('page_title','auth_key', 'teams', 'selected_team','shared_auth','gatekeepers'));
        } else {
            $message = "You do not have access to that resource.";
            return redirect('/')->with('error',$message);
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

        if (\Gate::allows('manage-gatekeepers')) {
            $request->validate([
                    'name' => 'required|unique:gatekeepers',
                    'auth_key' => 'required',
                ]);

            if ($request->input('is_default') == 'on') { $is_default = 1; } else { $is_default = 0; }

            \App\Gatekeeper::create([
                'name' => $request->input('name'),
                'status' => $request->input('status'),
                'type' => $request->input('type'),
                'is_default' => $is_default,
                'auth_key' => $request->input('auth_key'),
                'shared_auth' => $request->input('shared_auth'),
                'team_id' => $request->input('team_id')
                ]);

            $message = "Gatekeeper added successfully.";
            return redirect('/gatekeepers')->with('success', $message);

        } else {
            $message = "You do not have access to that resource.";
            return redirect('/')->with('error',$message);
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
        $gatekeeper = \App\Gatekeeper::find($id);

        if ($gatekeeper != NULL) {
            return view('gatekeeper.show', compact('gatekeeper'));
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

        if (\Gate::allows('manage-gatekeepers')) {

            $gatekeeper = \App\Gatekeeper::find($id);
            
            // get all users who are not yet authorized
            $user_ids = array();
            foreach (\App\User::where('status','active')->orderby('first_name')->get() as $user) {
                if ($user->is_trainer($id) === FALSE) {
                    $user_ids[$user->id] = $user->first_name . " " . $user->last_name;
                }
            }

            // set up teams dropdown
            $teams = \App\Team::orderby('name')->get();
            if (old('team_id')) {
                $selected_team = old('team_id');
            } else {
                $selected_team = $gatekeeper->team_id;
            }

            // grab list of gatekeepers (for shared auth field)
            $gatekeepers = \App\Gatekeeper::where(['status' => 'enabled', 'is_default' => '0'])->where('type','!=','training')->where('id', '!=', $gatekeeper->id)->orderby('name')->get();
            if (old('shared_auth')) {
                $shared_auth = old('shared_auth');
            } else {
                $shared_auth = $gatekeeper->shared_auth;
            }

            return view('gatekeeper.edit', compact('gatekeeper','user_ids', 'teams', 'selected_team', 'shared_auth', 'gatekeepers'));

        } else {
            $message = "You do not have access to that resource.";
            return redirect('/')->with('error',$message);
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

        if (\Gate::allows('manage-gatekeepers')) {
       
            $request->validate([
                'name' => 'required',
            ]);

            if ($request->has('is_default')) { $is_default = 1; } else { $is_default = 0; }

            $gatekeeper = \App\Gatekeeper::find($id);

            // if team was changed, update gatekeeper-specific team assignments
            if ($gatekeeper->team_id != $request->input('team_id')) {
                $assignments = \App\TeamAssignment::where('gatekeeper_id', $gatekeeper->id)->get();
                if ($assignments != NULL) {
                    foreach ($assignments as $assignment) {
                        $assignment->team_id = $request->input('team_id');
                        $assignment->save();
                    }
                }
            }
            
            $gatekeeper->name = $request->input('name');
            $gatekeeper->status = $request->input('status');
            $gatekeeper->type = $request->input('type');
            $gatekeeper->is_default = $is_default;
            $gatekeeper->team_id = $request->input('team_id');
            $gatekeeper->shared_auth = $request->input('shared_auth');
            
            if (request('auth_key') == NULL) {
                $gatekeeper->auth_key = $this->generate_auth_key();
            } else {
                $gatekeeper->auth_key = request('auth_key');    
            }

            $gatekeeper->save();
            $message = "Gatekeeper updated successfully.";
            if ($request->input('auth_key') == NULL) {
                $message .= " Authentication key changed - update your gatekeeper configuration accordingly.";
            }

            return redirect('/gatekeepers/' . $gatekeeper->id . '/edit')->with('success', $message);

        } else {
            $message = "You do not have access to that resource.";
            return redirect('/')->with('error',$message);
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

        if (\Gate::allows('manage-gatekeepers')) {

            $this->validate(request(),[
                'confirm' => 'required'
            ]);

            $gatekeeper = \App\Gatekeeper::find($id);
            $gatekeeper->delete();

            // remove all authorizations for this gatekeeper as well
            $result = \App\Authorization::where('gatekeeper_id',$id)->delete();

            // remove all authentication history
            $result = \App\Authentication::where('gatekeeper_id',$id)->delete();

            // remove any gatekeeper-specific team assignments (eg trainers, maintainers)
            $result = \App\TeamAssignment::where('gatekeeper_id',$id)->delete();

            // remove current status info
            $result = \App\GatekeeperStatus::where('gatekeeper_id',$id)->delete();

            $message = "Gatekeeper and related history deleted successfully.";
            return redirect('/gatekeepers')->with('success', $message);

        } else {
            $message = "You do not have access to that resource.";
            return redirect('/')->with('error',$message);
        }

    }

    // approves / rejects gatekeeper-related team assignments
    public function assignments($request_action, $request_id) {

        if (\Gate::allows('manage-teams')) {

            switch ($request_action) {
                case 'approve':
                    $assignment = \App\TeamAssignment::find($request_id);
                    if ($assignment != NULL) {
                        $assignment->status = 'active';
                        $assignment->save();
                        return response()->json(['status' => 'success', 'message' => 'Request Approved']);
                    }
                    break;
                case 'remove':
                    $assignment = \App\TeamAssignment::find($request_id);
                    if ($assignment != NULL) {
                        $assignment->delete();
                        return response()->json(['status' => 'success', 'message' => 'Request Removed']);
                    }
                    break;
                }

        }
        return response()->json(['status' => 'error', 'message' => 'Error Approving Request']);

    }



    // assign trainer to gatekeeper
    public function add_trainer($gatekeeper_id, $user_id) {

        // get gatekeeper ID from request
        $gatekeeper = \App\Gatekeeper::find($gatekeeper_id);

        $team = $gatekeeper->team()->first();
        if ($team == NULL) { 
            $has_team = false; 
            $team_id = 0;
            $trainer_status = 'active';
        } else { 
            $has_team = true; 
            $team_id = $team->id;
        }

        if (config('kwartzlabos.team_roles.trainer.approval_required')) {
            $trainer_status = 'new';
        } else {
            $trainer_status = 'active';
        }

        // ensure user actually has access
        if ((\Gate::allows('manage-gatekeepers')) || (($has_team) && ($team->is_lead()))) {
            
            \App\TeamAssignment::create([
                'user_id' => $user_id,
                'team_id' => $team_id,
                'team_role' => 'trainer',
                'gatekeeper_id' => $gatekeeper_id,
                'status' => $trainer_status
                ]);

            return response()->json(['status' => 'success', 'message' => 'Trainer Added Successfully']);
        }
        return response()->json(['status' => 'error', 'message' => 'Error Adding Trainer']);

    }
    // delete trainer from gatekeeper
    public function remove_trainer($gatekeeper_id, $trainer_id) {

        // get gatekeeper ID from request
        $gatekeeper = \App\Gatekeeper::find($gatekeeper_id);

        $team = $gatekeeper->team()->first();
        if ($team == NULL) { $has_team = false; } else { $has_team = true; }

        // ensure user actually has access
        if ((\Gate::allows('manage-gatekeepers')) || (($has_team) && ($team->is_lead()))) {
            $trainer = \App\TeamAssignment::where(['user_id' => $trainer_id, 'gatekeeper_id' => $gatekeeper_id, 'team_role' => 'trainer'])->first();
            $trainer->delete();

            return response()->json(['status' => 'success', 'message' => 'Trainer Removed']);
        }
        return response()->json(['status' => 'error', 'message' => 'Error Removing Trainer']);

    }

    // assign maintainer to gatekeeper
    public function add_maintainer($gatekeeper_id, $user_id) {

        // get gatekeeper ID from request
        $gatekeeper = \App\Gatekeeper::find($gatekeeper_id);

        $team = $gatekeeper->team()->first();
        if ($team == NULL) { 
            $has_team = false; 
            $team_id = 0;
            $trainer_status = 'active';
        } else { 
            $has_team = true; 
            $team_id = $team->id;
            if (config('kwartzlabos.team_roles.maintainer.approval_required')) {
                $trainer_status = 'new';
            } else {
                $trainer_status = 'active';
            }
        }

        // ensure user actually has access
        if ((\Gate::allows('manage-gatekeepers')) || (($has_team) && ($team->is_lead()))) {
            
            \App\TeamAssignment::create([
                'user_id' => $user_id,
                'team_id' => $team_id,
                'team_role' => 'maintainer',
                'gatekeeper_id' => $gatekeeper_id,
                'status' => $trainer_status
                ]);

            return response()->json(['status' => 'success', 'message' => 'Maintainer Added Successfully']);
        }
        return response()->json(['status' => 'error', 'message' => 'Error Adding Maintainer']);

    }

    // delete maintainer from gatekeeper
    public function remove_maintainer($gatekeeper_id, $maintainer_id) {

        // get gatekeeper ID from request
        $gatekeeper = \App\Gatekeeper::find($gatekeeper_id);

        $team = $gatekeeper->team()->first();
        if ($team == NULL) { $has_team = false; } else { $has_team = true; }

        // ensure user actually has access
        if ((\Gate::allows('manage-gatekeepers')) || (($has_team) && ($team->is_lead()))) {
            $maintainer = \App\TeamAssignment::where(['user_id' => $maintainer_id, 'gatekeeper_id' => $gatekeeper_id, 'team_role' => 'maintainer'])->first();
            $maintainer->delete();

            return response()->json(['status' => 'success', 'message' => 'Maintainer Removed']);
        }
        return response()->json(['status' => 'error', 'message' => 'Error Removing Maintainer']);

    }
    
    /* User dashboard for authorizations, machine locking, etc */
    /* Open to gatekeeper managers and team members the gatekeeper is assigned to */
    public function dashboard($id) {

        $gatekeeper = \App\Gatekeeper::find($id);

        if ($gatekeeper != NULL) {
            $team = $gatekeeper->team()->first();

            if ($team == NULL) { $has_team = false; } else { $has_team = true; }

            if ((\Gate::allows('manage-gatekeepers')) || (($has_team) && ($team->is_member()))) {
 
                $authorizations = \App\Authorization::where('gatekeeper_id', $gatekeeper->id)->get();

                // compile user list array with additional info for dropdowns
                $all_users = array();
                foreach (\App\User::where('status','active')->orderby('first_name')->get() as $user) {
                    $all_users[$user->id] = [
                        'name' => $user->get_name(),
                        'is_trainer' => $user->is_trainer($gatekeeper->id),
                        'is_maintainer' => $user->is_maintainer($gatekeeper->id),
                        'is_authorized' => $user->is_authorized($gatekeeper->id)
                    ];
                }
    
                // get heartbeat status (for display purposes)
                if ($gatekeeper->last_seen != NULL) {
                    $current_time = Carbon::now();
                    $last_seen = new Carbon($gatekeeper->last_seen);
                    $time_diff = $current_time->diffInMinutes($last_seen);
                    if ($time_diff > 20) { 
                        $heartbeat_status = 'danger';
                    } else {
                        $heartbeat_status = 'success';
                    }
                } else {
                    $heartbeat_status = 'warning';
                }

                return view('gatekeeper.dashboard', compact('gatekeeper','team', 'has_team', 'authorizations', 'all_users','heartbeat_status'));
            }
        }

    }

    // shows tool lockouts with usage information (for general users)
    public function tool_list() {

        $gatekeepers = \App\Gatekeeper::where(['status' => 'enabled', 'type' => 'lockout'])->get();

        return view('gatekeeper.tools', compact('gatekeepers'));

    }

    // authorizes a user for a gatekeeper (bypasses training system)
    public function grant_auth(Request $request) {

        // get gatekeeper ID from request
        $gatekeeper = \App\Gatekeeper::find($request->input('gatekeeper_id'));

        $team = $gatekeeper->team()->first();
        if ($team == NULL) { $has_team = false; } else { $has_team = true; }

        // ensure user actually has access
        if ((\Gate::allows('manage-gatekeepers')) || (($has_team) && ($team->is_member()))) {

            // make sure authorization isn't already in there
            $authorization = \App\Authorization::where(['gatekeeper_id' => $gatekeeper->id,'user_id' => request('user_id')])->get();

            if (count($authorization)>0) {
                $message = "Authorization already exists for this gatekeeper.";
                $message_type = "info";
            } else {
                $authorization = \App\Authorization::create([
                    'user_id' => request('user_id'),
                    'gatekeeper_id' => $gatekeeper->id
                    ]);
        
                $message = "Authorization added successfully.";
                $message_type = "success";
            }

            return redirect('/gatekeepers/' . $gatekeeper->id . '/dashboard')->with($message_type, $message);

        }

    }

    // de-authorizes a user from a gatekeeper
    public function revoke_auth($auth_id) {

        $auth_record = \App\Authorization::find($auth_id);
        $gatekeeper = $auth_record->gatekeeper()->first();

        $team = $gatekeeper->team()->first();
        if ($team == NULL) { $has_team = false; } else { $has_team = true; }

        if (($auth_record != NULL) && ($gatekeeper != NULL)) {
            // ensure managing user has permission to do this
            if ((\Gate::allows('manage-gatekeepers')) || (($has_team) && ($team->is_member()))) {
                $auth_record->delete();
                return response()->json(['status' => 'success', 'message' => 'Auth Revoked']);
            } else {
                return response()->json(['status' => 'error']);
            }
        }
        return response()->json(['status' => 'error']);

    }


}
