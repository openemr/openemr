<?php

/**
 * OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    OpenEMR Project
 * @copyright Copyright (c) 2026 OpenEMR Project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PaymentProcessing;

use DateTimeImmutable;
use OpenEMR\Common\Utils\ValidationUtils;

final class PortalPaymentInput
{
    private const PAYMENT_TYPES = ['pre_payment', 'copay', 'invoice_balance', 'cash'];

    public static function normalizePaymentType(string $value): string
    {
        return in_array($value, self::PAYMENT_TYPES, true) ? $value : '';
    }

    /**
     * @param array<array-key, mixed> $payments
     * @return array<array-key, float>
     */
    public static function normalizePositivePayments(array $payments): array
    {
        $normalized = [];
        foreach ($payments as $encounterId => $payment) {
            $amount = ValidationUtils::parsePositiveAmount($payment);
            if ($amount !== null) {
                $normalized[$encounterId] = $amount;
            }
        }

        return $normalized;
    }

    /**
     * @param array<mixed> $row
     * @return array{string, string, string}
     */
    public static function normalizeBillingCodeRow(array $row): array
    {
        return [
            is_string($row['code_type'] ?? null) ? $row['code_type'] : '',
            is_string($row['code'] ?? null) ? $row['code'] : '',
            is_string($row['modifier'] ?? null) ? $row['modifier'] : '',
        ];
    }

    public static function normalizeReceiptTime(string $value): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $value) !== 1) {
            return '';
        }

        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        return $dateTime !== false && $dateTime->format('Y-m-d H:i:s') === $value ? $value : '';
    }

    /** @return array{bool, string} */
    public static function normalizeReceiptRequest(bool $requested, string $time): array
    {
        if (!$requested) {
            return [false, ''];
        }

        $normalizedTime = self::normalizeReceiptTime($time);
        return [$normalizedTime !== '', $normalizedTime];
    }
}
