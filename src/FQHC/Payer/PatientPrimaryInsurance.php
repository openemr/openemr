<?php

/**
 * A patient's principal (primary) insurance as read from OpenEMR.
 *
 * Carries the display name and the OpenEMR insurance type code used to derive
 * the UDS payer category. Existence of this object means the patient has
 * coverage on file; its absence means none.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Payer;

final readonly class PatientPrimaryInsurance
{
    public function __construct(
        public ?string $planName,
        public ?int $insuranceTypeCode,
    ) {
    }
}
