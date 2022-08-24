<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KioskController extends Controller
{
    public function index()
    {
        if (\Auth::check()) {
            $page = [
                'title' => 'Main Menu',
                'refresh' => 120,
                'refresh_url' => '/kiosk/logout',
            ];

            return view('kiosk.mainmenu', compact('page'));
        } else {
            $page = [
                'title' => 'Login',
                'refresh' => 300,
                'refresh_url' => '/kiosk',
                'heading' => 'Tap key to begin',
                'form_url' => '/kiosk/authenticate',
            ];

            return view('kiosk.rfid', compact('page'));
        }
    }

    // login user based on RFID key, then redirect to main menu if successful
    public function authenticate(Request $request)
    {
        $rfid = md5($request->input('rfid'));
        $key = \App\Key::where('rfid', $rfid)->first();

        if ($key != null) {
            // check user
            $user = $key->user()->first();
            if ($user != null) {
                if ($user->status == 'active') {
                    // log user in and redirect to main menu
                    \Auth::loginUsingId($user->id);

                    return redirect('/kiosk');
                } else {
                    // user is inactive
                    $error_message = 'User Not Active';
                }
            } else {
                // unknown user (should never happen)
                $error_message = 'Unknown User';
            }
        } else {
            // unknown key
            $error_message = 'Access Denied';
        }

        $page = [
            'title' => 'Login',
            'icon' => 'error',
            'heading' => $error_message,
            'refresh' => 3,
            'refresh_url' => '/kiosk',
        ];

        return view('kiosk.page', compact('page'));
    }

    public function logout()
    {
        if (\Auth::check()) {
            \Auth::logout();
        }

        return redirect('/kiosk');
    }

    // presents interface to add a new card ID to database
    public function create_key(Request $request)
    {
        if (\Request::isMethod('get')) {
            // scan new key
            $page = [
                'title' => 'Assign Key',
                'refresh' => 30,
                'refresh_url' => '/kiosk',
                'heading' => 'Tap key to be added',
                'form_url' => '/kiosk/create_key',
            ];

            return view('kiosk.rfid', compact('page'));
        } else {
            if (! $request->input('rfid')) {
                return redirect('/kiosk');
            }

            if ($request->has('no')) {
                // cancelled key assignment
                $page = [
                    'title' => 'Request Cancelled',
                    'icon' => 'cancel',
                    'heading' => 'Request Cancelled',
                    'refresh' => 3,
                    'refresh_url' => '/kiosk',
                ];

                return view('kiosk.page', compact('page'));
            }

            // check if the key is already in the database
            $key = \App\Key::where('rfid', md5($request->input('rfid')))->first();
            if ($key != null) {
                // if we haven't confirmed to reassign key yet, ask
                if (! $request->has('reassign')) {
                    $page = [
                        'title' => 'Assign Key',
                        'text' => 'Do you want to reassign this key?',
                        'icon' => 'warning',
                        'refresh' => 30,
                        'refresh_url' => '/kiosk',
                        'form_url' => '/kiosk/create_key',
                        'form_hidden' => [
                            'rfid' => $request->input('rfid'),
                            'reassign' => 'true',
                        ],
                    ];

                    // lookup user (shouldn't be a key in the db without one!)
                    $user = $key->user()->first();
                    if ($user != null) {
                        $page['subheading'] = $user->get_name();
                        if ($user->status == 'active') {
                            $page['heading'] = 'Key already assigned to <strong>active</strong> user';
                        } else {
                            $page['heading'] = 'Key already assigned to <strong>inactive</strong> user';
                        }
                    } else {
                        $page['heading'] = 'Key Already in Database';
                        $page['subheading'] = 'User Unknown?!';
                    }

                    return view('kiosk.question', compact('page'));
                }
            }

            // if we don't have a user id yet, show user selection
            if (! $request->has('user_id')) {
                // select user to assign key to
                $users = \App\Models\User::orderby('first_preferred')->where('status', 'active')->get();
                $page = [
                    'title' => 'Assign Key',
                    'refresh' => 60,
                    'refresh_url' => '/kiosk',
                    'use_navbar' => 'true',
                    'form_url' => '/kiosk/create_key',
                    'form_hidden' => [
                        'rfid' => $request->input('rfid'),
                        'reassign' => 'true',
                    ],
                ];

                return view('kiosk.select_user', compact('page', 'users'));
            } else {
                // confirm user to assign key to
                $keyuser = \App\Models\User::find($request->input('user_id'));
                if ($keyuser != null) {
                    $page = [
                        'title' => 'Confirm Assignment',
                        'icon' => 'warning',
                        'heading' => 'Confirm User Assignment',
                        'text' => ' ',
                        'subheading' => $keyuser->get_name(),
                        //                        'refresh' => 30,
                        //                        'refresh_url' => '/kiosk',
                        'form_url' => '/kiosk/store_key',
                        'form_hidden' => [
                            'rfid' => $request->input('rfid'),
                            'user_id' => $keyuser->id,
                            'reassign' => 'true',
                        ],
                    ];

                    return view('kiosk.question', compact('page'));
                } else {
                    return redirect('/kiosk');
                }
            }
        }
    }

    // confirms assignment, saves key to database
    public function store_key(Request $request)
    {

        // only proceed if user had confirmed, otherwise bail
        if ($request->has('yes')) {
            $rfid = md5($request->input('rfid'));
            $key = \App\Key::where('rfid', $rfid)->first();

            // if key already exists in db, delete it
            if ($key != null) {
                $key->delete();
            }
            // just to be on the safe side ;)
            unset($key);

            $key = \App\Key::create([
                'user_id' => $request->input('user_id'),
                'rfid' => $rfid,
                'description' => 'Added via kiosk by '.\Auth::user()->get_name(),
            ]);

            if (! $key->id) {
                $page = [
                    'title' => 'Error Adding Key',
                    'icon' => 'error',
                    'heading' => 'Error Adding Key',
                    'subheading' => 'Contact Admin',
                    'refresh' => 3,
                    'refresh_url' => '/kiosk',
                ];

                return view('kiosk.page', compact('page'));
            } else {
                $page = [
                    'title' => 'Key Added Successfully',
                    'icon' => 'success',
                    'heading' => 'Key Added Successfully',
                    'refresh' => 3,
                    'refresh_url' => '/kiosk',
                ];

                return view('kiosk.page', compact('page'));
            }
        } else {
            $page = [
                'title' => 'Request Cancelled',
                'icon' => 'cancel',
                'heading' => 'Request Cancelled',
                'refresh' => 3,
                'refresh_url' => '/kiosk',
            ];

            return view('kiosk.page', compact('page'));
        }
    }
}
