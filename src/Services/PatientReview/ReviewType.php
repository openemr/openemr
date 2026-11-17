<?php

/**
 * What a patient submitted for staff review.
 *
 * Stored in patient_review_request.type, so the values are a persistence contract. The first
 * four are the workflows the patient portal has always had; Invoice is the one staff start
 * (billing asks the patient to pay online). FhirWrite is a patient-app FHIR create or update held
 * for clinician review; the resource type it writes is carried separately (FhirResourceType), so
 * this enum does not grow a case per FHIR resource.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

enum ReviewType: string
{
    case Profile = 'profile';
    case Payment = 'payment';
    case Document = 'document';
    case Invoice = 'invoice';
    case FhirWrite = 'fhir-write';

    /**
     * An invoice waits on the patient, not on staff, so it is never in the staff review queue.
     */
    public function initialStatus(): ReviewStatus
    {
        return match ($this) {
            self::Invoice => ReviewStatus::AwaitingPayment,
            self::Profile, self::Payment, self::Document, self::FhirWrite => ReviewStatus::Pending,
        };
    }

    /**
     * Whether "one open request" is counted per target row rather than per patient. A patient
     * can have several portal documents in review at once, one per document; a profile change
     * or a payment is one per patient.
     */
    public function isKeyedByTarget(): bool
    {
        return match ($this) {
            self::Document => true,
            self::Profile, self::Payment, self::Invoice, self::FhirWrite => false,
        };
    }

    /**
     * Whether a request of this type names the FHIR resource type it writes.
     */
    public function requiresResourceType(): bool
    {
        return match ($this) {
            self::FhirWrite => true,
            self::Profile, self::Payment, self::Document, self::Invoice => false,
        };
    }
}
