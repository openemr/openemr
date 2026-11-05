<?php

/**
 * The amount of a portal card payment, read from the invoice selection the payment page posts.
 *
 * The gateway is asked to charge one amount (the page's total) while the payment is saved with
 * its invoice selection, which is what staff post. Comparing the two before charging keeps a
 * payment from charging one amount and posting another.
 *
 * Amounts are plain decimals with at most two places ("50", "50.5", "50.00"), as the payment
 * page allows; anything else is not an amount.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Portal;

final class PortalPaymentAmount
{
    private const AMOUNT = '/^(\d+)(?:\.(\d{1,2}))?$/';

    /**
     * Whether $payment is a positive amount equal to the total of the invoice selection.
     */
    public static function matchesInvoice(string $payment, string $invoiceValues): bool
    {
        $charged = self::cents($payment);
        if ($charged === null || $charged <= 0) {
            return false;
        }

        return $charged === self::invoiceCents($invoiceValues);
    }

    /** The payment types staff posting (portal_payment.php) records against invoice lines. */
    private const LINE_PAYMENT_TYPES = ['copay', 'invoice_balance', 'cash'];

    /**
     * The total of an invoice selection in cents, as staff posting would record it: the
     * prepayment for a prepayment, otherwise the sum of its invoice lines (form_upay). Null when
     * it is not a selection, its payment type is one posting does not record, or it holds an
     * amount that is not one.
     */
    public static function invoiceCents(string $invoiceValues): ?int
    {
        $values = json_decode($invoiceValues, true);
        if (!is_array($values)) {
            return null;
        }
        $type = $values['radio_type_of_payment'] ?? null;
        if ($type === 'pre_payment') {
            $prepayment = $values['form_prepayment'] ?? null;

            return is_string($prepayment) ? self::cents(trim($prepayment)) : null;
        }
        if (!in_array($type, self::LINE_PAYMENT_TYPES, true)) {
            return null;
        }

        $total = 0;
        foreach ($values as $name => $value) {
            if (!is_string($name) || !str_starts_with($name, 'form_upay[')) {
                continue;
            }
            if (!is_string($value)) {
                return null;
            }
            $value = trim($value);
            if ($value === '') {
                continue;
            }
            $cents = self::cents($value);
            if ($cents === null) {
                return null;
            }
            $total += $cents;
        }

        return $total;
    }

    /**
     * An amount in cents, or null when it is not a plain amount with at most two decimals.
     */
    public static function cents(string $amount): ?int
    {
        if (preg_match(self::AMOUNT, $amount, $parts) !== 1) {
            return null;
        }
        $fraction = str_pad($parts[2] ?? '', 2, '0');

        return intval($parts[1]) * 100 + intval($fraction);
    }
}
