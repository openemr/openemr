<?php

/**
 * The assembled UDS Patient Snapshot: the essential UDS fields for one patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Snapshot;

final readonly class UdsSnapshot
{
    /**
     * @param list<UdsField>      $demographics Reused demographics shown as data.
     * @param list<PendingSection> $pending     UDS sections awaiting their capture step.
     */
    public function __construct(
        public ?string $patientName,
        public array $demographics,
        public array $pending,
    ) {
    }
}
