<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Image;

class RegenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:thumbnails {--type= : Image types to process (valid: all, users, teams, gatekeepers)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerates image thumbnails of database-linked photos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $image_type = $this->option('type');

        switch($image_type) {
            case 'users':
                $image_types = ['users'];
            break;
            case 'teams':
                $image_types = ['teams'];
            break;
            case 'gatekeepers':
                $image_types = ['gatekeepers'];
            break;
            case 'all':
                $image_types = ['users','teams','gatekeepers'];
            break;
            default:
                $this->error('No valid image type specified. Valid: all, users, teams, gatekeepers');
                return false;
        }

        $this->info('Regenerating image thumbnails');

        foreach($image_types as $image_type) {

            // grab the records we want to process
            switch ($image_type) {
                case 'users':
                    $recs = \App\User::where('photo','!=',NULL)->get();
                    break;
                case 'teams':
                    $recs = \App\Team::where('photo','!=',NULL)->get();
                    break;
                case 'gatekeepers':
                    $recs = \App\Gatekeeper::where('photo','!=',NULL)->get();
                    break;
            }
            $this->comment('Processing images for ' . $image_type);

            if (count($recs)>0) {
                $image_path = public_path() . "/storage/images/" . $image_type . '/';
                foreach ($recs as $rec) {

                    // older images have file extension in db photo field so strip it off and update the record
                    if (strpos($rec->photo,'.')>0) {
                        $rec->photo = substr($rec->photo, 0, strrpos($rec->photo, "."));
                        $rec->save();
                    }

                    $image_file = NULL;
                    // look for original file in the order of preference
                    if (file_exists($image_path . $rec->photo . '.png')) {
                        $image_file = $rec->photo . '.png';
                        $image_ext = 'png';
                    } else if (file_exists($image_path . $rec->photo . '.jpeg')) {
                        $image_file = $rec->photo . '.jpeg';
                        $image_ext = 'jpeg';
                    }

                    if ($image_file != NULL) {

                        $this->info('Processing ' . $image_file);
                        // generate thumbnails
                        $img = Image::make($image_path . $image_file)->orientate();
                        // if we started with a PNG file, create a JPEG version
                        if ($image_ext == 'png') {
                            $img->save($image_path . $rec->photo . '.jpeg');
                        }

                        // if image is not 1:1 aspect ratio, fill it out before resizing
                        if ($img->width() != $img->height()) {
                            if ($img->width() > $img->height()) {
                                $img->resizeCanvas(null, $img->width(), 'center', false, 'ffffff');
                            } else {
                                $img->resizeCanvas($img->height(), null, 'center', false, 'ffffff');
                            }
                        }

                        $img->resize(512, 512);
                        $img->save($image_path . $rec->photo . '-512px.jpeg');

                        $img->resize(256, 256);
                        $img->save($image_path . $rec->photo . '-256px.jpeg');

                        $img->resize(128, 128);
                        $img->save($image_path . $rec->photo . '-128px.jpeg');

                    } else {
                        $this->error('Could not find image - ' . $rec->photo);
                    }
                }
            } else {
                $this->info('No images for this type to process.');
            }

        }

        $this->info('Thumbnail generation complete.');

    }
}
