<?php


namespace App\Services;



class DocumentService{

    public function showDocument($filename)
{
    $path = storage_path($filename);

    if (!file_exists($path)) {
        $path==null;
    }

    return $path;
}

}
