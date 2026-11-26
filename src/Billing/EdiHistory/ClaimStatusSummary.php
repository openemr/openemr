<?php

/**
 * What the claim-status table shows from the index, beside the CSV columns.
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
 * Gathers the indexed statuses of each claim into one line for the table (#15530 step 2).
 * Nothing is inferred: a claim whose statuses include both an acceptance and a rejection
 * shows both, and a source with no name shows nothing.
 */
final class ClaimStatusSummary
{
    /**
     * @param list<array<string, mixed>> $statusRows the ack rows joined to their statuses
     * @param list<array<string, mixed>> $refRows    the ack rows joined to their REF*1K values
     * @return array<string, array{source: string, received: string, result: string, payer_numbers: string}>
     *               keyed by file name and claim reference
     */
    public static function byClaim(array $statusRows, array $refRows): array
    {
        $claims = [];
        $accepted = [];
        $rejected = [];
        $numbers = [];

        foreach ($statusRows as $row) {
            $key = self::key((string) ($row['file_name'] ?? ''), (string) ($row['claim_reference'] ?? ''));
            if (!isset($claims[$key])) {
                $name = (string) ($row['source_name'] ?? '');
                $kind = (string) ($row['source_kind'] ?? '');
                $claims[$key] = [
                    'source' => $name === '' ? '' : $name . ' (' . $kind . ')',
                    'received' => (string) ($row['source_received_date'] ?? ''),
                ];
            }
            if (!empty($row['accepted'])) {
                $accepted[$key] = true;
            }
            if (!empty($row['rejected'])) {
                $rejected[$key] = true;
            }
        }

        foreach ($refRows as $row) {
            $key = self::key((string) ($row['file_name'] ?? ''), (string) ($row['claim_reference'] ?? ''));
            if (!isset($claims[$key])) {
                continue;
            }
            $value = (string) ($row['ref_value'] ?? '');
            if ($value !== '' && !in_array($value, $numbers[$key] ?? [], true)) {
                $numbers[$key][] = $value;
            }
        }

        $out = [];
        foreach ($claims as $key => $claim) {
            $parts = [];
            if (isset($accepted[$key])) {
                $parts[] = 'Accepted';
            }
            if (isset($rejected[$key])) {
                $parts[] = 'Rejected';
            }
            $out[$key] = [
                'source' => $claim['source'],
                'received' => $claim['received'],
                'result' => implode(', ', $parts),
                'payer_numbers' => implode(', ', $numbers[$key] ?? []),
            ];
        }
        return $out;
    }

    /**
     * The key the table looks a claim up by: its file and its own reference.
     */
    public static function key(string $fileName, string $claimReference): string
    {
        return $fileName . "\0" . $claimReference;
    }
}