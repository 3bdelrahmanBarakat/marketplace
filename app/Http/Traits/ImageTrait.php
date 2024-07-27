<?php

namespace App\Http\Traits;

trait ImageTrait{


    public function uploadImage($file, $filename, $folder, $oldfile = null)
    {

        $image = str_replace('data:image/png;base64,', '', $file);
        $image = str_replace(' ', '+', $image);
        $image = base64_decode($image);
        $folder = public_path('images/' . $folder . '/' . $filename);
        file_put_contents($folder, $image);


        //$file->move(public_path('images/'.$folder),$filename);
        if (!is_null($oldfile)) {
            if (file_exists($oldfile)) {
                unlink($oldfile);
            }
        }

        return $filename;
    }
}
