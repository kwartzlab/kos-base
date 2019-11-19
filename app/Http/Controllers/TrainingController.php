<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrainingController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // grab training authorizations for the current user and corresponding gatekeepers
        $gatekeeper_ids = \App\Trainers::where('user_id', \Auth::user()->id)->get()->pluck('gatekeeper_id');
        $gatekeepers = \App\Gatekeeper::whereIn('id',$gatekeeper_ids)->get();

        return view('training.index', compact('gatekeepers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // get gatekeeper ID from request
        $gatekeeper = \App\Gatekeeper::find($request->input('gatekeeper_id'));
        
        // ensure user actually has access
        if ($gatekeeper->is_trainer()) {

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

            return redirect('/training/' . $gatekeeper->id . '/edit')->with($message_type, $message);

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
        $gatekeeper = \App\Gatekeeper::find($id);
        
        // ensure user actually has access
        if ($gatekeeper->is_trainer()) {

            // get all users who are not yet authorized
            $user_ids = array();
            foreach (\App\User::where('status','active')->orderby('first_name')->get() as $user) {
              
                if ($user->is_authorized($id) === FALSE) {
                    $user_ids[$user->id] = $user->first_name . " " . $user->last_name;
                }

            }

            // get records for all authorized users
            $authorized_users = \App\Authorization::where('gatekeeper_id', $gatekeeper->id)->get();

            return view('training.edit', compact('gatekeeper','user_ids','authorized_users'));

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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($gatekeeper_id,$id)
    {

        $gatekeeper = \App\Gatekeeper::find($gatekeeper_id);
        
        // ensure user actually has access
        if ($gatekeeper->is_trainer()) {
            $authorization = \App\Authorization::find($id);
            $authorization->delete();
    
            $message = "Authorization removed successfully.";
            return redirect('/training/' . $gatekeeper_id . '/edit')->with('success', $message);
        }


    }
}
