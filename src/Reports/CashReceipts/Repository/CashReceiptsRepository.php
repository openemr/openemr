<?php

/**
 * CashReceiptsRepository - Database access for cash receipts report
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\CashReceipts\Repository;

use OpenEMR\Common\Database\QueryUtils;

/**
 * Repository for fetching cash receipt data from the database
 */
class CashReceiptsRepository
{
    /**
     * Get copay receipts based on filters
     *
     * @param array $filters Filter parameters
     * @return array Array of copay records
     */
    public function getCopayReceipts(array $filters): array
    {
        $sql = "SELECT 
                b.fee, 
                b.pid, 
                b.encounter, 
                b.code_type, 
                b.code, 
                b.modifier,
                fe.date, 
                fe.id AS trans_id, 
                fe.provider_id AS docid, 
                fe.invoice_refno,
                CONCAT(p.lname, ' ', p.fname) AS patient_name
            FROM billing AS b
            JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter
            LEFT OUTER JOIN patient_data AS p ON p.pid = b.pid
            WHERE b.code_type = 'COPAY' 
                AND b.activity = 1 
                AND fe.date >= ? 
                AND fe.date <= ?";

        $binds = [
            $filters['from_date'] . ' 00:00:00',
            $filters['to_date'] . ' 23:59:59'
        ];

        // Add facility filter
        if (!empty($filters['facility_id'])) {
            $sql .= " AND fe.facility_id = ?";
            $binds[] = $filters['facility_id'];
        }

        // Add provider filter
        if (!empty($filters['provider_id'])) {
            $sql .= " AND fe.provider_id = ?";
            $binds[] = $filters['provider_id'];
        }

        $sql .= " ORDER BY fe.provider_id, fe.date, b.pid, b.encounter";

        return QueryUtils::fetchRecords($sql, $binds);
    }

    /**
     * Get AR activity receipts based on filters
     *
     * @param array $filters Filter parameters
     * @return array Array of AR activity records
     */
    public function getArActivityReceipts(array $filters): array
    {
        $sql = "SELECT 
                a.pid, 
                a.encounter, 
                a.post_time, 
                a.code, 
                a.modifier, 
                a.pay_amount,
                a.code_type AS ar_code_type,
                fe.date, 
                fe.id AS trans_id, 
                fe.provider_id AS docid, 
                fe.invoice_refno, 
                s.deposit_date, 
                s.payer_id,
                b.provider_id,
                CONCAT(p.lname, ' ', p.fname) AS patient_name
            FROM ar_activity AS a
            JOIN form_encounter AS fe ON fe.pid = a.pid AND fe.encounter = a.encounter
            LEFT OUTER JOIN ar_session AS s ON s.session_id = a.session_id
            LEFT OUTER JOIN patient_data AS p ON p.pid = a.pid
            LEFT OUTER JOIN billing AS b ON b.pid = a.pid 
                AND b.encounter = a.encounter 
                AND b.code COLLATE utf8mb4_general_ci = a.code COLLATE utf8mb4_general_ci
                AND COALESCE(b.modifier, '') COLLATE utf8mb4_general_ci = COALESCE(a.modifier, '') COLLATE utf8mb4_general_ci
                AND b.activity = 1
                AND b.code_type != 'COPAY' 
                AND b.code_type != 'TAX'
            WHERE a.deleted IS NULL 
                AND a.pay_amount != 0 
                AND (
                    a.post_time >= ? AND a.post_time <= ?
                    OR fe.date >= ? AND fe.date <= ?
                    OR s.deposit_date >= ? AND s.deposit_date <= ?
                )";

        $binds = [
            $filters['from_date'] . ' 00:00:00',
            $filters['to_date'] . ' 23:59:59',
            $filters['from_date'] . ' 00:00:00',
            $filters['to_date'] . ' 23:59:59',
            $filters['from_date'],
            $filters['to_date']
        ];

        // Add procedure code filter
        if (!empty($filters['procedure_code']) && !empty($filters['procedure_code_type'])) {
            $sql .= " AND (a.code_type = ? OR a.code_type = '') AND a.code = ?";
            $binds[] = $filters['procedure_code_type'];
            $binds[] = $filters['procedure_code'];
        }

        // Add facility filter
        if (!empty($filters['facility_id'])) {
            $sql .= " AND fe.facility_id = ?";
            $binds[] = $filters['facility_id'];
        }

        // Add provider filter
        if (!empty($filters['provider_id'])) {
            $sql .= " AND (b.provider_id = ? OR 
                    ((b.provider_id IS NULL OR b.provider_id = 0) AND fe.provider_id = ?))";
            $binds[] = $filters['provider_id'];
            $binds[] = $filters['provider_id'];
        }

        $sql .= " ORDER BY COALESCE(b.provider_id, fe.provider_id), fe.date, a.pid, a.encounter";

        return QueryUtils::fetchRecords($sql, $binds);
    }

    /**
     * Check if invoice has specific diagnosis code
     *
     * @param int $patientId
     * @param int $encounterId
     * @param string $diagnosisCodeType
     * @param string $diagnosisCode
     * @return bool
     */
    public function hasDiagnosisCode(
        int $patientId,
        int $encounterId,
        string $diagnosisCodeType,
        string $diagnosisCode
    ): bool {
        $sql = "SELECT COUNT(*) AS count 
                FROM billing 
                WHERE pid = ? 
                    AND encounter = ? 
                    AND code_type = ? 
                    AND code LIKE ? 
                    AND activity = 1";

        $result = QueryUtils::querySingleRow($sql, [
            $patientId,
            $encounterId,
            $diagnosisCodeType,
            $diagnosisCode
        ]);

        return !empty($result['count']);
    }

    /**
     * Get invoice amount for specific procedure
     *
     * @param int $patientId
     * @param int $encounterId
     * @param string $procedureCodeType
     * @param string $procedureCode
     * @return float
     */
    public function getInvoiceAmount(
        int $patientId,
        int $encounterId,
        string $procedureCodeType,
        string $procedureCode
    ): float {
        $sql = "SELECT SUM(fee) AS sum 
                FROM billing 
                WHERE pid = ? 
                    AND encounter = ? 
                    AND code_type = ? 
                    AND code = ? 
                    AND activity = 1";

        $result = QueryUtils::querySingleRow($sql, [
            $patientId,
            $encounterId,
            $procedureCodeType,
            $procedureCode
        ]);

        return (float)($result['sum'] ?? 0.0);
    }

    /**
     * Get insurance company name by ID
     *
     * @param int $insuranceId
     * @return string
     */
    public function getInsuranceCompanyName(int $insuranceId): string
    {
        if ($insuranceId === 0) {
            return '';
        }

        $sql = "SELECT name FROM insurance_companies WHERE id = ?";
        $result = QueryUtils::querySingleRow($sql, [$insuranceId]);

        return $result['name'] ?? '';
    }

    /**
     * Get provider name by ID
     *
     * @param int $providerId
     * @return string
     */
    public function getProviderName(int $providerId): string
    {
        $sql = "SELECT fname, lname FROM users WHERE id = ?";
        $result = QueryUtils::querySingleRow($sql, [$providerId]);

        if (empty($result)) {
            return 'Unknown';
        }

        return trim(($result['fname'] ?? '') . ' ' . ($result['lname'] ?? ''));
    }

    /**
     * Get all authorized providers
     *
     * @return array Array of provider records [id, fname, lname]
     */
    public function getAuthorizedProviders(): array
    {
        $sql = "SELECT id, lname, fname 
                FROM users 
                WHERE authorized = 1 
                ORDER BY lname, fname";

        return QueryUtils::fetchRecords($sql);
    }
}
