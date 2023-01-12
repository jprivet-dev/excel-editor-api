<?php

namespace App\Service;

use App\Entity\Data;
use App\Entity\FileUpload;
use App\Repository\DataRepository;
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

    private $stats = [
        'alreadyExistsCount' => 0,
    ];

    public function __construct(
        readonly string $uploadsDirectory,
        private DataRepository $dataRepository,
        private DenormalizerInterface $denormalizer
    ) {
    }

    public function import(FileUpload $file): array
    {
        $this->stats['alreadyExistsCount'] = 0;
        $path = sprintf('%s/%s', $this->uploadsDirectory, $file->getFilename());
        $reader = SimpleExcelReader::create($path);

        $reader->useHeaders(array_values($this->headersMapping));
        // BUG: $reader->getHeaders() & $reader->getOriginalHeaders() can only
        // be called once before validateOriginalHeaders().
        $this->validateOriginalHeaders($reader->getOriginalHeaders());

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

    private function validateOriginalHeaders(array $headers): void
    {
        $expectedHeaders = array_keys($this->headersMapping);
        $notAllowed = array_diff($headers, $expectedHeaders);
        $notAllowedCount = count($notAllowed);

        if ($notAllowedCount) {
            $message = $notAllowedCount > 1
                ? 'The columns [%s] in the imported file are not allowed.'
                : 'The column "%s" in the imported file is not allowed.';

            throw new \Exception(sprintf($message, implode(', ', $notAllowed)));
        }

        $mandatory = array_diff($expectedHeaders, $headers);
        $mandatoryCount = count($mandatory);

        if ($mandatoryCount) {
            $message = $mandatoryCount > 1
                ? 'The mandatory columns [%s] are not present in the imported file.'
                : 'The mandatory column "%s" is not present in the imported file.';

            throw new \Exception(sprintf($message, implode(', ', $mandatory)));
        }

        $ordered = array_diff_assoc($headers, $expectedHeaders);

        if (count($ordered)) {
            throw new \Exception(
                sprintf(
                    'The columns [%s] of the imported file are not in the right order.',
                    implode(', ', $ordered)
                )
            );
        }
    }

    private function add(array $row): void
    {
        /** @var Data $data */
        $data = $this->denormalizer->denormalize($row, Data::class);

        $this->dataRepository->add($data, true);
    }
}
