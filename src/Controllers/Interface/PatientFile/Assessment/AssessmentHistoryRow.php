<?php

/**
 * One saved response on the Assessment History tab.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Interface\PatientFile\Assessment;

final readonly class AssessmentHistoryRow
{
    /**
     * @param string $editUrl empty when the row has no response id to open
     * @param list<SmartApp> $smartApps apps it may be launched into; offered only with both ids
     */
    public function __construct(
        public string $name,
        public string $status,
        public string $lastUpdated,
        public string $version,
        public string $editUrl,
        public ?int $questionnaireId,
        public ?int $responseRecordId,
        public array $smartApps,
    ) {
    }

    /**
     * The Bootstrap badge class for the row's status.
     */
    public function statusClass(): string
    {
        return AssessmentValues::statusClass($this->status);
    }

    /**
     * A SMART app is offered only for a row linked to both its questionnaire and its response.
     */
    public function canLaunchSmart(): bool
    {
        return $this->smartApps !== [] && $this->questionnaireId !== null && $this->responseRecordId !== null;
    }
}
