<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

trait ImageTrait
{


    public function uploadImage($file, $filename, $folder, $oldfile = null)
    {

        $image = str_replace('data:image/png;base64,', '', $file);
        $image = str_replace(' ', '+', $image);
        $image = base64_decode($image);

        $directory = public_path('images/' . $folder);
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true); 
        }

        $path = $directory . '/' . $filename;
        file_put_contents($path, $image);


        //$file->move(public_path('images/'.$folder),$filename);
        if (!is_null($oldfile)) {
            if (file_exists($oldfile)) {
                unlink($oldfile);
            }
        }

        return $filename;
    }

    protected function uploadSliderImage(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $folderPath = 'images/categories';
            $file->move(public_path($folderPath), $fileName);
            $imageLink = url($folderPath . '/' . $fileName);

        return $imageLink;
    }
}
