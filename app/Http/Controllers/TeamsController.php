<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeamsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
 
        // get teams user belongs to
        $my_teams = \Auth::user()->teams()->get();

        // filter out duplicate teams (when user has multiple roles)
        $my_teams = collect($my_teams)->unique();

        // if user is an admin, show the full teams table
        if ((\Auth::user()->is_allowed('teams', 'manage')) || (\Auth::user()->is_superuser())) {
            $all_teams = \App\Team::orderby('name')->get();
        } else {
            $all_teams = NULL;
        }

        $team_roles = config('kwartzlabos.team_roles');

        return view('teams.index', compact('my_teams','all_teams','team_roles'));
    }


    /**
     * Manages teams (admin)
     *
     * @return \Illuminate\Http\Response
     */
    public function manage()
    {
        $teams = \App\Team::orderby('name')->get();
        return view('teams.manage', compact('teams'));
    }

    /**
     * Send training requests & view past requests
     *
     * @return \Illuminate\Http\Response
     */
    public function training()
    {
        $gatekeepers = \App\Gatekeeper::where('status','enabled')->orderby('name')->get();
        $request_status = config('kwartzlabos.team_request_status');

        return view('teams.training', compact('gatekeepers','request_status'));
    }

        /**
     * Send training requests & view past requests
     *
     * @return \Illuminate\Http\Response
     */
    public function training_request($gatekeeper)
    {
        $gatekeeper_requested = \App\Gatekeeper::where(['status' => 'enabled', 'id' => $gatekeeper])->first();
        if (!$gatekeeper_requested == NULL) {
            $team = $gatekeeper_requested->team()->first();
            // check if request has already been submitted
            $request = \App\TeamRequest::whereNotIn('status',['cancelled','failed'])->where(['request_type' => 'training', 'user_id' => \Auth::user()->id, 'gatekeeper_id' => $gatekeeper_requested->id])->first();
            if ($request == NULL) {      // save request
                if ($team == NULL) { $team_id = 0; } else { $team_id = $team->id; }
                \App\TeamRequest::create([
                    'request_type' => 'training',
                    'status' => 'new',
                    'user_id' => \Auth::user()->id,
                    'team_id' => $team_id,
                    'gatekeeper_id' => $gatekeeper_requested->id
                    ]);

                return response()->json(['status' => 'success', 'message' => 'Request Sent']);
            } else {                        // request already exists
                return response()->json(['status' => 'error', 'message' => 'Request already exists.']);
            }

        } else {
            $message = "Unknown Gatekeeper.";
            return redirect('/teams/training')->with('error', $message);
        }
    }

    public function training_cancel($request_id)
    {

        $training_request = \App\TeamRequest::find($request_id);

        // ensure it's the user's request (or is a trainer for the gatekeeper)
        if (($training_request->user_id == \Auth::user()->id) || ($training_request->team()->is_trainer())) {

            $training_request->status = 'cancelled';
            $training_request->save();
            
            return response()->json(['status' => 'success', 'message' => 'Request Cancelled']);

        } else {
            return response()->json(['status' => 'error']);
        }

    }

    public function training_pass($request_id)
    {

        $training_request = \App\TeamRequest::find($request_id);

        // ensure it's the user's request (or is a trainer for the gatekeeper)
        if (($training_request->user_id == \Auth::user()->id) || ($training_request->team()->is_trainer())) {

            $training_request->status = 'completed';
            $training_request->save();
            
            $gatekeeper = $training_request->gatekeeper()->first();

            // Authorize user for gatekeepers
            $authorization = \App\Authorization::where(['gatekeeper_id' => $gatekeeper->id,'user_id' => $training_request->user_id])->get();
            if (count($authorization) == 0) {
                $authorization = \App\Authorization::create([
                    'user_id' => $training_request->user_id,
                    'gatekeeper_id' => $gatekeeper->id
                    ]);
            }
            return response()->json(['status' => 'success', 'message' => 'Request Complete']);
        } else {
            return response()->json(['status' => 'error']);
        }

    }

    public function training_fail($request_id)
    {
        $training_request = \App\TeamRequest::find($request_id);

        // ensure it's the user's request (or is a trainer for the gatekeeper)
        if (($training_request->user_id == \Auth::user()->id) || ($training_request->team()->is_trainer())) {

            $training_request->status = 'failed';
            $training_request->save();
            
            return response()->json(['status' => 'success', 'message' => 'Request Complete']);

        } else {
            return response()->json(['status' => 'error']);
        }

    }



    public function requests($team_id, $request_type)
    {

        $team = \App\Team::find($team_id);

        if ($team != NULL) {
            if ($request_type == 'training') {
                // ensure user is a trainer for this team
                if (($team->is_trainer()) || ($team->is_lead())) {
                    $gatekeepers = $team->gatekeepers()->where('status','enabled')->orderby('name')->get();
                    $request_status = config('kwartzlabos.team_request_status');
                    return view('teams.requests', compact('team','gatekeepers', 'request_status'));

                } else {
                    $message = "You are not a trainer on that team.";
                    return redirect('/teams')->with('error', $message);
                }
            } else if ($request_type == 'maintenance') {

            }
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // get all users
        $user_list = array();
        foreach (\App\User::where('status','active')->orderby('first_name')->get() as $user) {
            $user_list[$user->id] = $user->first_name . " " . $user->last_name;
        }

        return view('teams.create',['user_list' => $user_list]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|unique:teams'
        ]);

        // save team
        $team = \App\Team::create([
            'name' => $request->input('name'),
            'description' => $request->input('description')
        ]);

        $team_assignments = array();
        // add selected users to team roles
        foreach (config('kwartzlabos.team_roles') as $team_role => $team_data) {
            $team_array = $request->input($team_role);
            if ($team_array != NULL) {
                foreach ($team_array as $key => $user_id) {
                    $team_assignments[] = [
                        'user_id' => $user_id,
                        'team_role' => $team_role,
                        'team_id' => $team->id
                    ];
                }
            }
        }

        // add team assignments
        $team->assignments()->createMany($team_assignments);

        $message = "Team created successfully.";
        return redirect('/teams/' . $team->id . '/edit')->with('success', $message);
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
        $team = \App\Team::find($id);

        // get all users
        $user_list = array();
        foreach (\App\User::where('status','active')->orderby('first_name')->get() as $user) {
            $user_list[$user->id] = $user->first_name . " " . $user->last_name;
        }
        
        // get team assignments and build array for dropdowns
        $team_assignments = array();
        foreach (config('kwartzlabos.team_roles') as $team_role => $team_data) {
            if (old($team_role) != NULL) { 
                foreach (old($team_role) as $key => $user_id) {
                    $team_assignments[$team_role][] = $user_id;
                }
            } else {
                foreach ($team->assignments()->get() as $team_assignment) {
                    if ($team_assignment->team_role == $team_role) {
                        $team_assignments[$team_role][] = $team_assignment->user_id;
                    }
                }
            }

        }

        return view('teams.edit', compact('team', 'team_assignments', 'user_list'));
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

        $request->validate([
            'name' => 'required'
        ]);

        // load existing record and update
        $team = \App\Team::find($id);

        $team->name = $request->input('name');
        $team->description = $request->input('description');

        // save Team changes
        $team->save();

        // process team assignment changes
        foreach (config('kwartzlabos.team_roles') as $team_role => $team_data) {
            if ($request->has($team_role)) {
                // go through existing records and add/update based on the form input
                foreach ($request->input($team_role) as $key => $user_id) {
                    if (\App\TeamAssignment::where(['user_id' => $user_id, 'team_role' => $team_role, 'team_id' => $team->id])->count() == 0) {
                        $team->assignments()->create(['user_id' => $user_id, 'team_role' => $team_role, 'team_id' => $team->id]);
                    }
                    // find all relevant assignments and delete those which aren't in the form input
                    $role_members = $team->get_role_members($team_role);
                    foreach ($role_members as $role_member) {
                        if (!in_array($role_member['user_id'], $request->input($team_role))) {
                            $role_member->delete();
                        }
                    }
                }
            } else {
                // if there's no array, remove any remaining assigned members
                $role_members = $team->get_role_members($team_role);
                if ($role_members != false) {
                    foreach ($role_members as $role_member) {
                        $role_member->delete();
                    }
                }
            }
        }

        $message = "Team updated successfully.";
        return redirect('/teams/' . $team->id . '/edit')->with('success', $message);

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
