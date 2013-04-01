<?php

/**
 * api/addpayment.php Add patient payment.
 *
 * API is allowed to add patient payment.
 * 
 * Copyright (C) 2012 Karl Englund <karl@mastermobileproducts.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-3.0.html>;.
 *
 * @package OpenEMR
 * @author  Karl Englund <karl@mastermobileproducts.com>
 * @link    http://www.open-emr.org
 */
header("Content-Type:text/xml");
$ignoreAuth = true;
require_once 'classes.php';

$xml_string = "";
$xml_string = "<payment>";

$token = $_POST['token'];

$payment_type = $_POST['payment_type'];

$patient_id = $_POST['patient_id'];
$visit_id = $_POST['visit_id'];
$amount = $_POST['amount'];
$modifier = $_POST['modifier'];
$check_ref_number = $_POST['check_ref_number'];
$payment_method = $_POST['payment_method'];

$prepayment = $_POST['prepayment'];
$NameNew = $_POST['fname'] . " " . $_POST['lname'] . " " . $_POST['mname'];

$now = time();
$today = date('Y-m-d', $now);
$timestamp = date('Y-m-d H:i:s', $now);

// Insertion in Payment table
function frontPayment($patient_id, $visit_id, $auth, $payment_method, $check_ref_number, $amount1, $amount2) {
    global $timestamp;
    $tmprow = sqlQuery("SELECT date FROM form_encounter WHERE " .
            "encounter=? and pid=?", array($visit_id, $patient_id));
    $tmprowArray = explode(' ', $tmprow['date']);
    if (date('Y-m-d') == $tmprowArray[0]) {
        if ($amount1 == 0) {
            $amount1 = $amount2;
            $amount2 = 0;
        }
    } else {
        if ($amount2 == 0) {
            $amount2 = $amount1;
            $amount1 = 0;
        }
    }
    $payid = sqlInsert("INSERT INTO payments ( " .
            "pid, encounter, dtime, user, method, source, amount1, amount2 " .
            ") VALUES ( ?, ?, ?, ?, ?, ?, ?, ?)", array($patient_id, $visit_id, $timestamp, $auth, $payment_method, $check_ref_number, $amount1, $amount2));
    return $payid;
}

