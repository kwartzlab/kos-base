<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Image;

class ImageController extends Controller
{

    /* Displays image upload and crop interface */
    public function imageCrop($photo_type = NULL, $user_id = NULL)
    {
        if ($photo_type != 'users') {
            $photo_type = NULL;
            $user_id = NULL;
        }
        return view('shared.imagecrop',compact('photo_type','user_id'));
    }

    /* Process cropped image for use by kOS */
    public function imageCropPost(Request $request)
    {

        // parse input from croppie
        $data = $request->image;

        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);

        $data = base64_decode($data);

        // create random filename and set path
        $image_name= md5(Str::uuid() . microtime());

        if ($request->filled('photo_type')) {
            $path = public_path() . "/storage/images/" . $request->input('photo_type') . '/';
            // if user photo, update their db record as well (currently logged in user)
            if ($request->input('photo_type') == 'users') {
                if ($request->filled('user_id')) {
                    // make sure user has permission to modify the user photo
                    if ((\Gate::allows('manage-users')) || ($request->input('user_id') == \Auth::user()->id)) {
                        $set_user_photo = TRUE;
                        $user_id = $request->input('user_id');
                    } else { 
                        $set_user_photo = FALSE;
                    }
                } else {
                    $set_user_photo = FALSE;
                }
            }
        } else {
            $path = public_path() . "/storage/images/";
        }
       
        $path_filename = $path . $image_name . '.png';

        // save file
        file_put_contents($path_filename, $data);

        // save jpg version of original and generate additional thumbnail sizes
        $img = Image::make($path_filename);
        $img->save($path . $image_name . '.jpeg');
        $img->resize(512, 512);
        $img->save($path . $image_name . '-512px.jpeg');
        $img->resize(256, 256);
        $img->save($path . $image_name . '-256px.jpeg');
        $img->resize(128, 128);
        $img->save($path . $image_name . '-128px.jpeg');

        // if user photo was uploaded, set new filename in the user's profile
        if ($set_user_photo) {
            $user = \App\User::find($user_id);
            
            // delete previous image files to keep things tidy
            if ($user->photo != NULL) {
                foreach(glob($path . $user->photo . "*") as $f) {
                    unlink($f);
                }
            }
            
            $user->photo = $image_name;
            $user->save();
        }

        // send success response
        return response()->json(['success'=>'done', 'filename' => $image_name]);
    }

}
