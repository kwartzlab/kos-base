<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

class MembersController extends Controller
{

    // creates a user profile directory
    public function index($view = 'default') {

        $users = \App\User::where('status', 'active')->orwhere('status', 'hiatus')->orderby('first_name')->get();

        $page_title = 'Member Directory (' . count($users) . ')';
        return view('members.index', compact('page_title','users'));

    }


    // returns a member profile
    public function profile($user_id) {

        $user = \App\User::find($user_id);

        if ($user_id == \Auth::user()->id) {
            $page_title = 'My Profile';
        } else {
            $page_title = 'Member Profile';
        }

        return view('members.profile', compact('page_title','user'));

    }

}
