<?php

/**
 * A UDS Table 3B Hispanic, Latino/a, or Spanish ethnicity column.
 *
 * The five Hispanic sub-columns (Mexican a1, Puerto Rican a2, Cuban a3, Another
 * a4, Combined a5) sum into the Total Hispanic column (a); the remaining columns
 * are Not Hispanic (b) and Unreported (c). `isHispanic()` drives the Total
 * Hispanic roll-up. Backed because it is reported. Matched exhaustively.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

enum UdsEthnicityCategory: string
{
    case Mexican = 'mexican';
    case PuertoRican = 'puerto_rican';
    case Cuban = 'cuban';
    case Another = 'another_hispanic';
    case Combined = 'hispanic_combined';
    case NotHispanic = 'not_hispanic';
    case Unreported = 'unreported';

    public function isHispanic(): bool
    {
        return match ($this) {
            self::Mexican, self::PuertoRican, self::Cuban, self::Another, self::Combined => true,
            self::NotHispanic, self::Unreported => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Mexican => 'Mexican, Mexican American, Chicano/a',
            self::PuertoRican => 'Puerto Rican',
            self::Cuban => 'Cuban',
            self::Another => 'Another Hispanic, Latino/a, or Spanish origin',
            self::Combined => 'Hispanic, Latino/a, or Spanish origin, combined',
            self::NotHispanic => 'Not Hispanic, Latino/a, or Spanish origin',
            self::Unreported => 'Unreported / Chose not to disclose ethnicity',
        };
    }
}
