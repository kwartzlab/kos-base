<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Image;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         return view('shared.image');
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
         if($request->hasFile('profile_image')) {

            dd($request->input());

            // get hashed filename for saving
            $hashed_filename = $request->file('profile_image')->hashName();

            // break up filename
            $filename_temp = explode('.', $hashed_filename);
            $photo_filename = $filename_temp[0];
            $photo_fileext = end($filename_temp);

            $image_path = 'public/profile_images/';
            $public_path = 'storage/profile_images/';

            // save original file
            $request->file('profile_image')->storeAs($image_path, $hashed_filename);

            // crop image at full resolution and save as new original
            $img = Image::make(public_path($public_path . $hashed_filename));

            $img->crop($request->input('w'), $request->input('h'), $request->input('x1'), $request->input('y1'));
            $img->save($public_path . $photo_filename . '-cropped.' . $photo_fileext);

            // resize images into various resolutions for kOS use - 256, 512
            return redirect('image')->with(['success' => "Image cropped successfully."]);
         }


        dd(request());
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
