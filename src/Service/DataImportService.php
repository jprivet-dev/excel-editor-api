<?php

namespace App\Service;

use App\Entity\Data;
use App\Entity\FileUpload;
use App\Repository\DataRepository;
use App\Validator\DataTableHeaders;
use Spatie\SimpleExcel\SimpleExcelReader;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DataImportService
{
    /**
     * Expected headers in the file to be imported.
     */
    private array $headersMapping = [
        'Nom du groupe' => 'nomDuGroupe',
        'Origine' => 'origine',
        'Ville' => 'ville',
        'Année début' => 'anneeDebut',
        'Année séparation' => 'anneeSeparation',
        'Fondateurs' => 'fondateurs',
        'Membres' => 'membres',
        'Courant musical' => 'courantMusical',
        'Présentation' => 'presentation',
    ];

    private array $stats = [
        'alreadyExistsCount' => 0,
    ];

    public function __construct(
        readonly string $uploadsDirectory,
        readonly DataRepository $dataRepository,
        readonly DenormalizerInterface $denormalizer,
        readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function import(FileUpload $file): array
    {
        $this->stats['alreadyExistsCount'] = 0;
        $reader = $this->createReader($file);

        // BUG with the cache: $reader->getHeaders() & $reader->getOriginalHeaders()
        // can only be called once before validate the headers.
        $expectedheaders = array_keys($this->headersMapping);
        $headers = $reader->getOriginalHeaders();

        $violations = $this->validator->validate($headers, new DataTableHeaders($expectedheaders));
        if (\count($violations)) {
            throw new ValidationFailedException($headers, $violations);
        }

        $reader->getRows()->each(function (array $row) {
            $result = $this->dataRepository->findBy([
                'nomDuGroupe' => $row['nomDuGroupe'],
            ]);

            if (\count($result) > 0) {
                $this->stats['alreadyExistsCount']++;

                return;
            }

            $this->add($row);
        });

        return $this->stats;
    }

    private function createReader(FileUpload $file): SimpleExcelReader
    {
        $path = sprintf('%s/%s', $this->uploadsDirectory, $file->getFilename());
        $reader = new SimpleExcelReader($path);
        $reader->useHeaders(array_values($this->headersMapping)); // Headers in camel case

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
