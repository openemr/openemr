<?php

/**
 * Reads what was posted for an encounter, for the claim filing history.
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
 * Active postings only: a voided ar_activity row is not what was posted (#15530 step 3).
 * With the pid the primary key is used; without it the encounter alone still answers.
 * A query that fails gives nothing, so the rest of the history still renders.
 */
final class EncounterPostedLookup
{
    /**
     * @return list<array<string, mixed>> rows for EncounterHistorySources::posted()
     */
    public static function forEncounter(int $encounter, int $pid = 0): array
    {
        if ($encounter <= 0) {
            return [];
        }
        $where = 'a.encounter = ?';
        $binds = [$encounter];
        if ($pid > 0) {
            $where = 'a.pid = ? AND a.encounter = ?';
            $binds = [$pid, $encounter];
        }
        try {
            return QueryUtils::fetchRecords(
                'SELECT a.code, a.payer_type, a.post_time, a.post_date, a.pay_amount,'
                . ' a.adj_amount, a.memo, a.reason_code, a.payer_claim_number, s.reference'
                . ' FROM `ar_activity` a'
                . ' LEFT JOIN `ar_session` s ON s.session_id = a.session_id'
                . ' WHERE ' . $where . ' AND a.deleted IS NULL'
                . ' ORDER BY a.sequence_no',
                $binds
            );
        } catch (SqlQueryException) {
            return [];
        }
    }
}