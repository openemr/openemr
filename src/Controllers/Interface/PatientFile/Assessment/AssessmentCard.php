<?php

/**
 * One questionnaire's card on the Available Assessments tab.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientFile\Assessment;

final readonly class AssessmentCard
{
    /**
     * @param string $summary the description, else the code's display text
     * @param bool $continuable the latest response is in progress and opens instead of a new one
     * @param list<SmartApp> $smartApps apps it may be launched into
     * @param ?int $smartResponseId the in-progress response a SMART launch continues
     */
    public function __construct(
        public int $questionnaireId,
        public string $name,
        public string $summary,
        public string $version,
        public bool $sdc,
        public string $latestStatus,
        public string $lastAssessed,
        public bool $continuable,
        public string $launchUrl,
        public array $smartApps,
        public ?int $smartResponseId,
    ) {
    }

    /**
     * The Bootstrap badge class for the latest response's status.
     */
    public function statusClass(): string
    {
        return AssessmentValues::statusClass($this->latestStatus);
    }
}
