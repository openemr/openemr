<?php

/**
 * Resolved, display-ready patient demographics that feed UDS Tables 3A/3B/4.
 *
 * A value object: every field is already resolved to a human-readable label
 * (coded list values translated) or null when absent. The rest of the snapshot
 * code works with this guaranteed-valid shape rather than a raw database row.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Snapshot;

final readonly class PatientDemographics
{
    public function __construct(
        public ?string $fullName,
        public ?string $ageDisplay,
        public ?string $sex,
        public ?string $race,
        public ?string $ethnicity,
        public ?string $language,
        public ?string $zip,
    ) {
    }
}
