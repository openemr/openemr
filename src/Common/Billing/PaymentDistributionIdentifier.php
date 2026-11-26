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
        return implode('/', array_map(rawurlencode(...), [$paymentId, $patientId, $encounter, $code, $modifier, $codeType]));
    }

    /**
     * Encodes values read from the legacy database result rows.
     */
    public static function encodeDatabaseValues(
        mixed $paymentId,
        mixed $patientId,
        mixed $encounter,
        mixed $code,
        mixed $modifier,
        mixed $codeType
    ): string {
        return self::encode(
            self::normalizeDatabaseValue($paymentId),
            self::normalizeDatabaseValue($patientId),
            self::normalizeDatabaseValue($encounter),
            self::normalizeDatabaseValue($code),
            self::normalizeDatabaseValue($modifier),
            self::normalizeDatabaseValue($codeType)
        );
    }

    private static function normalizeDatabaseValue(mixed $value): string
    {
        if (is_int($value) || is_string($value)) {
            return (string) $value;
        }

        if ($value === null) {
            return '';
        }

        throw new \UnexpectedValueException('Payment distribution fields must be strings, integers, or null');
    }

    /**
     * Decodes the fields from a payment distribution identifier.
     *
     * @return list<string>
     */
    public static function decode(string $identifier): array
    {
        return array_map(rawurldecode(...), explode('/', $identifier));
    }
}
