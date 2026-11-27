<?php

/**
 * What staff chose when approving a patient app write.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

use OpenEMR\Services\PatientReview\ReviewApprovalOptions;

final readonly class PatientWriteApprovalOptions implements ReviewApprovalOptions
{
    /**
     * @param ?int $encounter the patient's encounter (form_encounter.encounter) to file a
     *        questionnaire response under when the response names none; null to file it under
     *        none. A response that names its own encounter is always filed under that one.
     */
    public function __construct(
        public ?int $encounter = null,
    ) {
    }
}
