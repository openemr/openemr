<?php

/**
 * Homeless housing status for UDS Table 4.
 *
 * The UDS homeless categories. Backed because it is persisted.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\SpecialPopulation;

enum HomelessStatus: string
{
    case Shelter = 'shelter';
    case Transitional = 'transitional';
    case Street = 'street';
    case DoublingUp = 'doubling_up';
    case PermanentSupportiveHousing = 'permanent_supportive_housing';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Shelter => 'Sheltered',
            self::Transitional => 'Transitional',
            self::Street => 'Street / unsheltered',
            self::DoublingUp => 'Doubling up',
            self::PermanentSupportiveHousing => 'Permanent supportive housing',
            self::Other => 'Other',
        };
    }
}
