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

final class PortalPaymentInput
{
    private const PAYMENT_TYPES = ['pre_payment', 'copay', 'invoice_balance', 'cash'];

    public static function normalizePaymentType(string $value): string
    {
        return in_array($value, self::PAYMENT_TYPES, true) ? $value : '';
    }

    public static function normalizeReceiptTime(string $value): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $value) !== 1) {
            return '';
        }

        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        return $dateTime !== false && $dateTime->format('Y-m-d H:i:s') === $value ? $value : '';
    }
}