if ($userId = validateToken($token)) {
    $user = getUsername($userId);
    $acl_allow = acl_check('acct', 'bill', $user);


    if ($acl_allow) {

        if ($payment_type == 'pre_payment') {

            $strQuery = "insert into ar_session set " .
                    "payer_id = ?" .
                    ", patient_id = ?" .
                    ", user_id = ?" .
                    ", closed = ?" .
                    ", reference = ?" .
                    ", check_date =  now() , deposit_date = now() " .
                    ",  pay_total = ?" .
                    ", payment_type = 'patient'" .
                    ", description = ?" .
                    ", adjustment_code = 'pre_payment'" .
                    ", post_to_date = now() " .
                    ", payment_method = ?";
            $payment_id = idSqlStatement($strQuery, array(0, $patient_id, $userId, 0, $check_ref_number, $prepayment, $NameNew, $payment_method));

            $result = frontPayment($patient_id, $visit_id, $user, $payment_method, $check_ref_number, $amount, 0);
        }

        if ($payment_type == 'COPAY') {//copay saving to ar_session and ar_activity tables
            $ResultSearchNew = sqlStatement("SELECT * FROM billing LEFT JOIN code_types ON billing.code_type=code_types.ct_key " .
                    "WHERE code_types.ct_fee=1 AND billing.activity!=0 AND billing.pid =? AND encounter=? ORDER BY billing.code,billing.modifier", array($patient_id, $visit_id));

            if ($RowSearch = sqlFetchArray($ResultSearchNew)) {
                $Codetype = $RowSearch['code_type'];
                $Code = $RowSearch['code'];
                $Modifier = $RowSearch['modifier'];
            } else {
                $Codetype = '';
                $Code = '';
                $Modifier = '';
            }

            $strQuery1 = "INSERT INTO ar_session (payer_id,user_id,reference,check_date,deposit_date,pay_total," .
                    " global_amount,payment_type,description,patient_id,payment_method,adjustment_code,post_to_date) " .
                    " VALUES ('0',?,?,now(),now(),?,'','patient','COPAY',?,?,'patient_payment',now())";
            $session_id = idSqlStatement($strQuery1, array($userId, $check_ref_number, $amount, $patient_id, $payment_method));


            $insrt_id = idSqlStatement("INSERT INTO ar_activity (pid,encounter,code_type,code,modifier,payer_type,post_time,post_user,session_id,pay_amount,account_code)" .
                    " VALUES (?,?,?,?,?,0,now(),?,?,?,'PCP')", array($patient_id, $visit_id, $Codetype, $Code, $Modifier, $userId, $session_id, $amount));

            $result = frontPayment($patient_id, $visit_id, $user, $payment_method, $check_ref_number, $amount, 0);
        }

        if ($payment_type == 'invoice_balance' || $payment_type == 'cash') {

            if ($payment_type == 'cash') {
                sqlStatement("update form_encounter set last_level_closed=? where encounter=? and pid=? ", array(4, $visit_id, $patient_id));
                sqlStatement("update billing set billed=? where encounter=? and pid=?", array(1, $visit_id, $patient_id));
            }



            $adjustment_code = 'patient_payment';
            $strQuery2 = "insert into ar_session set " .
                    "payer_id = ?" .
                    ", patient_id = ?" .
                    ", user_id = ?" .
                    ", closed = ?" .
                    ", reference = ?" .
                    ", check_date =  now() , deposit_date = now() " .
                    ", pay_total = ?" .
                    ", payment_type = 'patient'" .
                    ", description = ?" .
                    ", adjustment_code = ?" .
                    ", post_to_date = now() " .
                    ", payment_method = ?";
            $payment_id = idSqlStatement($strQuery2, array(0, $patient_id, $userId, 0, $check_ref_number, $amount, $NameNew, $adjustment_code, $payment_method));

            $result = frontPayment($patient_id, $visit_id, $user, $payment_method, $check_ref_number, 0, $amount); //insertion to 'payments' table.

            $resMoneyGot = sqlStatement("SELECT sum(pay_amount) as PatientPay FROM ar_activity where pid =? and " .
                    "encounter =? and payer_type=0 and account_code='PCP'", array($patient_id, $visit_id)); //new fees screen copay gives account_code='PCP'
            $rowMoneyGot = sqlFetchArray($resMoneyGot);
            $Copay = $rowMoneyGot['PatientPay'];
            //Looping the existing code and modifier
            $ResultSearchNew = sqlStatement("SELECT * FROM billing LEFT JOIN code_types ON billing.code_type=code_types.ct_key WHERE code_types.ct_fee=1 " .
                    "AND billing.activity!=0 AND billing.pid =? AND encounter=? ORDER BY billing.code,billing.modifier", array($form_pid, $enc));
            while ($RowSearch = sqlFetchArray($ResultSearchNew)) {
                $Codetype = $RowSearch['code_type'];
                $Code = $RowSearch['code'];
                $Modifier = $RowSearch['modifier'];
                $Fee = $RowSearch['fee'];

                $resMoneyGot = sqlStatement("SELECT sum(pay_amount) as MoneyGot FROM ar_activity where pid =? " .
                        "and code_type=? and code=? and modifier=? and encounter =? and !(payer_type=0 and account_code='PCP')", array($form_pid, $Codetype, $Code, $Modifier, $enc));
                //new fees screen copay gives account_code='PCP'
                $rowMoneyGot = sqlFetchArray($resMoneyGot);
                $MoneyGot = $rowMoneyGot['MoneyGot'];

                $resMoneyAdjusted = sqlStatement("SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where " .
                        "pid =? and code_type=? and code=? and modifier=? and encounter =?", array($form_pid, $Codetype, $Code, $Modifier, $enc));
                $rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
                $MoneyAdjusted = $rowMoneyAdjusted['MoneyAdjusted'];

                $Remainder = $Fee - $Copay - $MoneyGot - $MoneyAdjusted;
                $Copay = 0;

                if (round($Remainder, 2) != 0 && $amount != 0) {
                    if ($amount - $Remainder >= 0) {
                        $insert_value = $Remainder;
                        $amount = $amount - $Remainder;
                    } else {
                        $insert_value = $amount;
                        $amount = 0;
                    }
                    sqlStatement("insert into ar_activity set " .
                            "pid = ?" .
                            ", encounter = ?" .
                            ", code_type = ?" .
                            ", code = ?" .
                            ", modifier = ?" .
                            ", payer_type = ?" .
                            ", post_time = now() " .
                            ", post_user = ?" .
                            ", session_id = ?" .
                            ", pay_amount = ?" .
                            ", adj_amount = ?" .
                            ", account_code = 'PP'", array($patient_id, $visit_id, $Codetype, $Code, $Modifier, 0, $userId, $payment_id, $insert_value, 0));
                }//if
            }//while
        }
        if ($result) {
            $xml_string .= "<status>0</status>";
            $xml_string .= "<reason>The Payment has been added</reason>";
        } else {
            $xml_string .= "<status>-1</status>";
            $xml_string .= "<reason>ERROR: Sorry, there was an error processing your data. Please re-submit the information again.</reason>";
        }
    } else {
        $xml_array['status'] = -2;
        $xml_array['reason'] = 'You are not Authorized to perform this action';
    }
} else {
    $xml_string .= "<status>-2</status>";
    $xml_string .= "<reason>Invalid Token</reason>";
}

$xml_string .= "</payment>";
echo $xml_string;
?>