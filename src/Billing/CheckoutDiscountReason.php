<?php

/**
 * The adjustment reason recorded for a checkout discount.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

/**
 * Chooses the memo for the discount posted at checkout.
 *
 * The checkout discount is saved as an ar_activity adjustment, the same row
 * Fees > Posting Payments writes. That screen stores the chosen Adjustment
 * Reasons (`adjreason`) option_id as the memo; checkout always wrote
 * "Discount". A posted reason is accepted only if it is one of the reasons
 * the page offered, so the memo stays a list entry or the old default.
 */
final class CheckoutDiscountReason
{
    /**
     * The `adjreason` type (option_value) for a charge adjustment: one that
     * changes the invoice balance, as a discount does. The other types move
     * the balance to coinsurance, deductible or patient responsibility, or
     * are comments.
     */
    public const CHARGE_ADJUSTMENT = 1;

    /**
     * The stock `adjreason` entry for the memo checkout has always written.
     * The page offers it as its default choice, so it is not listed again.
     */
    public const STOCK_DISCOUNT = 'Discount';

    /**
     * The reasons to offer besides the default.
     *
     * @param iterable<array<mixed>> $rows `adjreason` rows with option_id and title
     * @return list<array{id: string, title: string}>
     */
    public static function choices(iterable $rows): array
    {
        $choices = [];
        foreach ($rows as $row) {
            $id = $row['option_id'] ?? null;
            $title = $row['title'] ?? null;
            if (!is_string($id) || $id === '' || $id === self::STOCK_DISCOUNT || !is_string($title)) {
                continue;
            }
            $choices[] = ['id' => $id, 'title' => $title];
        }
        return $choices;
    }

    /**
     * The memo to record: the posted reason if it was offered, otherwise the
     * default.
     *
     * @param list<array{id: string, title: string}> $choices
     */
    public static function memo(mixed $posted, array $choices, string $default): string
    {
        if (!is_string($posted) || $posted === '') {
            return $default;
        }
        return in_array($posted, array_column($choices, 'id'), true) ? $posted : $default;
    }
}
