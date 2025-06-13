<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private string $targetDirectory;

    public function __construct(string $targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file): string
    {
        $originalName = $file->getClientOriginalName();
        $safeName = uniqid() . '__' . $originalName;

        $file->move($this->targetDirectory, $safeName);

        return '/uploads/files/' . $safeName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
