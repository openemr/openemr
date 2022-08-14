<?php

/**
 *
 * @package OpenEMR
 * @author Eldho Chacko <eldho@zhservices.com>
 * @author Paul Simon K <paul@zhservices.com>
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @author Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2020 Rod Roark <rod@sunsetsystems.com>
 * @link https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Billing\SLEOB;
use OpenEMR\Common\Logging\EventAuditLogger;

// Post a payment to the payments table.
//
function frontPayment($patient_id, $encounter, $method, $source, $amount1, $amount2, $timestamp, $auth = "")
{

    if (empty($auth)) {
        $auth = $_SESSION['authUser'];
    }

    $tmprow = sqlQuery(
        "SELECT date FROM form_encounter WHERE " .
        "encounter=? and pid=?",
        array($encounter,$patient_id)
    );
        //the manipulation is done to insert the amount paid into payments table in correct order to show in front receipts report,
        //if the payment is for today's encounter it will be shown in the report under today field and otherwise shown as previous
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
    ") VALUES ( ?, ?, ?, ?, ?, ?, ?, ?)", array($patient_id,$encounter,$timestamp,$auth,$method,$source,$amount1,$amount2));
    return $payid;
}

//===============================================================================
//This section handles the common functins of payment screens.
//===============================================================================
function DistributionInsert($CountRow, $created_time, $user_id)
{
//Function inserts the distribution.Payment,Adjustment,Deductible,Takeback & Follow up reasons are inserted as seperate rows.
 //It automatically pushes to next insurance for billing.
 //In the screen a drop down of Ins1,Ins2,Ins3,Pat are given.The posting can be done for any level.
    $Affected = 'no';
    // watch for payments less than $1, thanks @snailwell
    if (isset($_POST["Payment$CountRow"]) && (floatval($_POST["Payment$CountRow"]) > 0)) {
        if (trim(formData('type_name')) == 'insurance') {
            if (trim(formData("HiddenIns$CountRow")) == 1) {
                $AccountCode = "IPP";
            }

            if (trim(formData("HiddenIns$CountRow")) == 2) {
                $AccountCode = "ISP";
            }

            if (trim(formData("HiddenIns$CountRow")) == 3) {
                $AccountCode = "ITP";
            }
        } elseif (trim(formData('type_name')) == 'patient') {
            $AccountCode = "PP";
        }

        sqlBeginTrans();
        $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array(trim(formData('hidden_patient_code')), trim(formData("HiddenEncounter$CountRow"))));
        sqlStatement("insert into ar_activity set "    .
        "pid = '"       . trim(formData('hidden_patient_code')) .
        "', encounter = '"     . trim(formData("HiddenEncounter$CountRow"))  .
        "', sequence_no = '" . $sequence_no['increment'] .
                "', code_type = '"      . trim(formData("HiddenCodetype$CountRow"))  .
        "', code = '"      . trim(formData("HiddenCode$CountRow"))  .
        "', modifier = '"      . trim(formData("HiddenModifier$CountRow"))  .
        "', payer_type = '"   . trim(formData("HiddenIns$CountRow")) .
        "', post_time = '"  . trim($created_time) .
        "', post_user = '" . trim($user_id)  .
        "', session_id = '"    . trim(formData('payment_id')) .
        "', modified_time = '"  . trim($created_time) .
        "', pay_amount = '" . trim(formData("Payment$CountRow"))  .
        "', adj_amount = '"    . 0 .
        "', account_code = '" . "$AccountCode"  .
        "'");
          sqlCommitTrans();
          $Affected = 'yes';
    }

    if (!empty($_POST["AdjAmount$CountRow"]) && (floatval($_POST["AdjAmount$CountRow"] ?? null)) != 0) {
        if (trim(formData('type_name')) == 'insurance') {
            $AdjustString = "Ins adjust Ins" . trim(formData("HiddenIns$CountRow"));
            $AccountCode = "IA";
        } elseif (trim(formData('type_name')) == 'patient') {
            $AdjustString = "Pt adjust";
            $AccountCode = "PA";
        }

        sqlBeginTrans();
        $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array(trim(formData('hidden_patient_code')), trim(formData("HiddenEncounter$CountRow"))));
        sqlStatement("insert into ar_activity set "    .
        "pid = '"       . trim(formData('hidden_patient_code')) .
        "', encounter = '"     . trim(formData("HiddenEncounter$CountRow"))  .
        "', sequence_no = '"     . $sequence_no['increment']  .
                "', code_type = '"      . trim(formData("HiddenCodetype$CountRow"))  .
        "', code = '"      . trim(formData("HiddenCode$CountRow"))  .
        "', modifier = '"      . trim(formData("HiddenModifier$CountRow"))  .
        "', payer_type = '"   . trim(formData("HiddenIns$CountRow")) .
        "', post_time = '"  . trim($created_time) .
        "', post_user = '" . trim($user_id)  .
        "', session_id = '"    . trim(formData('payment_id')) .
        "', modified_time = '"  . trim($created_time) .
        "', pay_amount = '" . 0  .
        "', adj_amount = '"    . trim(formData("AdjAmount$CountRow")) .
        "', memo = '" . "$AdjustString"  .
        "', account_code = '" . "$AccountCode"  .
        "'");
           sqlCommitTrans();
          $Affected = 'yes';
    }

    if (!empty($_POST["Deductible$CountRow"]) && (floatval($_POST["Deductible$CountRow"] ?? null)) > 0) {
         sqlBeginTrans();
         $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array(trim(formData('hidden_patient_code')), trim(formData("HiddenEncounter$CountRow"))));
        sqlStatement("insert into ar_activity set "    .
        "pid = '"       . trim(formData('hidden_patient_code')) .
        "', encounter = '"     . trim(formData("HiddenEncounter$CountRow"))  .
        "', sequence_no = '"     . $sequence_no['increment']  .
                "', code_type = '"      . trim(formData("HiddenCodetype$CountRow"))  .
        "', code = '"      . trim(formData("HiddenCode$CountRow"))  .
        "', modifier = '"      . trim(formData("HiddenModifier$CountRow"))  .
        "', payer_type = '"   . trim(formData("HiddenIns$CountRow")) .
        "', post_time = '"  . trim($created_time) .
        "', post_user = '" . trim($user_id)  .
        "', session_id = '"    . trim(formData('payment_id')) .
        "', modified_time = '"  . trim($created_time) .
        "', pay_amount = '" . 0  .
        "', adj_amount = '"    . 0 .
        "', memo = '"    . "Deductible $" . trim(formData("Deductible$CountRow")) .
        "', account_code = '" . "Deduct"  .
        "'");
           sqlCommitTrans();
          $Affected = 'yes';
    }

    if (!empty($_POST["Takeback$CountRow"]) && (floatval($_POST["Takeback$CountRow"] ?? null)) > 0) {
         sqlBeginTrans();
         $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array(trim(formData('hidden_patient_code')), trim(formData("HiddenEncounter$CountRow"))));
        sqlStatement("insert into ar_activity set "    .
        "pid = '"       . trim(formData('hidden_patient_code')) .
        "', encounter = '"     . trim(formData("HiddenEncounter$CountRow"))  .
        "', sequence_no = '"     . $sequence_no['increment']  .
                "', code_type = '"      . trim(formData("HiddenCodetype$CountRow"))  .
        "', code = '"      . trim(formData("HiddenCode$CountRow"))  .
        "', modifier = '"      . trim(formData("HiddenModifier$CountRow"))  .
        "', payer_type = '"   . trim(formData("HiddenIns$CountRow")) .
        "', post_time = '"  . trim($created_time) .
        "', post_user = '" . trim($user_id)  .
        "', session_id = '"    . trim(formData('payment_id')) .
        "', modified_time = '"  . trim($created_time) .
        "', pay_amount = '" . trim(formData("Takeback$CountRow")) * -1  .
        "', adj_amount = '"    . 0 .
        "', account_code = '" . "Takeback"  .
        "'");
           sqlCommitTrans();
          $Affected = 'yes';
    }

    if (isset($_POST["FollowUp$CountRow"]) && $_POST["FollowUp$CountRow"] == 'y') {
         sqlBeginTrans();
         $sequence_no = sqlQuery("SELECT IFNULL(MAX(sequence_no),0) + 1 AS increment FROM ar_activity WHERE pid = ? AND encounter = ?", array(trim(formData('hidden_patient_code')), trim(formData("HiddenEncounter$CountRow"))));
         sqlStatement("insert into ar_activity set "    .
        "pid = '"       . trim(formData('hidden_patient_code')) .
        "', encounter = '"     . trim(formData("HiddenEncounter$CountRow"))  .
        "', sequence_no = '"     . $sequence_no['increment']  .
                "', code_type = '"      . trim(formData("HiddenCodetype$CountRow"))  .
        "', code = '"      . trim(formData("HiddenCode$CountRow"))  .
        "', modifier = '"      . trim(formData("HiddenModifier$CountRow"))  .
        "', payer_type = '"   . trim(formData("HiddenIns$CountRow")) .
        "', post_time = '"  . trim($created_time) .
        "', post_user = '" . trim($user_id)  .
        "', session_id = '"    . trim(formData('payment_id')) .
        "', modified_time = '"  . trim($created_time) .
        "', pay_amount = '" . 0  .
        "', adj_amount = '"    . 0 .
        "', follow_up = '"    . "y" .
        "', follow_up_note = '"    . trim(formData("FollowUpReason$CountRow")) .
        "'");
           sqlCommitTrans();
          $Affected = 'yes';
    }

    if ($Affected == 'yes') {
        if (trim(formData('type_name')) != 'patient') {
            $ferow = sqlQuery("select last_level_closed from form_encounter  where
		pid ='" . trim(formData('hidden_patient_code')) . "' and encounter='" . trim(formData("HiddenEncounter$CountRow")) . "'");
              //multiple charges can come.
            if ($ferow['last_level_closed'] < trim(formData("HiddenIns$CountRow"))) {
                //last_level_closed gets increased. unless a follow up is required.
                // in which case we'll allow secondary to be re setup to current setup.
                // just not advancing last closed.
                $tmp = ((!empty($_POST["Payment$CountRow"]) ? floatval($_POST["Payment$CountRow"]) : null) + (!empty($_POST["AdjAmount$CountRow"]) ? floatval($_POST["AdjAmount$CountRow"]) : null));
                if ((empty($_POST["FollowUp$CountRow"]) || ($_POST["FollowUp$CountRow"] != 'y')) && $tmp !== 0) {
                    sqlStatement("update form_encounter set last_level_closed='" .
                        trim(formData("HiddenIns$CountRow")) .
                        "' where pid ='" . trim(formData('hidden_patient_code')) .
                        "' and encounter='" . trim(formData("HiddenEncounter$CountRow")) . "'");
                }
                  //-----------------------------------
                  // Determine the next insurance level to be billed.
                  $ferow = sqlQuery("SELECT date, last_level_closed " .
                    "FROM form_encounter WHERE " .
                    "pid = '" . trim(formData('hidden_patient_code')) . "' AND encounter = '" . trim(formData("HiddenEncounter$CountRow")) . "'");
                  $date_of_service = substr($ferow['date'], 0, 10);
                  $new_payer_type = 0 + $ferow['last_level_closed'];
                if ($new_payer_type <= 3 && !empty($ferow['last_level_closed']) || $new_payer_type == 0) {
                    ++$new_payer_type;
                }

                  $new_payer_id = SLEOB::arGetPayerID(trim(formData('hidden_patient_code')), $date_of_service, $new_payer_type);
                if ($new_payer_id > 0) {
                        SLEOB::arSetupSecondary(trim(formData('hidden_patient_code')), trim(formData("HiddenEncounter$CountRow")), 0);
                }

                    //-----------------------------------
            }
        }
    }
}
//===============================================================================
  // Delete rows, with logging, for the specified table using the
  // specified WHERE clause.  Borrowed from deleter.php.
  //
function row_delete($table, $where)
{
    $tres = sqlStatement("SELECT * FROM " . escape_table_name($table) . " WHERE $where");
    $count = 0;
    while ($trow = sqlFetchArray($tres)) {
        $logstring = "";
        foreach ($trow as $key => $value) {
            if (! $value || $value == '0000-00-00 00:00:00') {
                continue;
            }

            if ($logstring) {
                $logstring .= " ";
            }

            $logstring .= $key . "='" . addslashes($value) . "'";
        }

        EventAuditLogger::instance()->newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$table: $logstring");
        ++$count;
    }

    if ($count) {
        $query = "DELETE FROM " . escape_table_name($table) . " WHERE $where";
        sqlStatement($query);
    }
}

// Deactivate rows, with logging, for the specified table using the
// specified SET and WHERE clauses.  Borrowed from deleter.php.
//
function row_modify($table, $set, $where)
{
    if (sqlQuery("SELECT * FROM " . escape_table_name($table) . " WHERE $where")) {
        EventAuditLogger::instance()->newEvent(
            "deactivate",
            $_SESSION['authUser'],
            $_SESSION['authProvider'],
            1,
            "$table: $where"
        );
        $query = "UPDATE $table SET $set WHERE $where";
        sqlStatement($query);
    }
}

//===============================================================================
