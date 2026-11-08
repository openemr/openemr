<?php

/**
 * Spots payment card details in a review request payload.
 *
 * The payload is stored as readable JSON, so card details must never be in it; they go through
 * ReviewQueueService::attachSecret(). This is a guard against a caller getting that wrong, not a
 * substitute for it: it catches card numbers and the usual field names, and cannot recognise
 * every way card data could be spelled.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview;

final class PaymentCardDetector
{
    /** Field names that hold card data whatever their value looks like. */
    private const CARD_FIELD = '/^(card[_-]?(number|num|no)|cc|cc[_-]?(number|num|no)|pan|cvv2?|cvc2?|cid|security[_-]?code)$/i';

    /**
     * @param array<array-key, mixed> $payload
     */
    public function containsCardData(array $payload): bool
    {
        foreach ($payload as $key => $value) {
            if (is_string($key) && preg_match(self::CARD_FIELD, $key) === 1 && $value !== null && $value !== '' && $value !== []) {
                return true;
            }
            if (is_array($value)) {
                if ($this->containsCardData($value)) {
                    return true;
                }
            } elseif ((is_string($value) || is_int($value)) && $this->isCardNumber((string) $value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 13 to 19 digits, allowing the spaces and dashes people type, that pass the Luhn check.
     */
    public function isCardNumber(string $value): bool
    {
        if (preg_match('/^\d(?:[ -]?\d){12,18}$/', trim($value)) !== 1) {
            return false;
        }
        $digits = preg_replace('/\D/', '', $value) ?? '';
        $sum = 0;
        $double = false;
        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $digit = intval($digits[$i]);
            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
            $double = !$double;
        }
        return $sum % 10 === 0;
    }
}
