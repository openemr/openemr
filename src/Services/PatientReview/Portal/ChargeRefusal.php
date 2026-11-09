<?php

/**
 * Why a portal card charge was refused before the gateway was asked to charge anything.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Portal;

enum ChargeRefusal
{
    /** The request names no valid patient. */
    case NoPatient;

    /** Another payment for the patient holds the lock. */
    case PaymentInProgress;

    /** A payment the gateway already charged is still waiting to be posted. */
    case ChargedPaymentOpen;

    /**
     * What the patient is told, translated.
     */
    public function message(): string
    {
        return match ($this) {
            self::NoPatient => xl('The payment could not be started. Reload the page and try again.'),
            self::PaymentInProgress => xl('Another payment for this account is in progress. Reload the page and try again.'),
            self::ChargedPaymentOpen => xl('A card payment for this account is already waiting to be posted. Reload the page.'),
        };
    }
}
