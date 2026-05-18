<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Filesystem\Filesystem;

class UploadService{
    public function upload(UploadedFile $file, string $oldfile = null): string{

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $slugger = new AsciiSlugger();
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $fileSystem = new Filesystem();
        if($oldfile !== null && $oldfile !== 'imgs/default.jpg' && $fileSystem->exists("$oldfile")){
            $fileSystem->remove("$oldfile");
        }

        $file->move('imgs/photoPlats', $newFilename);
        return $newFilename;
    }
}