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
    /**
     * Expected columns in the file to be imported.
     */
    private $headersMapping = [
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

    private ExcelHeadersModel $headers;

    private $stats = [
        'alreadyExistsCount' => 0,
    ];

    public function __construct(
        readonly string $uploadsDirectory,
        private DataRepository $dataRepository,
        private DenormalizerInterface $denormalizer,
        private ExcelHeadersValidator $validator,
    ) {
        $expectedColumns = array_values($this->headersMapping);
        $this->headers->setColumns($expectedColumns);
    }

    public function import(FileUpload $file): array
    {
        $this->stats['alreadyExistsCount'] = 0;
        $path = sprintf('%s/%s', $this->uploadsDirectory, $file->getFilename());
        $reader = SimpleExcelReader::create($path);

        $reader->useHeaders(array_values($this->headersMapping));
        // BUG with the cache: $reader->getHeaders() & $reader->getOriginalHeaders()
        // can only be called once before validateOriginalHeaders().
        $this->validator->validate($reader->getOriginalHeaders(), new ExcelHeaders());

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
