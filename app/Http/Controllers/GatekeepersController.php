<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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

            $page_title = 'Add Gatekeeper';
            return view('gatekeeper.create', compact('page_title','auth_key', 'teams', 'selected_team'));
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
                'description' => $request->input('description'),
                'status' => $request->input('status'),
                'type' => $request->input('type'),
                'is_default' => $is_default,
                'ip_address' => $request->input('ip_address'),
                'auth_key' => $request->input('auth_key'),
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

            return view('gatekeeper.edit', compact('gatekeeper','user_ids', 'teams', 'selected_team'));

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

            $gatekeeper->name = $request->input('name');
            $gatekeeper->description = $request->input('description');
            $gatekeeper->status = $request->input('status');
            $gatekeeper->type = $request->input('type');
            $gatekeeper->is_default = $is_default;
            $gatekeeper->ip_address = $request->input('ip_address');
            $gatekeeper->team_id = $request->input('team_id');

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

            $message = "Gatekeeper and authentication history deleted successfully.";
            return redirect('/gatekeepers')->with('success', $message);

        } else {
            $message = "You do not have access to that resource.";
            return redirect('/')->with('error',$message);
        }

    }

    // post new trainer to gatekeeper
    public function add_trainer($gatekeeper_id) {

        // make sure trainer isn't already in there
        $trainer = \App\Trainers::where(['gatekeeper_id' => $gatekeeper_id,'user_id' => request('user_id')])->get();

        if (count($trainer)>0) {
            $message = "Trainer already exists for this gatekeeper.";
        } else {

            $trainer = \App\Trainers::create([
                'user_id' => request('user_id'),
                'gatekeeper_id' => $gatekeeper_id
                ]);
    
            $message = "Trainer added successfully.";
    
        }

        return redirect('/gatekeepers/' . $gatekeeper_id . '/edit')->with('success', $message);


    }   

    // delete trainer from gatekeeper
    public function remove_trainer($gatekeeper_id, $trainer_id) {


        $trainer = \App\Trainers::find($trainer_id);
        $trainer->delete();

        $message = "Trainer removed successfully.";
        return redirect('/gatekeepers/' . $gatekeeper_id . '/edit')->with('success',$message);

    }

    /* User dashboard for authorizations, machine locking, etc */
    /* Open to gatekeeper managers and team members the gatekeeper is assigned to */
    public function dashboard($id) {

        $gatekeeper = \App\Gatekeeper::find($id);

        if ($gatekeeper != NULL) {
            $team = $gatekeeper->team()->first();

            if ($team == NULL) { $has_team = false; } else { $has_team = true; }
            $authorizations = \App\Authorization::where('gatekeeper_id', $gatekeeper->id)->get();
    
            // ensure user can manage gatekeepers or is on the managing team
            if ((\Gate::allows('manage-gatekeepers')) || (($has_team) && ($team->is_member()))) {
    
                return view('gatekeeper.dashboard', compact('gatekeeper','team', 'has_team', 'authorizations'));
    
            } else {
                $message = "You do not have access to that resource.";
                return redirect('/')->with('error',$message);
            }
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
