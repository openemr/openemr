<?php

/**
 * Resolve the local HL7 EI.1 placer-order formats emitted by OpenEMR.
 *
 * @package OpenEMR
 * @copyright Copyright (c) 2026
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Orders;

final class Hl7PlacerOrderId
{
    /**
     * A compound identifier is accepted only for the configured sending facility;
     * HL7 does not generally define a dash as a local order-id separator.
     * Returns 0 for invalid, zero or out-of-range identifiers.
     */
    public static function parse(string $value, string $componentDelimiter, string $sendFacilityId = ''): int
    {
        if (strlen($componentDelimiter) !== 1) {
            return 0;
        }
        $entityId = explode($componentDelimiter, $value, 2)[0];
        $digits = $entityId;
        if (!ctype_digit($digits)) {
            $prefix = $sendFacilityId . '-';
            if ($sendFacilityId === '' || !str_starts_with($entityId, $prefix)) {
                return 0;
            }
            $digits = substr($entityId, strlen($prefix));
            if (!ctype_digit($digits)) {
                return 0;
            }
        }
        $digits = ltrim($digits, '0');
        $limit = (string) PHP_INT_MAX;
        if (
            $digits === '' || strlen($digits) > strlen($limit) ||
            (strlen($digits) === strlen($limit) && strcmp($digits, $limit) > 0)
        ) {
            return 0;
        }
        return (int) $digits;
    }
}
