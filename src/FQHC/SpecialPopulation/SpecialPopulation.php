<?php

/**
 * A UDS Table 4 special population a patient may belong to.
 *
 * Each patient may hold several of these (counted if the status applied at any
 * point in the reporting year). Two of them carry a subtype value set
 * (agricultural worker and homeless); the rest have none. Backed because it is
 * persisted; matched exhaustively so a new population forces decisions here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\SpecialPopulation;

enum SpecialPopulation: string
{
    case AgriculturalWorker = 'agricultural_worker';
    case Homeless = 'homeless';
    case PublicHousing = 'public_housing';
    case Veteran = 'veteran';
    case SchoolBased = 'school_based';

    public function label(): string
    {
        return match ($this) {
            self::AgriculturalWorker => 'Agricultural worker',
            self::Homeless => 'Homeless',
            self::PublicHousing => 'Public housing resident',
            self::Veteran => 'Veteran',
            self::SchoolBased => 'School-based',
        };
    }

    /**
     * Allowed subtype codes mapped to their labels (empty when the population
     * has no subtype).
     *
     * @return array<string, string>
     */
    public function subtypeOptions(): array
    {
        return match ($this) {
            self::AgriculturalWorker => [
                AgriculturalWorkerType::Migratory->value => AgriculturalWorkerType::Migratory->label(),
                AgriculturalWorkerType::Seasonal->value => AgriculturalWorkerType::Seasonal->label(),
            ],
            self::Homeless => [
                HomelessStatus::Shelter->value => HomelessStatus::Shelter->label(),
                HomelessStatus::Transitional->value => HomelessStatus::Transitional->label(),
                HomelessStatus::Street->value => HomelessStatus::Street->label(),
                HomelessStatus::DoublingUp->value => HomelessStatus::DoublingUp->label(),
                HomelessStatus::PermanentSupportiveHousing->value => HomelessStatus::PermanentSupportiveHousing->label(),
                HomelessStatus::Other->value => HomelessStatus::Other->label(),
                HomelessStatus::Unknown->value => HomelessStatus::Unknown->label(),
            ],
            self::PublicHousing, self::Veteran, self::SchoolBased => [],
        };
    }
}
