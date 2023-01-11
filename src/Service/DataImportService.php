<?php

namespace App\Service;

use App\Entity\Data;
use App\Entity\FileUpload;
use App\Repository\DataRepository;
use App\Util\StringUtil;
use Spatie\SimpleExcel\SimpleExcelReader;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class DataImportService
{
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
        $rows = SimpleExcelReader::create($path)
            ->formatHeadersUsing(fn($header) => StringUtil::camelCase($header))
            ->getRows();

        $rows->each(function (array $row) {
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
