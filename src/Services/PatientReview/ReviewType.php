<?php

/**
 * What a patient submitted for staff review.
 *
 * Stored in patient_review_request.type, so the values are a persistence contract. The first
 * four are the workflows the patient portal has always had; Invoice is the one staff start
 * (billing asks the patient to pay online).
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

    /**
     * An invoice waits on the patient, not on staff, so it is never in the staff review queue.
     */
    public function initialStatus(): ReviewStatus
    {
        return match ($this) {
            self::Invoice => ReviewStatus::AwaitingPayment,
            self::Profile, self::Payment, self::Document => ReviewStatus::Pending,
        };
    }
}
