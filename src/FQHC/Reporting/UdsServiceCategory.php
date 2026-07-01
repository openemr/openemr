<?php

/**
 * A UDS Table 5 service category (the utilization service lines).
 *
 * Each carries visits and patients on Table 5. A patient is counted once per
 * category but once in every category they were seen in, so these are the keys
 * the utilization aggregator dedups within. Backed because it is reported and
 * exchanged with the UDS submission. Matched exhaustively.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting;

enum UdsServiceCategory: string
{
    case Medical = 'medical';
    case Dental = 'dental';
    case MentalHealth = 'mental_health';
    case SubstanceUseDisorder = 'substance_use_disorder';
    case Vision = 'vision';
    case OtherProfessional = 'other_professional';
    case Enabling = 'enabling';

    public function label(): string
    {
        return match ($this) {
            self::Medical => 'Medical',
            self::Dental => 'Dental',
            self::MentalHealth => 'Mental health',
            self::SubstanceUseDisorder => 'Substance use disorder',
            self::Vision => 'Vision',
            self::OtherProfessional => 'Other professional',
            self::Enabling => 'Enabling services',
        };
    }
}
