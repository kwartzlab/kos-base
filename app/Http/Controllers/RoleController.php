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

                $permissions[$acl_object . ':' . $acl_operation] = 'selected';
                
            }
        }
        return view('roles.create',compact('permissions'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate(request(),[
            'name' => 'required|unique:roles'
        ]);

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
                'operation' => $acl_operation
            ];
            
        }

        // save User Role
        $acl_role = \App\Role::create([
            'name' => request('name'),
            'description' => request('description')
            ]);

        // add role permissions
        $acl_role->permissions()->createMany($acl_attributes);

        $message = "User Role added successfully.";
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
            $permissions[$permission['object'] . ':' . $permission['operation']] = 'selected';
        }

        // Get users list not already assigned to this role
        $user_list = array();
        foreach (\App\User::where('status','active')->orderby('first_name')->get() as $user) {
            
            if ($user->has_role($id) === FALSE) {
                $user_list[$user->id] = $user->first_name . " " . $user->last_name;
            }

        }

        return view('roles.edit', compact('role','permissions','user_list'));

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
        $this->validate(request(),[
            'name' => 'required'
        ]);

    
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
                'operation' => $acl_operation
            ];
            
        }

        // load existing record and update
        $acl_role = \App\Role::find($id);

        $acl_role->name = request('name');
        $acl_role->description = request('description');

        // save User Role
        $acl_role->save();

        // clear and re-add role permissions
        $acl_role->permissions()->delete();
        $acl_role->permissions()->createMany($acl_attributes);

        $message = "User Role updated successfully.";
        return redirect('/roles/' . $acl_role->id . '/edit')->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->validate(request(),[
            'confirm' => 'required'
        ]);

        // user trying to edit superuser role.. cheeky!
        if ($id == 1) {
            $message = "You cannot delete the superuser role. Nice try!";
            return redirect('/roles')->with('error', $message);
        }

        $role = \App\Role::find($id);
        
        // delete role permissions first
        $role->permissions()->delete();

        $role->delete();

        $message = "User Role deleted successfully.";
        return redirect('/roles')->with('success', $message);
    }
}
