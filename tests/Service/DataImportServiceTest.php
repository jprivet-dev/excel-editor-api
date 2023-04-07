<?php

namespace App\Tests\Service;

use App\Entity\Data;
use App\Entity\FileUpload;
use App\Repository\DataRepository;
use App\Service\DataImportService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
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
        $file = (new FileUpload(__DIR__))->setFilename('Fixtures/data.xlsx');

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

        $service = new DataImportService($dataRepository, $denormalizer, $validator);
        $service->import($file);

        $this->assertEquals($importedCount, $service->getStats()->getImportedCount());
        $this->assertEquals($alreadyExistCount, $service->getStats()->getAlreadyExistCount());
    }

    public function provideParams(): \Generator
    {
        // linesCount | importedCount | alreadyExistCount | onConsecutiveCalls
        yield 'Without already imported data' => [
            9,
            9,
            0,
            [[], [], [], [], [], [], [], [], []],
        ];
        yield 'With 2 already imported data' => [
            9,
            7,
            2,
            [[], [], [], [], [], [], [], [new Data()], [new Data()]],
        ];
        yield 'With only already imported data' => [
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

    /**
     * @throws ExceptionInterface
     */
    public function testImportWithException(): void
    {
        $this->expectException(ValidationFailedException::class);

        $file = (new FileUpload(__DIR__))->setFilename('Fixtures/data.xlsx');

        $dataRepository = $this->createMock(DataRepository::class);
        $denormalizer = $this->createMock(DenormalizerInterface::class);

        $violations = new ConstraintViolationList([
            $this->getViolation('Error'),
        ]);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $service = new DataImportService($dataRepository, $denormalizer, $validator);
        $service->import($file);

        $this->assertTrue(true);
    }

    protected function getViolation($message, $root = null, $propertyPath = null, $code = null): ConstraintViolation
    {
        return new ConstraintViolation($message, $message, [], $root, $propertyPath, null, null, $code);
    }
}
