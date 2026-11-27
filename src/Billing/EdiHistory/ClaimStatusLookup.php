<?php

/**
 * Reads the indexed claim statuses for the EDI History claim-status table.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EdiHistory;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;

/**
 * The index is an addition to the claim-status table, not a replacement: a site without
 * the tables, or a query that fails, still gets the CSV columns it had before (#15530 step 2).
 */
final class ClaimStatusLookup
{
    /**
     * @param list<array<string, mixed>> $csvRows rows of claims_f277.csv, keyed by column name
     * @return array<string, array{source: string, received: string, result: string, payer_numbers: string}>
     */
    public static function forCsvRows(array $csvRows): array
    {
        $files = [];
        $references = [];
        foreach ($csvRows as $row) {
            $fileName = (string) ($row['FileName'] ?? '');
            // A 277CA carries the reference in CLM01; a response to a 276 carries it in Trace.
            $reference = (string) ($row['Trace'] ?? '');
            if ($reference === '') {
                $reference = (string) ($row['CLM01'] ?? '');
            }
            if ($fileName === '' || $reference === '') {
                continue;
            }
            $files[$fileName] = true;
            $references[$reference] = true;
        }
        if ($files === [] || $references === []) {
            return [];
        }

        try {
            if (!QueryUtils::existsTable('x12_claim_ack_277')) {
                return [];
            }
            $fileNames = array_keys($files);
            $claimReferences = array_keys($references);
            $fileMarks = implode(', ', array_fill(0, count($fileNames), '?'));
            $refMarks = implode(', ', array_fill(0, count($claimReferences), '?'));
            $where = ' WHERE a.file_name IN (' . $fileMarks . ') AND a.claim_reference IN (' . $refMarks . ')';
            $binds = [...$fileNames, ...$claimReferences];

            $statusRows = QueryUtils::fetchRecords(
                'SELECT a.file_name, a.claim_reference, a.source_name, a.source_kind,'
                . ' a.source_received_date, s.accepted, s.rejected'
                . ' FROM `x12_claim_ack_277` a'
                . ' LEFT JOIN `x12_claim_status_277` s ON s.ack_id = a.id' . $where,
                $binds
            );
            $refRows = QueryUtils::fetchRecords(
                'SELECT a.file_name, a.claim_reference, r.ref_value'
                . ' FROM `x12_claim_ack_277` a'
                . ' JOIN `x12_claim_ref_277` r ON r.ack_id = a.id'
                . ' WHERE r.qualifier = ? AND a.file_name IN (' . $fileMarks . ')'
                . ' AND a.claim_reference IN (' . $refMarks . ')',
                ['1K', ...$binds]
            );
        } catch (SqlQueryException) {
            return [];
        }

        return ClaimStatusSummary::byClaim($statusRows, $refRows);
    }
}