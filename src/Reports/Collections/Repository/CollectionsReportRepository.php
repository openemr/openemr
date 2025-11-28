<?php

/**
 * Collections Report Repository
 * Built with Warp Terminal
 * Handles all database queries for the Collections Report.
 * Separates data access logic from controller and business logic.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\Collections\Repository;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Billing\InvoiceSummary;
use OpenEMR\Billing\SLEOB;

class CollectionsReportRepository
{
    /**
     * Fetch invoice data based on filters
     *
     * @param array $filters Filter parameters from form submission
     * @return array Array of invoice records with encounter and patient data
     */
    public function fetchInvoiceData(array $filters): array
    {
        // Build WHERE clause and bind parameters
        $where = '';
        $sqlArray = [];

        // Handle export/CSV selection filters
        if (!empty($filters['form_export']) || !empty($filters['form_csvexport'])) {
            $where = '( 1 = 2';
            if (!empty($filters['form_cb'])) {
                foreach ($filters['form_cb'] as $key => $value) {
                    [$pid, $encounter] = explode('.', $key);

                    if (($filters['form_individual'] ?? '') == 1) {
                        $where .= ' OR f.encounter = ? ';
                        $sqlArray[] = $encounter;
                    } else {
                        $where .= ' OR f.pid = ? ';
                        $sqlArray[] = $pid;
                    }
                }
            }
            $where .= ' )';
        }

        // Date range filter
        if (!empty($filters['form_date'])) {
            if ($where) {
                $where .= ' AND ';
            }

            if (!empty($filters['form_to_date'])) {
                $where .= 'f.date >= ? AND f.date <= ? ';
                $sqlArray[] = $filters['form_date'] . ' 00:00:00';
                $sqlArray[] = $filters['form_to_date'] . ' 23:59:59';
            } else {
                $where .= 'f.date >= ? AND f.date <= ? ';
                $sqlArray[] = $filters['form_date'] . ' 00:00:00';
                $sqlArray[] = $filters['form_date'] . ' 23:59:59';
            }
        }

        // Facility filter
        if (!empty($filters['form_facility'])) {
            if ($where) {
                $where .= ' AND ';
            }
            $where .= 'f.facility_id = ? ';
            $sqlArray[] = $filters['form_facility'];
        }

        // Provider filter
        if (!empty($filters['form_provider'])) {
            if ($where) {
                $where .= ' AND ';
            }
            $where .= 'f.provider_id = ? ';
            $sqlArray[] = $filters['form_provider'];
        }

        // Default WHERE clause if none specified
        if (!$where) {
            $where = '1 = 1';
        }

        // Build main query
        $query = "SELECT f.id, f.date, f.pid, CONCAT(w.lname, ', ', w.fname) AS provider_id, f.encounter, f.last_level_billed, " .
            "f.last_level_closed, f.last_stmt_date, f.stmt_count, f.invoice_refno, f.in_collection, " .
            "p.fname, p.mname, p.lname, p.street, p.city, p.state, " .
            "p.postal_code, p.phone_home, p.ss, p.billing_note, " .
            "p.pubpid, p.DOB, CONCAT(u.lname, ', ', u.fname) AS referrer, " .
            "( SELECT bill_date FROM billing AS b WHERE " .
            "b.pid = f.pid AND b.encounter = f.encounter AND " .
            "b.activity = 1 AND b.code_type != 'COPAY' LIMIT 1) AS bill_date, " .
            "( SELECT SUM(b.fee) FROM billing AS b WHERE " .
            "b.pid = f.pid AND b.encounter = f.encounter AND " .
            "b.activity = 1 AND b.code_type != 'COPAY' ) AS charges, " .
            "( SELECT SUM(b.fee) FROM billing AS b WHERE " .
            "b.pid = f.pid AND b.encounter = f.encounter AND " .
            "b.activity = 1 AND b.code_type = 'COPAY' ) AS copays, " .
            "( SELECT SUM(s.fee) FROM drug_sales AS s WHERE " .
            "s.pid = f.pid AND s.encounter = f.encounter ) AS sales, " .
            "( SELECT SUM(a.pay_amount) FROM ar_activity AS a WHERE " .
            "a.pid = f.pid AND a.encounter = f.encounter AND a.deleted IS NULL) AS payments, " .
            "( SELECT SUM(a.adj_amount) FROM ar_activity AS a WHERE " .
            "a.pid = f.pid AND a.encounter = f.encounter AND a.deleted IS NULL) AS adjustments " .
            "FROM form_encounter AS f " .
            "JOIN patient_data AS p ON p.pid = f.pid " .
            "LEFT OUTER JOIN users AS u ON u.id = p.ref_providerID " .
            "LEFT OUTER JOIN users AS w ON w.id = f.provider_id " .
            "WHERE $where " .
            "ORDER BY f.pid, f.encounter";

        // Execute query using modern QueryUtils
        $records = QueryUtils::fetchRecords($query, $sqlArray);

        // Process each record to add computed fields
        $processedRecords = [];
        foreach ($records as $record) {
            $processedRecord = $this->processInvoiceRecord($record, $filters);
            if ($processedRecord !== null) {
                $processedRecords[] = $processedRecord;
            }
        }

        return $processedRecords;
    }

    /**
     * Process individual invoice record with business logic
     *
     * @param array $record Raw database record
     * @param array $filters Filter parameters
     * @return array|null Processed record or null if filtered out
     */
    private function processInvoiceRecord(array $record, array $filters): ?array
    {
        $patient_id = $record['pid'];
        $encounter_id = $record['encounter'];
        $pt_balance = $record['charges'] + $record['sales'] + $record['copays'] - $record['payments'] - $record['adjustments'];
        $pt_balance = 0 + sprintf("%.2f", $pt_balance);
        $svcdate = substr($record['date'], 0, 10);

        // Apply balance filters
        if (!empty($filters['form_cb_with_debt']) && $pt_balance <= 0) {
            return null;
        }

        if (!empty($filters['form_refresh']) && empty($filters['is_all'])) {
            if ($pt_balance == 0) {
                return null;
            }
        }

        if (($filters['form_category'] ?? '') == 'Credits') {
            if ($pt_balance > 0) {
                return null;
            }
        }

        // Compute insurance and duncount information
        $last_level_closed = $record['last_level_closed'];
        $duncount = $record['stmt_count'];
        $payerids = [];
        $insposition = 0;
        $insname = '';

        if (!$duncount) {
            for ($i = 1; $i <= 3; ++$i) {
                $tmp = SLEOB::arGetPayerID($patient_id, $svcdate, $i);
                if (empty($tmp)) {
                    break;
                }
                $payerids[] = $tmp;
            }

            $duncount = $last_level_closed - count($payerids);
            if ($duncount < 0) {
                if (!empty($payerids[$last_level_closed])) {
                    $ins_id = $payerids[$last_level_closed];
                    $insname = $this->getInsuranceName($ins_id);
                    $insposition = $last_level_closed + 1;
                }
            }
        }

        // Apply category filters (Due Ins, Due Pt)
        $is_due_ins = $filters['is_due_ins'] ?? false;
        $is_due_pt = $filters['is_due_pt'] ?? false;

        if ($is_due_ins && $duncount >= 0) {
            return null;
        }

        if ($is_due_pt && $duncount < 0) {
            return null;
        }

        // Build processed row
        $row = [
            'id' => $record['id'],
            'pid' => $patient_id,
            'encounter' => $encounter_id,
            'invnumber' => "$patient_id.$encounter_id",
            'custid' => $patient_id,
            'name' => $record['fname'] . ' ' . $record['lname'],
            'fname' => $record['fname'],
            'lname' => $record['lname'],
            'mname' => $record['mname'],
            'address1' => $record['street'],
            'city' => $record['city'],
            'state' => $record['state'],
            'zipcode' => $record['postal_code'],
            'phone' => $record['phone_home'],
            'duncount' => $duncount,
            'dos' => $svcdate,
            'ss' => $record['ss'],
            'DOB' => $record['DOB'],
            'pubpid' => $record['pubpid'],
            'billnote' => $record['billing_note'],
            'referrer' => $record['referrer'],
            'provider' => $record['provider_id'],
            'irnumber' => $record['invoice_refno'],
            'bill_date' => $record['bill_date'],
            'in_collection' => $record['in_collection'],
        ];

        // Get primary insurance name
        $row['ins1'] = '';
        if ($insposition == 1) {
            $row['ins1'] = $insname;
        } else {
            if (empty($payerids)) {
                $tmp = SLEOB::arGetPayerID($patient_id, $svcdate, 1);
                if (!empty($tmp)) {
                    $payerids[] = $tmp;
                }
            }

            if (!empty($payerids)) {
                $row['ins1'] = $this->getInsuranceName($payerids[0]);
            }
        }

        // Get invoice summary for charges/adjustments/payments
        $invlines = InvoiceSummary::arGetInvoiceSummary($patient_id, $encounter_id, true);

        $row['charges'] = 0;
        $row['adjustments'] = 0;
        $row['paid'] = 0;
        $ins_seems_done = true;
        $aging_date = $svcdate;

        foreach ($invlines as $key => $value) {
            $row['charges'] += $value['chg'] + ($value['adj'] ?? 0);
            $row['adjustments'] += 0 - ($value['adj'] ?? 0);
            $row['paid'] += $value['chg'] - $value['bal'];

            foreach ($value['dtl'] as $dkey => $dvalue) {
                $dtldate = trim(substr($dkey, 0, 10));
                if ($dtldate && $dtldate > $aging_date) {
                    $aging_date = $dtldate;
                }
            }

            $lckey = strtolower($key);
            if ($lckey == 'co-pay' || $lckey == 'claim') {
                continue;
            }

            if (count($value['dtl']) <= 1) {
                $ins_seems_done = false;
            }
        }

        // Calculate amount (charges with adjustments)
        $row['amount'] = $row['charges'] + $row['adjustments'];

        // Billing error messages
        $row['billing_errmsg'] = '';
        if ($is_due_ins && $last_level_closed < 1 && $ins_seems_done) {
            $row['billing_errmsg'] = 'Ins1 seems done';
        } elseif ($last_level_closed >= 1 && !$ins_seems_done) {
            $row['billing_errmsg'] = 'Ins1 seems not done';
        }

        // Determine aging date (bill_date vs last activity)
        $aging_date = ($row['bill_date'] > $aging_date) ? $row['bill_date'] : $aging_date;
        $row['aging_date'] = $aging_date;

        // Calculate inactive days
        if ($aging_date == '') {
            $row['inactive_days'] = 'n/a';
        } else {
            $latime = mktime(
                0,
                0,
                0,
                (int) substr($aging_date, 5, 2),
                (int) substr($aging_date, 8, 2),
                (int) substr($aging_date, 0, 4)
            );
            $row['inactive_days'] = floor((time() - $latime) / (60 * 60 * 24));
        }

        // Look up insurance policy number if needed
        if (!empty($filters['form_cb_policy'])) {
            $instype = ($insposition == 2) ? 'secondary' : (($insposition == 3) ? 'tertiary' : 'primary');
            $insrow = QueryUtils::querySingleRow(
                "SELECT policy_number FROM insurance_data WHERE " .
                "pid = ? AND type = ? AND (date <= ? OR date IS NULL) " .
                "ORDER BY date DESC LIMIT 1",
                [$patient_id, $instype, $svcdate]
            );
            $row['policy'] = $insrow['policy_number'] ?? '';
        }

        // Look up group number if needed
        if (!empty($filters['form_cb_group_number'])) {
            $instype = ($insposition == 2) ? 'secondary' : (($insposition == 3) ? 'tertiary' : 'primary');
            $insrow = QueryUtils::querySingleRow(
                "SELECT group_number FROM insurance_data WHERE " .
                "pid = ? AND type = ? AND (date <= ? OR date IS NULL) " .
                "ORDER BY date DESC LIMIT 1",
                [$patient_id, $instype, $svcdate]
            );
            $row['groupnumber'] = $insrow['group_number'] ?? '';
        }

        // Build patient name for sorting key
        $ptname = $record['lname'] . ', ' . $record['fname'];
        if ($record['mname']) {
            $ptname .= ' ' . substr($record['mname'], 0, 1);
        }

        // Add sorting metadata
        $row['_sort_key'] = $insname . '|' . $patient_id . '|' . $ptname . '|' . $encounter_id;

        return $row;
    }

    /**
     * Get insurance company name by ID
     *
     * @param int $payerId Insurance company ID
     * @return string Insurance company name
     */
    private function getInsuranceName(int $payerId): string
    {
        $result = QueryUtils::querySingleRow(
            "SELECT name FROM insurance_companies WHERE id = ?",
            [$payerId]
        );
        return $result['name'] ?? '';
    }
}
