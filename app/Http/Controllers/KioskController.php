<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KioskController extends Controller
{
    
    public function index() {

    	if (\Auth::check()) {
	        return view('kiosk.mainmenu', ['page_title' => 'KeyKiosk']);
    	} else {
	        return view('kiosk.index', ['page_title' => 'KeyKiosk']);
    	}

    }

   	// login user based on RFID key, then redirect to main menu if successful
    public function authenticate(Request $request) {

    	$rfid = md5($request->input('rfid'));
		$key = \App\Key::where('rfid', $rfid)->first();

        if (count($key) != 0) {
        	// check user
			$user = \App\User::find($key->user_id);
	        if (count($user) != 0) {
	        	if ($user->status == 'active') {
	        		// log user in and redirect to main menu
	        		\Auth::loginUsingId($user->id);
	        		return redirect('/kiosk');

	        	} else {
	        		// user is inactive
		        	$error_message = 'User Inactive';
		            return view('kiosk.error', compact('error_message'));
	        	}

	        } else {
	        	// unknown user (should never happen)
	        	$error_message = 'Unknown User';
	            return view('kiosk.error', compact('error_message'));
	        }

        } else {
        	// unknown key
        	$error_message = 'Access Denied';
            return view('kiosk.error', compact('error_message'));

        }

    }

    public function logout() {

    	if (\Auth::check()) {
	    	\Auth::logout();
	    }

	    return redirect('/kiosk');
    }


    // presents interface to add a new card ID to database
    public function create_key(Request $request) {

        if (\Request::isMethod('get')) {

            // scanning key
            return view('kiosk.create_key', ['page_title' => 'KeyKiosk']);

        } else {                
            // make sure we have at least some input for rfid key
            if (!$request->input('rfid')) {
                return redirect('/kiosk/create_key');           
            }   

            // see if key is already in database
            $key = \App\Key::where('rfid', md5($request->input('rfid')))->first();
            if (count($key)>0) {
                $error_message = 'Key Already in Database';
                return view('kiosk.error', compact('error_message'));
            }

            if (!$request->input('user_id')) {

                // select user to assign key to
                $users = \App\User::orderby('first_name')->where('status','active')->get();

                $rfid = $request->input('rfid');
                $page_title = 'KeyKiosk';
                $vcenter = '' ; // disables vertical center
                return view('kiosk.select_user', compact('page_title','users','rfid','vcenter'));

            } else {

                // confirm user to assign key to
                $keyuser = \App\User::find($request->input('user_id'));
                
                $rfid = $request->input('rfid');
                $page_title = 'KeyKiosk';
                return view('kiosk.confirm_user', compact('page_title','keyuser','rfid'));

            }

        }


    } else {
        return redirect('/kiosk');            
    }


    // saves key to database
    public function store_key(Request $request) {

        $key = \App\Key::create([
            'user_id' => $request->input('user_id'),
            'rfid' => md5($request->input('rfid')),
            'description' => 'Added via kiosk by ' . \Auth::user()->first_name . " " . \Auth::user()->last_name
            ]);

        if (!$key->id) {

            $error_message = 'Error adding key; contact admin';
            return view('kiosk.error', compact('error_message'));

        } else {

            $message = 'Key Added Successfully';
            return view('kiosk.success', compact('message'));

        }


    }

    // unlocks a gatekeeper (if allowable)
    public function unlock() {


    }


}
