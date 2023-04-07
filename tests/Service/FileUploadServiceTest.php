<?php

namespace App\Tests\Service;

use App\Repository\FileUploadRepository;
use App\Service\FileUploadService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class FileUploadServiceTest extends TestCase
{
    public function testSave(): void
    {
        $uploadsDirectory = '';

        $slugger = $this->createMock(SluggerInterface::class);
        $slugger
            ->expects($this->once())
            ->method('slug')
            ->willReturn(new UnicodeString('__filename__'));

        $repository = $this->createMock(FileUploadRepository::class);
        $uploadedFile = $this->createMock(UploadedFile::class);

        $service = new FileUploadService($uploadsDirectory, $slugger, $repository);
        $file = $service->save($uploadedFile);

        $this->stringStartsWith('__filename__', $file);
    }
}
