<?php

/**
 * UDS Table 4 principal third-party medical insurance category.
 *
 * The fixed UDS payer buckets. Backed because it is persisted/reported and
 * exchanged with the UDS report. Matched exhaustively.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Payer;

enum UdsPayerCategory: string
{
    case None = 'none';
    case Medicaid = 'medicaid';
    case Medicare = 'medicare';
    case OtherPublic = 'other_public';
    case Private = 'private';

    public function label(): string
    {
        return match ($this) {
            self::None => 'None / uninsured',
            self::Medicaid => 'Medicaid',
            self::Medicare => 'Medicare',
            self::OtherPublic => 'Other public',
            self::Private => 'Private',
        };
    }
}
