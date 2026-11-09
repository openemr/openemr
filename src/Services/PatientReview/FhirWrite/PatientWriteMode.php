<?php

/**
 * Which patient apps a site allows to submit data through the FHIR API.
 *
 * Stored in the fhir_patient_write_mode global, so the values are a persistence contract.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\FhirWrite;

enum PatientWriteMode: string
{
    case Off = 'off';
    case Confidential = 'confidential';
    case ConfidentialAndPublic = 'confidential_public';

    /**
     * Whether this mode lets the given kind of client submit writes. Off refuses every client.
     */
    public function allowsClient(PatientWriteClientType $client): bool
    {
        return match ($this) {
            self::Off => false,
            self::Confidential => $client === PatientWriteClientType::Confidential,
            self::ConfidentialAndPublic => true,
        };
    }
}
