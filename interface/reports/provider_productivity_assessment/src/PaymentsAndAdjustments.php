<?php

/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)  Juggernaut Systems Express
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Reports\Productivity;

class PaymentsAndAdjustments
{
    public int $pid;
    public int $encounter;
    public bool $name;
    const MAX_ROWS_TO_PROCESS = 2;
    public function getPaymentsAdjustments(): array
    {
        $sql = "SELECT pay_amount AS payments, adj_amount AS adjustments FROM `ar_activity`
                                                         WHERE `pid` = ?
                                                           AND encounter = ?
                                                           AND deleted IS NULL";
        $transactions = sqlStatement($sql, [$this->pid, $this->encounter]);
        $payments = 0;
        $adjustments = 0;
        $pa = 0;
        //We need a flag to come back to say start the loop at the next two rows
        // use the patient name to set the flag. If the name is the same on the next loop. Get the next two rows skip the first two
        //This is working but there are still some outstanding issues with the logic
       if ($this->name == 0) {
            foreach ($transactions as $transaction) {
                if ($transaction['payments'] == '0.00' && $transaction['adjustments'] == '0.00') {
                    continue;
                }
                if ($pa == self::MAX_ROWS_TO_PROCESS) {
                    break;
                }
                if ($transaction['payments'] != '0.00') {
                    $payments = $transaction['payments'];
                }
                if ($transaction['adjustments'] != '0.00') {
                    $adjustments = $transaction['adjustments'];
                }
                $pa++;
            }
       }
        if ($this->name == 1) {
            foreach ($transactions as $transaction) {

                if ($pa < self::MAX_ROWS_TO_PROCESS) {
                    $pa++;
                    continue;
                }
                if ($transaction['payments'] != '0.00') {
                    $payments = $transaction['payments'];
                }
                if ($transaction['adjustments'] != '0.00') {
                    $adjustments = $transaction['adjustments'];
                }
                $pa++;
            }
        }

        return [
            'payments' => $payments,
            'adjustments' => $adjustments
        ];
    }
}
