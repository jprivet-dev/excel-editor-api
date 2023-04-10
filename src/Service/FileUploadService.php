<?php

namespace App\Service;

use App\Entity\FileUpload;
use App\Repository\FileUploadRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploadService
{
    public function __construct(
        private readonly string $uploadsDirectory,
        private SluggerInterface $slugger,
        private FileUploadRepository $repository,
    ) {
    }

    public function save(UploadedFile $uploadedFile): FileUpload
    {
        $filename = $this->upload($uploadedFile);
        $file = new FileUpload($this->uploadsDirectory);
        $file->setFilename($filename);
        $this->repository->add($file, true);

        return $file;
    }

    private function upload(UploadedFile $uploadedFile): string
    {
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
        $uploadedFile->move($this->uploadsDirectory, $fileName);

        return $fileName;
    }
}
