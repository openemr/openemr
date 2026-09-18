<?php

/**
 * Query service for open prepayment balances.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (C) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;

/**
 * Finds pre-payment sessions whose received amount has not been fully applied.
 *
 * "Unapplied" is ar_session.pay_total minus the sum of live
 * (deleted IS NULL) ar_activity.pay_amount rows for the session, and
 * deliberately does not subtract ar_session.global_amount. Money swept to the
 * Global Account has not satisfied a charge, so it remains an unapplied credit;
 * it is reported separately as inGlobal so parked credit stays visible.
 */
class PrepaymentBalanceService extends BaseService
{
    public const TABLE_NAME = 'ar_session';

    /**
     * Amounts below this are rounding residue, not a real balance.
     */
    private const UNAPPLIED_EPSILON = 0.005;

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    /**
     * @param string|null $fromDate Y-m-d lower bound on check_date, null for none
     * @param string|null $toDate Y-m-d upper bound on check_date, null for none
     * @param int|null $patientId Limit to one patient, null for all
     * @param bool $parkedOnly Only sessions with a non-zero global_amount
     * @return list<PrepaymentBalance> Ordered by unapplied amount, largest first
     */
    public function getOpenBalances(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $patientId = null,
        bool $parkedOnly = false,
    ): array {
        // Applied money is pre-aggregated per (session, pid) so that a session
        // with several distributions does not fan out and multiply pay_total.
        //
        // The unapplied threshold is applied in WHERE rather than HAVING: the
        // outer query has no GROUP BY, so a bare HAVING would depend on a MySQL
        // extension to standard SQL.
        $sql = "SELECT s.session_id, s.patient_id, s.check_date, s.reference,
                       s.pay_total, s.global_amount,
                       COALESCE(act.applied, 0) AS applied,
                       s.pay_total - COALESCE(act.applied, 0) AS unapplied,
                       DATEDIFF(CURDATE(), s.check_date) AS days_open,
                       p.lname, p.fname, p.mname
                  FROM ar_session AS s
                  LEFT JOIN (
                       SELECT session_id, pid, SUM(pay_amount) AS applied
                         FROM ar_activity
                        WHERE deleted IS NULL
                        GROUP BY session_id, pid
                       ) AS act
                    ON act.session_id = s.session_id AND act.pid = s.patient_id
                  LEFT JOIN patient_data AS p ON p.pid = s.patient_id
                 WHERE s.adjustment_code = 'pre_payment'
                   AND s.closed = 0
                   AND (s.pay_total - COALESCE(act.applied, 0)) > ?";

        $binds = [self::UNAPPLIED_EPSILON];

        if ($fromDate !== null && $fromDate !== '') {
            $sql .= " AND s.check_date >= ?";
            $binds[] = $fromDate;
        }
        if ($toDate !== null && $toDate !== '') {
            $sql .= " AND s.check_date <= ?";
            $binds[] = $toDate;
        }
        if ($patientId !== null) {
            $sql .= " AND s.patient_id = ?";
            $binds[] = $patientId;
        }
        if ($parkedOnly) {
            $sql .= " AND s.global_amount != 0";
        }

        $sql .= " ORDER BY unapplied DESC, s.check_date ASC";

        $balances = [];
        foreach (QueryUtils::fetchRecords($sql, $binds) as $row) {
            $balances[] = PrepaymentBalance::fromRow($row);
        }

        return $balances;
    }
}
