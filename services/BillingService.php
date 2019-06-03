<?php
/**
 * BillingService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Services\InsuranceService;

class BillingService
{
    private $insuranceService;

    public function __construct()
    {
        $this->insuranceService = new InsuranceService();
    }

    public function getBalances($pid)
    {
        $patientBalance = $this->getPatientBalance($pid, false);
        $insuranceBalance = $this->getPatientBalance($pid, true) - $patientBalance;
        $totalBalance = $patientBalance + $insuranceBalance;
        return array(
            'patientBalance' => $patientBalance,
            'insuranceBalance' => $insuranceBalance,
            'totalBalance' => $totalBalance
        );
    }

    private function getPatientBalance($pid, $withInsurance = false, $eid = false)
    {
        $balance = 0;

        $bindArray = array($pid);
        $eRow = "SELECT date, 
                        encounter, 
                        last_level_billed, 
                        last_level_closed, 
                        stmt_count 
                        FROM form_encounter 
                        WHERE pid = ?";
        if ($eid) {
            $eRow .= " AND encounter = ?";
            array_push($bindArray, $eid);
        }
        $eRes = sqlStatement($eRow, $bindArray);

        while ($eRow = sqlFetchArray($eRes)) {
            $encounter = $eRow['encounter'];
            $dos = substr($eRow['date'], 0, 10);
            $insArr = $this->insuranceService->getEffectiveInsurances($pid, $dos);
            $insCount = count($insArr);
            if (!$withInsurance && $eRow['last_level_closed'] < $insCount && $eRow['stmt_count'] == 0) {
                // It's out to insurance so only the co-pay might be due.

                $bSql = "SELECT SUM(fee) AS amount 
                                FROM billing 
                                WHERE pid = ? AND 
                                      encounter = ? AND 
                                      code_type = 'copay' AND 
                                      activity = 1";
                $bRow = sqlQuery($bSql, array($pid, $encounter));

                $dSql = "SELECT SUM(pay_amount) AS payments 
                                FROM ar_activity 
                                WHERE pid = ? AND 
                                      encounter = ? AND 
                                      payer_type = 0";
                $dRow = sqlQuery($dSql, array($pid, $encounter));

                $copay = !empty($insArr[0]['copay']) ? $insArr[0]['copay'] * 1 : 0;
                $amt = !empty($bRow['amount']) ? $bRow['amount'] * 1 : 0;
                $pay = !empty($dRow['payments']) ? $dRow['payments'] * 1 : 0;
                $ptbal = $copay + $amt - $pay;
                if ($ptbal) { // @TODO check if we want to show patient payment credits.
                    $balance += $ptbal;
                }
            } else {
                // Including insurance or not out to insurance, everything is due.

                $bSql = "SELECT SUM(fee) AS amount 
                                FROM billing 
                                WHERE pid = ? AND 
                                encounter = ? AND 
                                activity = 1";
                $bRow = sqlQuery($bSql, array($pid, $encounter));

                $dSql = "SELECT SUM(pay_amount) AS payments,
                                SUM(adj_amount) AS adjustments 
                                FROM ar_activity 
                                WHERE pid = ? AND 
                                      encounter = ?";
                $dRow = sqlQuery($dSql, array($pid, $encounter));

                $sSql = "SELECT SUM(fee) AS amount 
                                FROM drug_sales 
                                WHERE pid = ? AND 
                                      encounter = ?";
                $sRow = sqlQuery($sSql, array($pid, $encounter));

                $balance += $bRow['amount'] + $sRow['amount'] - $dRow['payments'] - $dRow['adjustments'];
            }
        }

        return sprintf('%01.2f', $balance);
    }
}