<?php

/**
 * The data-quality gaps found on one patient's reporting-year record.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Reporting\DataQuality;

final readonly class PatientDataQualityIssues
{
    /**
     * @param non-empty-list<UdsDataQualityGap> $gaps
     */
    public function __construct(
        public int $pid,
        public array $gaps,
    ) {
    }
}
