<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Image;

class ImageController extends Controller
{
    /* Displays image upload and crop interface */
    public function imageCrop($photo_type = null, $id = null)
    {
        if (! in_array($photo_type, ['users', 'teams', 'gatekeepers'])) {
            $photo_type = null;
            $id = null;
        }

        return view('shared.imagecrop', compact('photo_type', 'id'));
    }

    /* returns the last image uploaded by user */

    public function getLastImage()
    {
        return response()->json(['success' => 'done', 'filename' => session('last_image_upload')]);
    }

    /* Process cropped image for use by kOS */
    public function imageCropPost(Request $request)
    {

        // parse input from croppie
        $data = $request->image;

        [$type, $data] = explode(';', $data);
        [, $data] = explode(',', $data);

        $data = base64_decode($data);

        // create random filename and set path
        $image_name = md5(Str::uuid().microtime());

        $rec = null;
        if ($request->filled('photo_type')) {
            $photo_type = $request->input('photo_type');
            $path = public_path().'/storage/images/'.$photo_type.'/';

            if ($request->filled('id')) {
                // Update db records as needed
                switch ($photo_type) {
                    case 'users':

                        // If image is uploaded as part of the application, don't update the record
                        if ($request->input('id') != 'new') {
                            // make sure user has permission to modify the user photo
                            if ((\Gate::allows('manage-users')) || ($request->input('id') == \Auth::user()->id)) {
                                $rec = \App\Models\User::find($request->input('id'));
                            }
                        }
                        break;
                    case 'teams':
                        // make sure user has permission to modify the team photo
                        $team = \App\Models\Team::find($request->input('id'));
                        if ($team != null) {
                            if ((\Gate::allows('manage-teams')) || ($team->is_lead())) {
                                $rec = $team;
                            }
                        }
                        break;
                    case 'gatekeepers':
                        // make sure user has permission to modify the gatekeeper photo
                        $gatekeeper = \App\Models\Gatekeeper::find($request->input('id'));
                        if ($gatekeeper != null) {
                            $team = \App\Models\Team::find($gatekeeper->team_id);
                            if ($team != null) {
                                $has_team = true;
                            } else {
                                $has_team = false;
                            }
                            if ((\Gate::allows('manage-gatekeepers')) || (($has_team) && ($team->is_lead()))) {
                                $rec = $gatekeeper;
                            }
                        }
                        break;
                }
            }
        } else {
            $path = public_path().'/storage/images/';
        }

        $path_filename = $path.$image_name.'.png';

        // save file
        file_put_contents($path_filename, $data);

        // save jpg version of original and generate additional thumbnail sizes
        $img = Image::make($path_filename);
        $img->orientate();
        $img->save($path.$image_name.'.jpeg');
        $img->resize(512, 512);
        $img->save($path.$image_name.'-512px.jpeg');
        $img->resize(256, 256);
        $img->save($path.$image_name.'-256px.jpeg');
        $img->resize(128, 128);
        $img->save($path.$image_name.'-128px.jpeg');

        // update database with photo filename as needed
        if ($rec != null) {
            // delete previous image files to keep things tidy
            if ($rec->photo != null) {
                foreach (glob($path.$rec->photo.'*') as $f) {
                    unlink($f);
                }
            }
            $rec->photo = $image_name;
            $rec->save();
        }

        // set session variable with filename (used in new user form to retrieve filename)
        session(['last_image_upload' => $image_name]);

        // send success response
        return response()->json(['success' => 'done', 'filename' => $image_name]);
    }
}
