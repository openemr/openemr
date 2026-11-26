<?php

/**
 * The claim numbers and the first payer receipt a filing history shows.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

/**
 * Every payer claim number the files show, and the earliest date a payer (not a
 * clearinghouse) said it received the claim (#15530 step 4). Nothing is inferred: a
 * source that is not a payer does not count as a payer receipt, and an unreal date
 * is not a date.
 */
final class ClaimFilingFacts
{
    /**
     * @param list<array<string, mixed>> $refRows    indexed REF*1K rows (ref_value)
     * @param list<array<string, mixed>> $remitRows  claims_f835.csv rows (ClaimID)
     * @param list<array<string, mixed>> $postedRows ar_activity rows (payer_claim_number)
     * @return list<string> each number once, in the order the sources give them
     */
    public static function payerClaimNumbers(array $refRows, array $remitRows, array $postedRows): array
    {
        $numbers = [];
        $sources = [
            [$refRows, 'ref_value'],
            [$remitRows, 'ClaimID'],
            [$postedRows, 'payer_claim_number'],
        ];
        foreach ($sources as [$rows, $column]) {
            foreach ($rows as $row) {
                $value = trim((string) ($row[$column] ?? ''));
                if ($value !== '' && !in_array($value, $numbers, true)) {
                    $numbers[] = $value;
                }
            }
        }
        return $numbers;
    }

    /**
     * @param list<array<string, mixed>> $ackRows indexed acknowledgments
     * @return string the earliest payer received date, or '' when no payer gave one
     */
    public static function firstPayerReceipt(array $ackRows): string
    {
        $first = '';
        foreach ($ackRows as $row) {
            if ((string) ($row['source_kind'] ?? '') !== 'Payer') {
                continue;
            }
            $date = trim((string) ($row['source_received_date'] ?? ''));
            if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m) !== 1) {
                continue;
            }
            if (!checkdate((int) $m[2], (int) $m[3], (int) $m[1])) {
                continue;
            }
            if ($first === '' || strcmp($date, $first) < 0) {
                $first = $date;
            }
        }
        return $first;
    }
}