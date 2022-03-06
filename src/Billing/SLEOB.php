<?php

/**
 * This class has various billing functions for posting charges and payments.
 *
 * @package OpenEMR
 * @author Rod Roark <rod@sunsetsystems.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2005-2009 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

require_once(dirname(__FILE__) . "/../../library/patient.inc");

use OpenEMR\Billing\BillingUtilities;

class SLEOB
{
    // Try to figure out our invoice number (pid.encounter) from the
    // claim ID and other stuff in the ERA.  This should be straightforward
    // except that some payers mangle the claim ID that we give them.
    //
    public static function slInvoiceNumber(&$out)
    {
        $invnumber = $out['our_claim_id'];
        $atmp = preg_split('/[ -]/', $invnumber);
        $acount = count($atmp);

        $pid = 0;
        $encounter = 0;
        if ($acount == 2) {
            $pid = $atmp[0];
            $encounter = $atmp[1];
        } elseif ($acount == 3) {
            $pid = $atmp[0];
            $brow = sqlQuery("SELECT encounter FROM billing WHERE " .
                "pid = '$pid' AND encounter = ? AND activity = 1", array($atmp[1]));

            $encounter = $brow['encounter'];
        } elseif ($acount == 1) {
            $pres = sqlStatement("SELECT pid FROM patient_data WHERE " .
                "lname LIKE ? AND " .
                "fname LIKE ? " .
                "ORDER BY pid DESC", array($out['patient_lname'], $out['patient_fname']));
            while ($prow = sqlFetchArray($pres)) {
                if (strpos($invnumber, $prow['pid']) === 0) {
                    $pid = $prow['pid'];
                    $encounter = substr($invnumber, strlen($pid));
                    break;
                }
            }
        }

        if ($pid && $encounter) {
            $invnumber = "$pid.$encounter";
        }

        return array($pid, $encounter, $invnumber);
    }

    // This gets a posting session ID.  If the payer ID is not 0 and a matching
    // session already exists, then its ID is returned.  Otherwise a new session
    // is created.
    //
    public static function arGetSession($payer_id, $reference, $check_date, $deposit_date = '', $pay_total = 0)
    {
        if (empty($deposit_date)) {
            $deposit_date = $check_date;
        }

        if ($payer_id) {
            $row = sqlQuery("SELECT session_id FROM ar_session WHERE " .
                "payer_id = ? AND reference = ? AND " .
                "check_date = ? AND deposit_date = ? " .
                "ORDER BY session_id DESC LIMIT 1", array($payer_id, $reference, $check_date, $deposit_date));
            if (!empty($row['session_id'])) {
                return $row['session_id'];
            }
        }

        return sqlInsert("INSERT INTO ar_session ( " .
            "payer_id, user_id, reference, check_date, deposit_date, pay_total " .
            ") VALUES ( ?, ?, ?, ?, ?, ? )", array($payer_id, $_SESSION['authUserID'], $reference, $check_date, $deposit_date, $pay_total));
    }

    //writing the check details to Session Table on ERA proxcessing
    public static function arPostSession($payer_id, $check_number, $check_date, $pay_total, $post_to_date, $deposit_date, $debug)
    {
        $query = "INSERT INTO ar_session( " .
            "payer_id,user_id,closed,reference,check_date,pay_total,post_to_date,deposit_date,patient_id,payment_type,adjustment_code,payment_method " .
            ") VALUES (?, ?, 0, ?, ?, ?, ? ,?, 0, 'insurance', 'insurance_payment', 'electronic')";
        if ($debug) {
            echo text($query) . "<br />\n";
        } else {
            $sessionId = sqlInsert($query, array($payer_id, $_SESSION['authUserID'], 'ePay - ' . $check_number, $check_date, $pay_total, $post_to_date, $deposit_date));
            return $sessionId;
        }
    }

    // Post a payment, new style.
    //
    public static function arPostPayment(
        $patient_id,
        $encounter_id,
        $session_id,
        $amount,
        $code,
        $payer_type,
        $memo,
        $debug,
        $time = '',
        $codetype = '',
        $date = ''
    ) {
        $codeonly = $code;
        $modifier = '';
        $tmp = strpos($code, ':');
        if ($tmp) {
            $codeonly = substr($code, 0, $tmp);
            $modifier = substr($code, $tmp + 1);
        }

        if (empty($time)) {
            $time = date('Y-m-d H:i:s');
        }

        sqlBeginTrans();
        $sequence_no = sqlQuery(
            "SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?",
            array($patient_id, $encounter_id)
        );
        $query = "INSERT INTO ar_activity ( " .
            "pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time, post_date, post_user, " .
            "session_id, memo, pay_amount " .
            ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        sqlStatement(
            $query,
            array(
                $patient_id,
                $encounter_id,
                $sequence_no['increment'],
                $codetype,
                $codeonly,
                $modifier,
                $payer_type,
                $time,
                $date,
                $_SESSION['authUserID'],
                $session_id,
                $memo,
                $amount
            )
        );
        sqlCommitTrans();
        return;
    }

    // Post a charge.  This is called only from sl_eob_process.php where
    // automated remittance processing can create a new service item.
    // Here we add it as an unauthorized item to the billing table.
    //
    public static function arPostCharge($patient_id, $encounter_id, $session_id, $amount, $units, $thisdate, $code, $description, $debug, $codetype = '')
    {
        /*****************************************************************
         * // Select an existing billing item as a template.
         * $row= sqlQuery("SELECT * FROM billing WHERE " .
         * "pid = '$patient_id' AND encounter = '$encounter_id' AND " .
         * "code_type = 'CPT4' AND activity = 1 " .
         * "ORDER BY id DESC LIMIT 1");
         * $this_authorized = 0;
         * $this_provider = 0;
         * if (!empty($row)) {
         * $this_authorized = $row['authorized'];
         * $this_provider = $row['provider_id'];
         * }
         *****************************************************************/

        if (empty($codetype)) {
            // default to CPT4 if empty, which is consistent with previous functionality.
            $codetype = "CPT4";
        }

        $codeonly = $code;
        $modifier = '';
        $tmp = strpos($code, ':');
        if ($tmp) {
            $codeonly = substr($code, 0, $tmp);
            $modifier = substr($code, $tmp + 1);
        }

        BillingUtilities::addBilling(
            $encounter_id,
            $codetype,
            $codeonly,
            $description,
            $patient_id,
            0,
            0,
            $modifier,
            $units,
            $amount,
            '',
            ''
        );
    }

    // Post an adjustment, new style.
    //
    public static function arPostAdjustment($patient_id, $encounter_id, $session_id, $amount, $code, $payer_type, $reason, $debug, $time = '', $codetype = '')
    {
        $codeonly = $code;
        $modifier = '';
        $tmp = strpos($code, ':');
        if ($tmp) {
            $codeonly = substr($code, 0, $tmp);
            $modifier = substr($code, $tmp + 1);
        }

        if (empty($time)) {
            $time = date('Y-m-d H:i:s');
        }

        sqlBeginTrans();
        $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array($patient_id, $encounter_id));
        $query = "INSERT INTO ar_activity ( " .
            "pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_user, post_time, " .
            "session_id, memo, adj_amount " .
            ") VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        sqlStatement($query, array($patient_id, $encounter_id, $sequence_no['increment'], $codetype, $codeonly, $modifier, $payer_type, $_SESSION['authUserID'], $time, $session_id, $reason, $amount));
        sqlCommitTrans();
        return;
    }

    public static function arGetPayerID($patient_id, $date_of_service, $payer_type)
    {
        if ($payer_type < 1 || $payer_type > 3) {
            return 0;
        }

        $tmp = array(1 => 'primary', 2 => 'secondary', 3 => 'tertiary');
        $value = $tmp[$payer_type];
        $query = "SELECT provider FROM insurance_data WHERE " .
            "pid = ? AND type = ? AND (date <= ? OR date IS NULL) " .
            "ORDER BY date DESC LIMIT 1";
        $nprow = sqlQuery($query, array($patient_id, $value, $date_of_service));
        if (empty($nprow)) {
            return 0;
        }

        return $nprow['provider'];
    }

    // Make this invoice re-billable, new style.
    //
    public static function arSetupSecondary($patient_id, $encounter_id, $debug, $crossover = 0)
    {
        if ($crossover == 1) {
            //if claim forwarded setting a new status
            $status = 6;
        } else {
            $status = 1;
        }

        // Determine the next insurance level to be billed.
        $ferow = sqlQuery("SELECT date, last_level_billed " .
            "FROM form_encounter WHERE " .
            "pid = ? AND encounter = ?", array($patient_id, $encounter_id));
        $date_of_service = substr($ferow['date'], 0, 10);
        $new_payer_type = 0 + $ferow['last_level_billed'];
        if ($new_payer_type < 3 && !empty($ferow['last_level_billed']) || $new_payer_type == 0) {
            ++$new_payer_type;
        }

        $new_payer_id = self::arGetPayerID($patient_id, $date_of_service, $new_payer_type);

        if ($new_payer_id) {
            // Queue up the claim.
            if (!$debug) {
                BillingUtilities::updateClaim(true, $patient_id, $encounter_id, $new_payer_id, $new_payer_type, $status, 5, '', 'hcfa', '', $crossover);
            }
        } else {
            // Just reopen the claim.
            if (!$debug) {
                BillingUtilities::updateClaim(true, $patient_id, $encounter_id, -1, -1, $status, 0, '', '', '', $crossover);
            }
        }

        return xl("Encounter ") . $encounter_id . xl(" is ready for re-billing.");
    }
}
