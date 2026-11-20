<?php

/**
 * Who a patient statement is addressed to, and where: the guardian (guarantor) when one
 * is on the patient's demographics, otherwise the patient.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\Statement;

final class StatementRecipient
{
    /**
     * The statement's "to" block: a name, then address lines. A guardian's name is used
     * when set; their address too when it has a street and a city, else the patient's, so a
     * partial guardian address never makes an unmailable statement.
     *
     * @param array<mixed> $patient a patient_data row: names, address and guardian columns
     * @return list<string>
     */
    public static function lines(array $patient): array
    {
        $get = static fn (string $column): string => is_string($patient[$column] ?? null) ? trim($patient[$column]) : '';

        $guardian = $get('guardiansname');
        if ($guardian !== '' && $get('guardianaddress') !== '' && $get('guardiancity') !== '') {
            return self::block($guardian, [$get('guardianaddress')], $get('guardiancity'), $get('guardianstate'), $get('guardianpostalcode'));
        }

        return self::block(
            $guardian !== '' ? $guardian : trim($get('fname') . ' ' . $get('lname')),
            [$get('street'), $get('street_line_2')],
            $get('city'),
            $get('state'),
            $get('postal_code')
        );
    }

    /**
     * @param list<string> $streetLines blank ones are left out
     * @return list<string>
     */
    private static function block(string $name, array $streetLines, string $city, string $state, string $postalCode): array
    {
        return [
            $name,
            ...array_values(array_filter($streetLines, static fn (string $line): bool => $line !== '')),
            $city . ', ' . $state . ' ' . $postalCode,
        ];
    }
}
