<?php

namespace App\Service;

use App\Entity\Data;
use App\Entity\FileUpload;
use App\Repository\DataRepository;
use Spatie\SimpleExcel\SimpleExcelReader;

class DataImportService
{
    public const NOM_DU_GROUPE = 'Nom du groupe';
    public const ORIGINE = 'Origine';
    public const VILLE = 'Ville';
    public const ANNEE_DEBUT = 'Année début';
    public const ANNEE_SEPARATION = 'Année séparation';
    public const FONDATEURS = 'Fondateurs';
    public const MEMBRES = 'Membres';
    public const COURANT_MUSICAL = 'Courant musical';
    public const PRESENTATION = 'Présentation';

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
                'nomDuGroupe' => $row[self::NOM_DU_GROUPE],
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
        $data->setNomDuGroupe($this->string($row[self::NOM_DU_GROUPE]));
        $data->setOrigine($this->string($row[self::ORIGINE]));
        $data->setVille($this->string($row[self::VILLE]));
        $data->setAnneeDebut($this->yearToDateTime($row[self::ANNEE_DEBUT]));
        $data->setAnneeSeparation($this->yearToDateTime($row[self::ANNEE_SEPARATION]));
        $data->setFondateurs($this->string($row[self::FONDATEURS]));
        $data->setMembres($this->integer($row[self::MEMBRES]));
        $data->setCourantMusical($this->string($row[self::COURANT_MUSICAL]));
        $data->setPresentation($this->string($row[self::PRESENTATION]));

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

    private function yearToDateTime(?string $year): ?\DateTimeImmutable
    {
        $year = trim($year);

        return $year ? \DateTimeImmutable::createFromFormat('Y', $year) : null;
    }
}
