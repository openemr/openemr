<?php

/**
 * The 277 claim status index in x12_claim_ack_277, x12_claim_status_277 and x12_claim_ref_277.
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

final class ClaimStatusIndexRepository implements ClaimStatusIndexStore
{
    private const ACK_COLUMNS = [
        'file_sha256', 'file_name', 'block_ordinal', 'interchange_control_number',
        'transaction_control_number', 'interchange_date', 'claim_reference', 'pid', 'encounter',
        'patient_last_name', 'patient_first_name', 'service_date_from', 'service_date_to',
        'source_entity_code', 'source_kind', 'source_name', 'source_id_qualifier', 'source_id',
        'source_received_date',
    ];

    private const STATUS_COLUMNS = [
        'position', 'category', 'code', 'entity_code', 'effective_date', 'action_code',
        'amount', 'status_text', 'service_line', 'rejected', 'accepted',
    ];

    public function isIndexed(string $fileSha256): bool
    {
        return QueryUtils::fetchSingleValue(
            'SELECT `id` FROM `x12_claim_ack_277` WHERE `file_sha256` = ? LIMIT 1',
            'id',
            [$fileSha256]
        ) !== null;
    }

    public function recordFile(array $claims): void
    {
        // A file is indexed whole or not at all, so a run cut short is tried again.
        QueryUtils::inTransaction(function () use ($claims): void {
            $at = date('Y-m-d H:i:s');
            foreach ($claims as $claim) {
                $ack = $claim['ack'];
                $ackId = QueryUtils::sqlInsert(
                    'INSERT INTO `x12_claim_ack_277` (`' . implode('`, `', self::ACK_COLUMNS)
                    . '`, `indexed_at`) VALUES (' . implode(', ', array_fill(0, count(self::ACK_COLUMNS) + 1, '?')) . ')',
                    [...array_map(static fn(string $c): mixed => $ack[$c], self::ACK_COLUMNS), $at]
                );
                foreach ($claim['statuses'] as $status) {
                    QueryUtils::sqlStatementThrowException(
                        'INSERT INTO `x12_claim_status_277` (`ack_id`, `' . implode('`, `', self::STATUS_COLUMNS)
                        . '`) VALUES (' . implode(', ', array_fill(0, count(self::STATUS_COLUMNS) + 1, '?')) . ')',
                        [$ackId, ...array_map(static fn(string $c): mixed => $status[$c], self::STATUS_COLUMNS)]
                    );
                }
                foreach ($claim['references'] as $ref) {
                    QueryUtils::sqlStatementThrowException(
                        'INSERT INTO `x12_claim_ref_277` (`ack_id`, `qualifier`, `ref_value`) VALUES (?, ?, ?)',
                        [$ackId, $ref['qualifier'], $ref['ref_value']]
                    );
                }
            }
        });
    }
}