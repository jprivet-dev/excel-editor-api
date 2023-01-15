<?php

namespace App\Service;

use App\Entity\Data;
use App\Entity\FileUpload;
use App\Model\ExcelHeadersModel;
use App\Repository\DataRepository;
use App\Validator\ExcelHeaders;
use App\Validator\ExcelHeadersValidator;
use Spatie\SimpleExcel\SimpleExcelReader;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class DataImportService
{
    private ExcelHeadersModel $headers;

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

    private array $headersOriginal;
    private array $headersCamelCase;

    private array $stats = [
        'alreadyExistsCount' => 0,
    ];

    public function __construct(
        readonly string $uploadsDirectory,
        readonly DataRepository $dataRepository,
        readonly DenormalizerInterface $denormalizer,
        readonly ExcelHeadersValidator $validator,
    ) {
        $this->headersOriginal = array_keys($this->headersMapping);
        $this->headersCamelCase = array_values($this->headersMapping);

        $this->headers = new ExcelHeadersModel($this->headersOriginal);
    }

    public function import(FileUpload $file): array
    {
        $this->stats['alreadyExistsCount'] = 0;
        $path = sprintf('%s/%s', $this->uploadsDirectory, $file->getFilename());
        $reader = SimpleExcelReader::create($path);

        $reader->useHeaders($this->headersCamelCase);
        // BUG with the cache: $reader->getHeaders() & $reader->getOriginalHeaders()
        // can only be called once before validateOriginalHeaders().
        $this->headers->setHeaders($reader->getOriginalHeaders());
        $this->validator->validate($this->headers, new ExcelHeaders());

        $reader->getRows()->each(function (array $row) {
            $result = $this->dataRepository->findBy([
                'nomDuGroupe' => $row['nomDuGroupe'],
            ]);

            if (count($result) > 0) {
                $this->stats['alreadyExistsCount']++;

                return;
            }

            $this->add($row);
        });

        return $this->stats;
    }

    private function add(array $row): void
    {
        /** @var Data $data */
        $data = $this->denormalizer->denormalize($row, Data::class);

        $this->dataRepository->add($data, true);
    }
}
