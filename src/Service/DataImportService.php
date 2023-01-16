<?php

namespace App\Service;

use App\Entity\Data;
use App\Entity\FileUpload;
use App\Model\DataImportStats;
use App\Model\DataTableHeadersMapping;
use App\Repository\DataRepository;
use App\Validator\DataTableHeaders;
use Spatie\SimpleExcel\SimpleExcelReader;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DataImportService
{
    private DataTableHeadersMapping $headersMapping;

    private DataImportStats $stats;

    public function __construct(
        readonly string $uploadsDirectory,
        readonly DataRepository $dataRepository,
        readonly DenormalizerInterface $denormalizer,
        readonly ValidatorInterface $validator,
    ) {
        $this->headersMapping = new DataTableHeadersMapping();
        $this->stats = new DataImportStats();
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function import(FileUpload $file): void
    {
        $this->stats->setFile($file);
        $reader = $this->createReader($file);

        // BUG with the cache: $reader->getHeaders() & $reader->getOriginalHeaders()
        // can only be called once before validate the headers.
        $headers = $reader->getOriginalHeaders();

        $violations = $this->validator->validate(
            $headers,
            new DataTableHeaders($this->headersMapping->getExpectedHeaders())
        );

        if (\count($violations)) {
            throw new ValidationFailedException($headers, $violations);
        }

        $reader->getRows()->each(function (array $row) {
            $result = $this->dataRepository->findBy([
                'nomDuGroupe' => $row['nomDuGroupe'],
            ]);

            if (\count($result) > 0) {
                $this->stats->addAlreadyExist($row['nomDuGroupe']);

                return;
            }

            $this->stats->addImported($row['nomDuGroupe']);

            $this->add($row);
        });
    }

    public function getStats(): DataImportStats
    {
        return $this->stats;
    }

    private function createReader(FileUpload $file): SimpleExcelReader
    {
        $path = sprintf('%s/%s', $this->uploadsDirectory, $file->getFilename());
        $reader = new SimpleExcelReader($path);
        $reader->useHeaders($this->headersMapping->getCamelCaseHeaders());

        return $reader;
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    private function add(array $row): void
    {
        /** @var Data $data */
        $data = $this->denormalizer->denormalize($row, Data::class);

        $this->dataRepository->add($data, true);
    }
}
