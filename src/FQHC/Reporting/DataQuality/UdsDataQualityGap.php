<?php

/**
 * A concrete UDS data-quality gap on a patient's reporting-year record.
 *
 * Each case corresponds to a place the report generator already silently
 * drops or miscounts a patient rather than reporting them accurately:
 * missing age/sex exclude a patient from Table 3A/4 (see report.html.twig's
 * reconciliation note), an unknown FPL band means income was never captured,
 * and an insurance code the payer classifier doesn't recognise is coerced to
 * None/Uninsured by Table4ReportBuilder. Backed because the value is used as
 * a stable grouping key when counting gaps. Matched exhaustively.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\DataQuality;

enum UdsDataQualityGap: string
{
    case MissingAge = 'missing_age';
    case MissingSex = 'missing_sex';
    case UnknownFplBand = 'unknown_fpl_band';
    case UnclassifiedInsurance = 'unclassified_insurance';

    public function label(): string
    {
        return match ($this) {
            self::MissingAge => 'Missing date of birth',
            self::MissingSex => 'Missing or unrecognized sex',
            self::UnknownFplBand => 'Income/FPL not on file',
            self::UnclassifiedInsurance => 'Insurance code not mapped to a UDS payer category',
        };
    }
}
