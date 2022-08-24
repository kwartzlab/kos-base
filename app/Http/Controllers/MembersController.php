<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MembersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($view = 'default')
    {
        $users = \App\User::where('status', 'active')->orwhere('status', 'hiatus')->orderby('first_preferred')->get();

        $page_title = 'Member Directory ('.count($users).')';

        return view('members.index', compact('page_title', 'users'));
    }

    // lists users with specific skill set
    public function skill($skill_id)
    {
        $skill = \App\UserSkill::find($skill_id);
        if ($skill == null) {
            return redirect('/members/')->with('info', 'Unknown Skill.');
        }

        return view('members.skill', compact('skill'));
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
        //
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
        $user = \App\User::find($id);

        if ($id == \Auth::user()->id) {
            $page_title = 'My Profile';
        } else {
            $page_title = 'Member Profile';
        }

        // build existing skills list
        $skill_data = \App\UserSkill::orderby('skill')->get();
        $ex_skills = $skill_data->unique('skill');

        $existing_skills = [];
        if ($ex_skills->count() > 0) {
            foreach ($ex_skills as $skill) {
                $existing_skills[(string) Str::uuid()] = $skill->skill;
            }
        }

        $user_skills = [];
        if (old('skills')) {
            $skills = old('skills');
            foreach ($skills as $key => $skill) {
                $user_skills[(string) Str::uuid()] = $skill;
            }
        } else {
            $skills = $user->skills()->get();
            foreach ($skills as $skill) {
                $user_skills[(string) Str::uuid()] = $skill->skill;
            }
        }

        // build social array to match the form input
        $user_socials = [];
        if (old('socials')) {
            $user_socials = old('socials');
        } else {
            $socials = $user->socials()->get();
            if (count($socials) > 0) {
                foreach ($socials as $social) {
                    $user_socials[(string) Str::uuid()] = [
                        'service' => $social['service'],
                        'profile' => $social['profile'],
                    ];
                }
            }
        }

        // build social array to match the form input
        $user_certs = [];
        if (old('certs')) {
            $user_certs = old('certs');
        } else {
            $certs = $user->certs()->get();
            if (count($certs) > 0) {
                foreach ($certs as $cert) {
                    $user_certs[(string) Str::uuid()] = [
                        'type' => $cert['type'],
                        'name' => $cert['name'],
                        'expiry_date' => $cert['expiry_date'],
                    ];
                }
            }
        }

        return view('members.edit', compact('page_title', 'user', 'existing_skills', 'user_socials', 'user_certs', 'user_skills'));
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
        $user = \App\User::find($id);

        $request->validate([
            'email' => 'required|unique:users,email,'.$user->id,
            'address' => 'required',
            'city' => 'required',
            'province' => 'required',
            'postal' => 'required',
            'password' => 'confirmed',
        ]);

        // ensure we're only editing our own profiles...
        if ($user->id == \Auth::user()->id) {

            // compile socials
            if ($request->input('socials')) {
                // remove existing records
                \App\UserSocial::where('user_id', $id)->delete();
                // add form records
                foreach ($request->input('socials') as $key => $social) {
                    if ($social['profile'] != null) {
                        $social_rec = new \App\UserSocial([
                            'service' => $social['service'],
                            'profile' => $social['profile'],
                        ]);
                        $user->socials()->save($social_rec);
                    }
                }
            }

            // compile skills
            if ($request->input('skills')) {
                // remove existing records
                \App\UserSkill::where('user_id', $id)->delete();
                // add form records
                foreach ($request->input('skills') as $key => $skill) {
                    $skill_rec = new \App\UserSkill(['skill' => $skill]);
                    $user->skills()->save($skill_rec);
                }
            }

            // compile certs
            if ($request->input('certs')) {
                // remove existing records
                \App\UserCert::where('user_id', $id)->delete();
                // add form records
                foreach ($request->input('certs') as $key => $cert) {
                    $cert_rec = new \App\UserCert([
                        'type' => $cert['type'],
                        'name' => $cert['name'],
                        'expiry_date' => $cert['expiry_date'],
                    ]);
                    $user->certs()->save($cert_rec);
                }
            }

            $user->email = $request->input('email');
            $user->phone = $request->input('phone');
            $user->address = $request->input('address');
            $user->city = $request->input('city');
            $user->province = $request->input('province');
            $user->postal = $request->input('postal');

            if ($request->input('password')) {
                $user->password = Hash::make($request->input('password'));
            }

            $user->save();
            $message = 'Profile updated successfully.';
            if ($request->input('password')) {
                $message .= ' Password changed.';
            }

            return redirect('/members/'.$user->id.'/profile')->with('success', $message);
        } else {
            $message = 'You do not have access to that resource.';

            return redirect('/')->with('error', $message);
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
        //
    }
}
