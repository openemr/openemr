<?php

/**
 * Encodes and decodes payment distribution identifiers.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @copyright Copyright (c) 2026 OpenEMR Community
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Billing;

final class PaymentDistributionIdentifier
{
    /**
     * Encodes the fields that identify a payment distribution.
     */
    public static function encode(
        string $paymentId,
        string $patientId,
        string $encounter,
        string $code,
        string $modifier,
        string $codeType
    ): string {
        return implode('/', array_map('rawurlencode', [$paymentId, $patientId, $encounter, $code, $modifier, $codeType]));
    }

    /**
     * Decodes the fields from a payment distribution identifier.
     *
     * @return list<string>
     */
    public static function decode(string $identifier): array
    {
        return array_map('rawurldecode', explode('/', $identifier));
    }
}
