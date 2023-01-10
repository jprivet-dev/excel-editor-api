<?php

declare(strict_types=1);

namespace App\Util;

enum DataColumns: string
{
    case NOM_DU_GROUPE = 'Nom du groupe';
    case ORIGINE = 'Origine';
    case VILLE = 'Ville';
    case ANNEE_DEBUT = 'Année début';
    case ANNEE_SEPARATION = 'Année séparation';
    case FONDATEURS = 'Fondateurs';
    case MEMBRES = 'Membres';
    case COURANT_MUSICAL = 'Courant musical';
    case PRESENTATION = 'Présentation';
}
