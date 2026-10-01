<?php

/**
 * Postal notice shown after a facility is saved.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

final class FacilityPostalNotice
{
    /**
     * Save-dialog text for a billing facility. The row is already stored.
     */
    public const FACILITY_SAVED_BILLING_POSTAL = 'This billing facility was saved. The postal code is not 9 digits, '
        . 'so a claim billed from this facility can be rejected.';

    /**
     * Save-dialog text for a service facility. The row is already stored.
     */
    public const FACILITY_SAVED_SERVICE_POSTAL = 'This service facility was saved. The postal code is not 9 digits, '
        . 'so a claim that uses this service location can be rejected.';

    /**
     * Save-dialog text when the facility is both a billing and a service location.
     */
    public const FACILITY_SAVED_BOTH_POSTAL = 'This facility was saved as a billing and service location. '
        . 'The postal code is not 9 digits, so a claim that uses it can be rejected.';

    /**
     * Postal notice for the facility screen. Empty when the dialog should stay quiet.
     *
     * Digits are kept the same way Claim::x12Zip() keeps them. Nine digits stay quiet.
     */
    public static function forPostalCode(string $postal, bool $billingLocation, bool $serviceLocation): string
    {
        if (!$billingLocation && !$serviceLocation) {
            return '';
        }
        $digits = preg_replace('/[^0-9]/', '', $postal) ?? '';
        if (strlen($digits) === 9) {
            return '';
        }
        if ($billingLocation && $serviceLocation) {
            return self::FACILITY_SAVED_BOTH_POSTAL;
        }
        if ($billingLocation) {
            return self::FACILITY_SAVED_BILLING_POSTAL;
        }

        return self::FACILITY_SAVED_SERVICE_POSTAL;
    }
}
