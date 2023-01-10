<?php

namespace App\Service;

use App\Entity\Data;
use App\Entity\FileUpload;
use App\Repository\DataRepository;
use App\Util\DataColumns;
use App\Util\DateTimeUtil;
use Spatie\SimpleExcel\SimpleExcelReader;

class DataImportService
{
    private $stats = [
        'alreadyExistsCount' => 0,
    ];

    public function __construct(
        readonly string $uploadsDirectory,
        private DataRepository $dataRepository
    ) {
    }

    public function import(FileUpload $file): array
    {
        $path = sprintf('%s/%s', $this->uploadsDirectory, $file->getFilename());
        $rows = SimpleExcelReader::create($path)->getRows();
        $this->stats['alreadyExistsCount'] = 0;

        $rows->each(function (array $row) {
            $result = $this->dataRepository->findBy([
                'nomDuGroupe' => $row[DataColumns::NOM_DU_GROUPE->value],
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
        $data = new Data();

        // TODO: utiliser plutôt le serializer pour le formatage des données.
        $data->setNomDuGroupe($this->string($row[DataColumns::NOM_DU_GROUPE->value]));
        $data->setOrigine($this->string($row[DataColumns::ORIGINE->value]));
        $data->setVille($this->string($row[DataColumns::VILLE->value]));
        $data->setAnneeDebut(DateTimeUtil::yearToDateTime($row[DataColumns::ANNEE_DEBUT->value]));
        $data->setAnneeSeparation(DateTimeUtil::yearToDateTime($row[DataColumns::ANNEE_SEPARATION->value]));
        $data->setFondateurs($this->string($row[DataColumns::FONDATEURS->value]));
        $data->setMembres($this->integer($row[DataColumns::MEMBRES->value]));
        $data->setCourantMusical($this->string($row[DataColumns::COURANT_MUSICAL->value]));
        $data->setPresentation($this->string($row[DataColumns::PRESENTATION->value]));

        $this->dataRepository->add($data, true);
    }

    private function string(?string $value): ?string
    {
        $value = htmlentities($value, null, 'utf-8');
        $value = str_replace("&nbsp;", ' ', $value);
        $value = html_entity_decode($value);

        return trim($value) ?? null;
    }

    private function integer(?string $value): ?int
    {
        return (int)trim($value) ?? null;
    }
}
