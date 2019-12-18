<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $forms = \App\Forms::orderby('name')->get();
        return view('forms.index', compact('forms'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // set up special forms default
        if (old('special_form')) {
            $selected_assignment = old('selected_assignment');
        } else {
            $selected_assignment = '0';
        }

        return view('forms.create',compact('selected_assignment'));
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
        //dd($request->all());

        // build our field array and convert to json
        if ($request->input('element')) {
            $field_array = array();
            foreach ($request->input('element') as $field_uuid => $field) {

                if (array_key_exists('required',$field)) {
                    $field_required = 1;
                } else {
                    $field_required = 0;
                }

                $options_array = array();
                if (array_key_exists('options',$field)) {
                    foreach ($field['options'] as $option_uuid => $option) {
                        // only add option if name is filled in
                        if ($option['name'] != NULL) {
                            $options_array[$option_uuid] = [
                                'name' => $option['name'],
                                'value' => $option['value']
                            ];
                        }
                    }
                }

                switch($field['type']) {
                    case 'input':
                        $field_array[$field_uuid] = [
                            'type' => $field['type'],
                            'label' => $field['label'],
                            'name' => $field['name'],
                            'length' => $field['length'],
                            'mask' => $field['mask'],
                            'required' => $field_required,
                        ];
                        break;
                    case 'textarea':
                        if (array_key_exists('usehtml',$field)) {
                            $field_usehtml = 1;
                        } else {
                            $field_usehtml = 0;
                        }
                        $field_array[$field_uuid] = [
                            'type' => $field['type'],
                            'label' => $field['label'],
                            'name' => $field['name'],
                            'length' => $field['length'],
                            'usehtml' => $field_usehtml,
                            'required' => $field_required,
                        ];
                        break;
                    case 'switch':
                        $field_array[$field_uuid] = [
                            'type' => $field['type'],
                            'label' => $field['label'],
                            'name' => $field['name'],
                            'on' => $field['on'],
                            'off' => $field['off'],
                            'required' => $field_required,
                        ];
                        break;
                    case 'dropdown':
                        $field_array[$field_uuid] = [
                            'type' => $field['type'],
                            'label' => $field['label'],
                            'name' => $field['name'],
                            'options' => $options_array,
                            'required' => $field_required,
                        ];
                        break;
                    case 'radio':
                        $field_array[$field_uuid] = [
                            'type' => $field['type'],
                            'label' => $field['label'],
                            'name' => $field['name'],
                            'options' => $options_array,
                            'required' => $field_required,
                        ];
                        break;
                    case 'checkbox':
                        $field_array[$field_uuid] = [
                            'type' => $field['type'],
                            'label' => $field['label'],
                            'name' => $field['name'],
                            'options' => $options_array,
                            'required' => $field_required,
                        ];
                        break;
                    case 'upload':
                        if (array_key_exists('multiupload',$field)) {
                            $field_multiupload = 1;
                        } else {
                            $field_multiupload = 0;
                        }
                        $field_array[$field_uuid] = [
                            'type' => $field['type'],
                            'label' => $field['label'],
                            'name' => $field['name'],
                            'multiupload' => $field_multiupload,
                            'required' => $field_required,
                        ];
                        break;
                    }
            }

        }

        $form = \App\Forms::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => $request->input('status'),
            'special_form' => $request->input('special_form'),
            'fields' => json_encode($field_array)
            ]);

        $message = "Form created successfully.";
        return redirect('/forms/' . $form->id . '/edit')->with('success', $message);




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
        //
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
    public function destroy($id)
    {
        //
    }
}
