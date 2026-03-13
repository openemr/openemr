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
use OpenEMR\PaymentProcessing\Recorder;

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
        [$encounter,$patient_id]
    );
        //the manipulation is done to insert the amount paid into payments table in correct order to show in front receipts report,
        //if the payment is for today's encounter it will be shown in the report under today field and otherwise shown as previous
    $tmprowArray = explode(' ', (string) $tmprow['date']);
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
    ") VALUES ( ?, ?, ?, ?, ?, ?, ?, ?)", [$patient_id,$encounter,$timestamp,$auth,$method,$source,$amount1,$amount2]);
    return $payid;
}

//===============================================================================
//This section handles the common functions of payment screens.
//===============================================================================
function DistributionInsert(int $CountRow, $created_time, $user_id): void
{
//Function inserts the distribution.Payment,Adjustment,Deductible,Takeback & Follow up reasons are inserted as separate rows.
 //It automatically pushes to next insurance for billing.
 //In the screen a drop down of Ins1,Ins2,Ins3,Pat are given.The posting can be done for any level.
    $r = new Recorder();
    $Affected = false;
    // watch for payments less than $1, thanks @snailwell
    if (!empty($_POST["Payment$CountRow"]) && (floatval($_POST["Payment$CountRow"]) > 0)) {
        if (trimPost('type_name') == 'insurance') {
            if (trimPost("HiddenIns$CountRow") == 1) {
                $AccountCode = "IPP";
            }

            if (trimPost("HiddenIns$CountRow") == 2) {
                $AccountCode = "ISP";
            }

            if (trimPost("HiddenIns$CountRow") == 3) {
                $AccountCode = "ITP";
            }
        } elseif (trimPost('type_name') == 'patient') {
            $AccountCode = "PP";
        }

        $r->recordActivity([
            'patientId' => trimPost('hidden_patient_code'),
            'encounterId' => trimPost("HiddenEncounter$CountRow"),
            'codeType' => trimPost("HiddenCodetype$CountRow"),
            'code' => trimPost("HiddenCode$CountRow"),
            'modifier' => trimPost("HiddenModifier$CountRow"),
            'payerType' => trimPost("HiddenIns$CountRow"),
            'postUser' => trim((string) $user_id),
            'sessionId' => trimPost('payment_id'),
            'payAmount' => trimPost("Payment$CountRow"),
            'adjustmentAmount' => '0',
            'memo' => '',
            'accountCode' => $AccountCode,
        ]);
        $Affected = true;
    }

    if (!empty($_POST["AdjAmount$CountRow"]) && (floatval($_POST["AdjAmount$CountRow"] ?? null)) != 0) {
        if (trimPost('type_name') == 'insurance') {
            $AdjustString = "Ins adjust Ins" . trimPost("HiddenIns$CountRow");
            $AccountCode = "IA";
        } elseif (trimPost('type_name') == 'patient') {
            $AdjustString = "Pt adjust";
            $AccountCode = "PA";
        }

        $r->recordActivity([
            'patientId' => trimPost('hidden_patient_code'),
            'encounterId' => trimPost("HiddenEncounter$CountRow"),
            'codeType' => trimPost("HiddenCodetype$CountRow"),
            'code' => trimPost("HiddenCode$CountRow"),
            'modifier' => trimPost("HiddenModifier$CountRow"),
            'payerType' => trimPost("HiddenIns$CountRow"),
            'postUser' => trim((string) $user_id),
            'sessionId' => trimPost('payment_id'),
            'payAmount' => '0',
            'adjustmentAmount' => trimPost("AdjAmount$CountRow"),
            'memo' => $AdjustString,
            'accountCode' => $AccountCode,
        ]);
        $Affected = true;
    }

    if (!empty($_POST["Deductible$CountRow"]) && (floatval($_POST["Deductible$CountRow"] ?? null)) > 0) {
        $r->recordActivity([
            'patientId' => trimPost('hidden_patient_code'),
            'encounterId' => trimPost("HiddenEncounter$CountRow"),
            'codeType' => trimPost("HiddenCodetype$CountRow"),
            'code' => trimPost("HiddenCode$CountRow"),
            'modifier' => trimPost("HiddenModifier$CountRow"),
            'payerType' => trimPost("HiddenIns$CountRow"),
            'postUser' => trim((string) $user_id),
            'sessionId' => trimPost('payment_id'),
            'payAmount' => '0',
            'adjustmentAmount' => '0',
            'memo' => 'Deductible $' . trimPost("Deductible$CountRow"),
            'accountCode' => 'Deduct',
        ]);
        $Affected = true;
    }

    if (!empty($_POST["Takeback$CountRow"]) && (floatval($_POST["Takeback$CountRow"] ?? null)) > 0) {
        $r->recordActivity([
            'patientId' => trimPost('hidden_patient_code'),
            'encounterId' => trimPost("HiddenEncounter$CountRow"),
            'codeType' => trimPost("HiddenCodetype$CountRow"),
            'code' => trimPost("HiddenCode$CountRow"),
            'modifier' => trimPost("HiddenModifier$CountRow"),
            'payerType' => trimPost("HiddenIns$CountRow"),
            'postUser' => trim((string) $user_id),
            'sessionId' => trimPost('payment_id'),
            'payAmount' => strval(floatval(trimPost("Takeback$CountRow")) * -1),
            'adjustmentAmount' => '0',
            'memo' => '',
            'accountCode' => 'Takeback',
        ]);
        $Affected = true;
    }

    if (isset($_POST["FollowUp$CountRow"]) && $_POST["FollowUp$CountRow"] == 'y') {
        $r->recordActivity([
            'patientId' => trimPost('hidden_patient_code'),
            'encounterId' => trimPost("HiddenEncounter$CountRow"),
            'codeType' => trimPost("HiddenCodetype$CountRow"),
            'code' => trimPost("HiddenCode$CountRow"),
            'modifier' => trimPost("HiddenModifier$CountRow"),
            'payerType' => trimPost("HiddenIns$CountRow"),
            'postUser' => trim((string) $user_id),
            'sessionId' => trimPost('payment_id'),
            'payAmount' => '0',
            'adjustmentAmount' => '0',
            'memo' => '',
            'accountCode' => '',
            'followUp' => true,
            'followUpNote' => trimPost("FollowUpReason$CountRow"),
        ]);
        $Affected = true;
    }

    if ($Affected) {
        if (trimPost('type_name') != 'patient') {
            $ferow = sqlQuery('SELECT last_level_closed FROM form_encounter WHERE pid=? AND encounter=?', [
                trimPost('hidden_patient_code'),
                trimPost("HiddenEncounter$CountRow"),
            ]);
              //multiple charges can come.
            if ($ferow['last_level_closed'] < trimPost("HiddenIns$CountRow")) {
                //last_level_closed gets increased. unless a follow up is required.
                // in which case we'll allow secondary to be re setup to current setup.
                // just not advancing last closed.
                $tmp = ((!empty($_POST["Payment$CountRow"]) ? floatval($_POST["Payment$CountRow"]) : null) + (!empty($_POST["AdjAmount$CountRow"]) ? floatval($_POST["AdjAmount$CountRow"]) : null));
                if ((empty($_POST["FollowUp$CountRow"]) || ($_POST["FollowUp$CountRow"] != 'y')) && $tmp !== 0) {
                    sqlStatement('UPDATE form_encounter SET last_level_closed=? WHERE pid = ? AND encounter = ?', [
                        trimPost("HiddenIns$CountRow"),
                        trimPost('hidden_patient_code'),
                        trimPost("HiddenEncounter$CountRow"),
                    ]);
                }
                  //-----------------------------------
                  // Determine the next insurance level to be billed.
                $ferow = sqlQuery('SELECT date, last_level_closed FROM form_encounter WHERE pid=? AND encounter=?', [
                    trimPost('hidden_patient_code'),
                    trimPost("HiddenEncounter$CountRow"),
                ]);
                  $date_of_service = substr((string) $ferow['date'], 0, 10);
                  $new_payer_type = 0 + $ferow['last_level_closed'];
                if ($new_payer_type <= 3 && !empty($ferow['last_level_closed']) || $new_payer_type == 0) {
                    ++$new_payer_type;
                }

                  $new_payer_id = SLEOB::arGetPayerID(trimPost('hidden_patient_code'), $date_of_service, $new_payer_type);
                if ($new_payer_id > 0) {
                        SLEOB::arSetupSecondary(trimPost('hidden_patient_code'), trimPost("HiddenEncounter$CountRow"), 0);
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
/**
 * @param string $table
 * @param string $where
 */
function payment_row_delete($table, $where): void
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

            $logstring .= $key . "='" . addslashes((string) $value) . "'";
        }

        EventAuditLogger::getInstance()->newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$table: $logstring");
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
/**
 * @param string $table
 * @param string $set
 * @param string $where
 */
function payment_row_modify($table, $set, $where): void
{
    if (sqlQuery("SELECT * FROM " . escape_table_name($table) . " WHERE $where")) {
        EventAuditLogger::getInstance()->newEvent(
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
