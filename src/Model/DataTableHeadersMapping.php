<?php

declare(strict_types=1);

namespace App\Model;

final class DataTableHeadersMapping
{
    private array $mapping = [
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

    public function getExpectedHeaders(): array
    {
        return array_keys($this->mapping);
    }

    public function getCamelCaseHeaders(): array
    {
        return array_values($this->mapping);
    }
}
