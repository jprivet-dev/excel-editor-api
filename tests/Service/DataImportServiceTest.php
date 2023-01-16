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
    /**
     * @dataProvider provideParams
     */
    public function testImport(
        int $linesCount,
        int $importedCount,
        int $alreadyExistCount,
        array $onConsecutiveCalls
    ): void {
        $uploadsDirectory = __DIR__;
        $file = (new FileUpload())->setFilename('Fixtures/data.xlsx');

        $dataRepository = $this->createMock(DataRepository::class);
        $dataRepository
            ->expects($this->exactly($linesCount))
            ->method('findBy')
            ->willReturnOnConsecutiveCalls(...$onConsecutiveCalls);

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer
            ->expects($this->exactly($importedCount))
            ->method('denormalize')
            ->willReturn(new Data());

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([]));

        $service = new DataImportService($uploadsDirectory, $dataRepository, $denormalizer, $validator);
        $service->import($file);

        $this->assertEquals($importedCount, $service->getStats()->getImportedCount());
        $this->assertEquals($alreadyExistCount, $service->getStats()->getAlreadyExistCount());
    }

    public function provideParams()
    {
        // linesCount | importedCount | alreadyExistCount | onConsecutiveCalls
        yield 'Without allready imported data' => [
            9,
            9,
            0,
            [[], [], [], [], [], [], [], [], []],
        ];
        yield 'With 2 allready imported data' => [
            9,
            7,
            2,
            [[], [], [], [], [], [], [], [new Data()], [new Data()]],
        ];
        yield 'With only allready imported data' => [
            9,
            0,
            9,
            [
                [new Data()],
                [new Data()],
                [new Data()],
                [new Data()],
                [new Data()],
                [new Data()],
                [new Data()],
                [new Data()],
                [new Data()],
            ],
        ];
    }
}
