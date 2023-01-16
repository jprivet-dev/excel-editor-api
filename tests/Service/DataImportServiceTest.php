<?php

namespace App\Tests\Service;

use App\Entity\Data;
use App\Entity\FileUpload;
use App\Repository\DataRepository;
use App\Service\DataImportService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DataImportServiceTest extends TestCase
{
    public function testValidFile(): void
    {
        $linesCount = 9;
        $uploadsDirectory = __DIR__;
        $file = $this->createValidFile();

        $dataRepository = $this->createMock(DataRepository::class);
        $dataRepository
            ->expects($this->exactly($linesCount))
            ->method('findBy')
            ->willReturn([]);

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer
            ->expects($this->exactly($linesCount))
            ->method('denormalize')
            ->willReturn(new Data());

        $violationList = new ConstraintViolationList([]);
        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violationList);

        $service = new DataImportService($uploadsDirectory, $dataRepository, $denormalizer, $validator);
        $service->import($file);

        $stats = $service->getStats();
        $this->assertEquals($linesCount, $stats->getImportedCount());
        $this->assertEquals(0, $stats->getAlreadyExistCount());
    }

    private function createValidFile(): FileUpload
    {
        return (new FileUpload())->setFilename('Fixtures/data.xlsx');;
    }

}
