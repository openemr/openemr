<?php

/**
 * Agricultural worker subtype for UDS Table 4.
 *
 * UDS distinguishes migratory from seasonal agricultural workers. Backed
 * because it is persisted.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\SpecialPopulation;

enum AgriculturalWorkerType: string
{
    case Migratory = 'migratory';
    case Seasonal = 'seasonal';

    public function label(): string
    {
        return match ($this) {
            self::Migratory => 'Migratory',
            self::Seasonal => 'Seasonal',
        };
    }
}
