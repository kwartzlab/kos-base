<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles = \App\Role::orderby('name')->get();

        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // Build permissions array for easy searching to build dropdown
        $permissions = [];
        if (old()) {
            foreach (old('acl-attributes') as $key => $acl_attribute) {
                // convert json string to array
                $acl_attribute = json_decode(str_replace('\'', '"', $acl_attribute), true);

                // determinte attribute object and operation
                $acl_object = array_keys($acl_attribute)[0];
                $acl_operation = $acl_attribute[$acl_object];

                $permissions[$acl_object.':'.$acl_operation] = 'selected';
            }
        }

        return view('roles.create', compact('permissions'));
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
            'name' => 'required|unique:roles',
        ]);

        // save User Role
        $acl_role = \App\Role::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);

        if ($request->has('acl-attributes')) {
            $acl_attributes = [];
            // combine all ACL elements from form into one big json
            foreach ($request->input('acl-attributes') as $key => $acl_attribute) {
                // convert json string to array
                $acl_attribute = json_decode(str_replace('\'', '"', $acl_attribute), true);

                // determinte attribute object and operation
                $acl_object = array_keys($acl_attribute)[0];
                $acl_operation = $acl_attribute[$acl_object];

                // add attribute to permissions array
                $acl_attributes[] = [
                    'object' => $acl_object,
                    'operation' => $acl_operation,
                ];
            }

            // add role permissions
            $acl_role->permissions()->createMany($acl_attributes);
        }

        $message = 'User Role added successfully.';

        return redirect('/roles')->with('success', $message);
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
        $role = \App\Role::find($id);

        $acl_permissions = $role->permissions()->get()->toArray();

        // Build permissions array for easy searching to build dropdown
        $permissions = [];
        foreach ($acl_permissions as $key => $permission) {
            $permissions[$permission['object'].':'.$permission['operation']] = 'selected';
        }

        // Get users list unassigned to this role
        $user_list = [];
        foreach (\App\User::where('status', 'active')->orderby('first_preferred')->get() as $user) {
            if ($user->has_role($id) === false) {
                $user_list[$user->id] = $user->get_name();
            }
        }

        // grab existing users assigned to this role
        $assigned_list = [];
        foreach (\App\UserRole::where('role_id', $role->id)->get() as $assigned_user_role) {
            $assigned_user = \App\User::find($assigned_user_role['user_id']);
            $assigned_list[] = [
                'name' => $assigned_user->get_name(),
                'id' => $assigned_user->id,
                'created_at' => $assigned_user_role->created_at->diffForHumans(),
            ];
        }

        return view('roles.edit', compact('role', 'permissions', 'user_list', 'assigned_list'));
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
            'name' => 'required',
        ]);

        // load existing record and update
        $acl_role = \App\Role::find($id);

        $acl_role->name = $request->input('name');
        $acl_role->description = $request->input('description');

        // save User Role
        $acl_role->save();

        // clear existing role permissions
        $acl_role->permissions()->delete();

        if ($request->has('acl-attributes')) {
            $acl_attributes = [];
            // combine all ACL elements from form into one big json
            foreach (request('acl-attributes') as $key => $acl_attribute) {
                // convert json string to array
                $acl_attribute = json_decode(str_replace('\'', '"', $acl_attribute), true);

                // determinte attribute object and operation
                $acl_object = array_keys($acl_attribute)[0];
                $acl_operation = $acl_attribute[$acl_object];

                // add attribute to permissions array
                $acl_attributes[] = [
                    'object' => $acl_object,
                    'operation' => $acl_operation,
                ];
            }

            $acl_role->permissions()->createMany($acl_attributes);
        }

        $message = 'User Role updated successfully.';

        return redirect('/roles/'.$acl_role->id.'/edit')->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->validate(request(), [
            'confirm' => 'required',
        ]);

        // user trying to edit superuser role.. cheeky!
        if ($id == 1) {
            $message = 'You cannot delete the superuser role. Nice try!';

            return redirect('/roles')->with('error', $message);
        }

        $role = \App\Role::find($id);

        // delete role permissions first
        $role->permissions()->delete();

        $role->delete();

        $message = 'User Role deleted successfully.';

        return redirect('/roles')->with('success', $message);
    }

    // post new trainer to gatekeeper
    public function add_user($role_id)
    {

        // make sure trainer isn't already in there

        $assigned_user = \App\UserRole::where(['role_id' => $role_id, 'user_id' => request('assign-user')])->get();

        if (count($assigned_user) > 0) {
            $message = 'User is already assigned to this role.';

            return redirect('/roles/'.$role_id.'/edit')->with('info', $message);
        } else {
            $assigned_user = \App\UserRole::create([
                'user_id' => request('assign-user'),
                'role_id' => $role_id,
            ]);

            $message = 'User added successfully.';
        }

        return redirect('/roles/'.$role_id.'/edit')->with('success', $message);
    }

    // delete trainer from gatekeeper
    public function remove_user($role_id, $user_id)
    {

        // ensure there is at least one superuser remaining
        if ($role_id == 1) {
            $role = \App\Role::find(1);
            if (count($role->users()->get()) < 2) {
                $message = 'Need to have at least one Superuser.';

                return redirect('/roles/'.$role_id.'/edit')->with('error', $message);
            }
        }

        $assigned_user = \App\UserRole::where(['user_id' => $user_id, 'role_id' => $role_id]);
        $assigned_user->delete();

        $message = 'User removed successfully.';

        return redirect('/roles/'.$role_id.'/edit')->with('success', $message);
    }
}
