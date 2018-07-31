<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class GatekeepersController extends Controller
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
    public function index()
    {

        if (\Auth::user()->acl == 'admin') {
     
            $gatekeepers = \App\Gatekeeper::orderby('name')->get();

            $page_title = "Gatekeepers";
            return view('gatekeeper.index', compact('page_title','gatekeepers'));
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
        
        if (\Auth::user()->acl == 'admin') {

            // generate auth key
            $auth_key = $this->generate_auth_key();
            
            $page_title = 'Add Gatekeeper';
            return view('gatekeeper.create', compact('page_title','auth_key'));

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

        if (\Auth::user()->acl == 'admin') {

            $this->validate(request(),[
                    'name' => 'required|unique:gatekeepers',
                    'ip_address' => 'required',
                    'auth_key' => 'required'
                ]);


            \App\Gatekeeper::create([
                'name' => request('name'),
                'description' => request('description'),
                'status' => request('status'),
                'type' => request('type'),
                'is_default' => request('is_default'),
                'ip_address' => request('ip_address'),
                'auth_key' => request('auth_key')
                ]);

            $message = "Gatekeeper added successfully.";
            return redirect('/gatekeepers')->withMessage($message);

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

        if (\Auth::user()->acl == 'admin') {

            $gatekeeper = \App\Gatekeeper::find($id);
            
            // get all users who are not yet authorized
            $user_ids = array();
            foreach (\App\User::where('status','active')->orderby('first_name')->get() as $user) {
                
                if ($user->is_authorized($id) === FALSE) {
                    $user_ids[$user->id] = $user->first_name . " " . $user->last_name;
                }

            }


            $page_title = "Editing: " . $gatekeeper->name;
            return view('gatekeeper.edit', compact('page_title','gatekeeper','user_ids'));

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

        if (\Auth::user()->acl == 'admin') {

            $this->validate(request(),[
                'name' => 'required',
                'ip_address' => 'required'
            ]);

            $gatekeeper = \App\Gatekeeper::find($id);

            $gatekeeper->name = $request->input('name');
            $gatekeeper->description = $request->input('description');
            $gatekeeper->status = $request->input('status');
            $gatekeeper->type = $request->input('type');
            $gatekeeper->is_default = $request->input('is_default');
            $gatekeeper->ip_address = $request->input('ip_address');

            if (request('auth_key') == NULL) {
                $gatekeeper->auth_key = $this->generate_auth_key();
            } else {
                $gatekeeper->auth_key = request('auth_key');    
            }

            $gatekeeper->save();
            $message = "Gatekeeper updated successfully.";
            if ($request->input('auth_key') == NULL) {
                $message .= "<br />Authentication key changed.";
            }

            return redirect('/gatekeepers')->withMessage($message);
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

        if (\Auth::user()->acl == 'admin') {

            $this->validate(request(),[
                'confirm' => 'required'
            ]);

            $gatekeeper = \App\Gatekeeper::find($id);
            $gatekeeper->delete();

            // remove all authorizations for this gatekeeper as well
            $result = \App\Authorization::where('gatekeeper_id',$id)->delete();

            $message = "Gatekeeper and related authorizations deleted.";
            return redirect('/gatekeepers')->withMessage($message);

        }
    }



    // post new trainer to gatekeeper
    public function add_trainer($gatekeeper_id) {

        if (\Auth::user()->acl == 'admin') {

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

            return redirect('/gatekeepers/' . $gatekeeper_id . '/edit')->withMessage($message);

        }

    }   

    // delete trainer from gatekeeper
    public function remove_trainer($gatekeeper_id, $trainer_id) {

        if (\Auth::user()->acl == 'admin') {

            $trainer = \App\Trainers::find($trainer_id);
            $trainer->delete();

            $message = "Trainer removed successfully.";
            return redirect('/gatekeepers/' . $gatekeeper_id . '/edit')->withMessage($message);

        }
    }
}
