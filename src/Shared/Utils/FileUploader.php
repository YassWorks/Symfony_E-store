<?php

namespace App\Shared\Utils;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private SluggerInterface $slugger,
        private string $uploadDirectory
    ) {
    }

    public function uploadFile(UploadedFile $file): array
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
        
        try {
            $file->move($this->uploadDirectory, $newFilename);
            return [
                'success' => true,
                'filename' => $newFilename
            ];
        } catch (FileException) {
            return [
                'success' => false,
                'error' => 'There was a problem uploading your file. Please try again.'
            ];
        }
    }
}
