<?php
/**
 * delete tool, for logging and removing patient data.
 *
 * Called from many different pages.
 *
 *  Copyright (C) 2005-2016 Rod Roark <rod@sunsetsystems.com>
 *  Copyright (C) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author Roberto Vasquez <robertogagliotta@gmail.com>
 * @link    http://www.open-emr.org
 */

use OpenEMR\Core\Header;

require_once('../globals.php');
require_once($GLOBALS['srcdir'].'/log.inc');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/sl_eob.inc.php');

 $patient     = $_REQUEST['patient'];
 $encounterid = $_REQUEST['encounterid'];
 $formid      = $_REQUEST['formid'];
 $issue       = $_REQUEST['issue'];
 $document    = $_REQUEST['document'];
 $payment     = $_REQUEST['payment'];
 $billing     = $_REQUEST['billing'];
 $transaction = $_REQUEST['transaction'];

 $info_msg = "";

 // Delete rows, with logging, for the specified table using the
 // specified WHERE clause.
 //
function row_delete($table, $where)
{
    $tres = sqlStatement("SELECT * FROM $table WHERE $where");
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

            $logstring .= $key . "= '" . $value . "' ";
        }

        newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$table: $logstring");
        ++$count;
    }

    if ($count) {
        $query = "DELETE FROM $table WHERE $where";
        if (!$GLOBALS['sql_string_no_show_screen']) {
            echo text($query) . "<br>\n";
        }

        sqlStatement($query);
    }
}

 // Deactivate rows, with logging, for the specified table using the
 // specified SET and WHERE clauses.
 //
function row_modify($table, $set, $where)
{
    if (sqlQuery("SELECT * FROM $table WHERE $where")) {
        newEvent("deactivate", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "$table: $where");
        $query = "UPDATE $table SET $set WHERE $where";
        if (!$GLOBALS['sql_string_no_show_screen']) {
            echo text($query) . "<br>\n";
        }

        sqlStatement($query);
    }
}

// We use this to put dashes, colons, etc. back into a timestamp.
//
function decorateString($fmt, $str)
{
    $res = '';
    while ($fmt) {
        $fc = substr($fmt, 0, 1);
        $fmt = substr($fmt, 1);
        if ($fc == '.') {
            $res .= substr($str, 0, 1);
            $str = substr($str, 1);
        } else {
            $res .= $fc;
        }
    }

    return $res;
}

// Delete and undo product sales for a given patient or visit.
// This is special because it has to replace the inventory.
//
function delete_drug_sales($patient_id, $encounter_id = 0)
{
    $where = $encounter_id ? "ds.encounter = '" . add_escape_custom($encounter_id) . "'" :
    "ds.pid = '" . add_escape_custom($patient_id) . "' AND ds.encounter != 0";
    sqlStatement("UPDATE drug_sales AS ds, drug_inventory AS di " .
    "SET di.on_hand = di.on_hand + ds.quantity " .
    "WHERE $where AND di.inventory_id = ds.inventory_id");
    if ($encounter_id) {
        row_delete("drug_sales", "encounter = '" . add_escape_custom($encounter_id) . "'");
    } else {
        row_delete("drug_sales", "pid = '" . add_escape_custom($patient_id) . "'");
    }
}

// Delete a form's data that is specific to that form.
//
function form_delete($formdir, $formid, $patient_id, $encounter_id)
{
    $formdir = ($formdir == 'newpatient') ? 'encounter' : $formdir;
    $formdir = ($formdir == 'newGroupEncounter') ? 'groups_encounter' : $formdir;
    if (substr($formdir, 0, 3) == 'LBF') {
        row_delete("lbf_data", "form_id = '" . add_escape_custom($formid) . "'");
        // Delete the visit's "source=visit" attributes that are not used by any other form.
        $where = "pid = '" . add_escape_custom($patient_id) . "' AND encounter = '" .
          add_escape_custom($encounter_id) . "' AND field_id NOT IN (" .
          "SELECT lo.field_id FROM forms AS f, layout_options AS lo WHERE " .
          "f.pid = '" . add_escape_custom($patient_id) . "' AND f.encounter = '" .
          add_escape_custom($encounter_id) . "' AND f.formdir LIKE 'LBF%' AND " .
          "f.deleted = 0 AND f.form_id != '" . add_escape_custom($formid) . "' AND " .
          "lo.form_id = f.formdir AND lo.source = 'E' AND lo.uor > 0)";
        // echo "<!-- $where -->\n"; // debugging
        row_delete("shared_attributes", $where);
    } else if ($formdir == 'procedure_order') {
        $tres = sqlStatement("SELECT procedure_report_id FROM procedure_report " .
        "WHERE procedure_order_id = ?", array($formid));
        while ($trow = sqlFetchArray($tres)) {
            $reportid = 0 + $trow['procedure_report_id'];
            row_delete("procedure_result", "procedure_report_id = '" . add_escape_custom($reportid) . "'");
        }

        row_delete("procedure_report", "procedure_order_id = '" . add_escape_custom($formid) . "'");
        row_delete("procedure_order_code", "procedure_order_id = '" . add_escape_custom($formid) . "'");
        row_delete("procedure_order", "procedure_order_id = '" . add_escape_custom($formid) . "'");
    } else if ($formdir == 'physical_exam') {
        row_delete("form_$formdir", "forms_id = '" . add_escape_custom($formid) . "'");
    } else {
        row_delete("form_$formdir", "id = '" . add_escape_custom($formid) . "'");
    }
}

// Delete a specified document including its associated relations and file.
//
function delete_document($document)
{
    $trow = sqlQuery("SELECT url, thumb_url, storagemethod, couch_docid, couch_revid FROM documents WHERE id = ?", array($document));
    $url = $trow['url'];
    $thumb_url = $trow['thumb_url'];
    row_delete("categories_to_documents", "document_id = '" . add_escape_custom($document) . "'");
    row_delete("documents", "id = '" . add_escape_custom($document) . "'");
    row_delete("gprelations", "type1 = 1 AND id1 = '" . add_escape_custom($document) . "'");

    switch ((int)$trow['storagemethod']) {
        //for hard disk store
        case 0:
            @unlink(substr($url, 7));

            if (!is_null($thumb_url)) {
                @unlink(substr($thumb_url, 7));
            }
            break;
        //for CouchDB store
        case 1:
            $couchDB = new CouchDB();
            $couchDB->DeleteDoc($GLOBALS['couchdb_dbase'], $trow['couch_docid'], $trow['couch_revid']);
            break;
    }
}
?>
<html>
<head>
    <?php Header::setupHeader('opener'); ?>
<title><?php echo xlt('Delete Patient, Encounter, Form, Issue, Document, Payment, Billing or Transaction'); ?></title>

<script language="javascript">
function submit_form()
{
document.deletefrm.submit();
}
// Java script function for closing the popup
function popup_close() {
    dlgclose();
}
</script>
</head>

<body class="body_top">
<?php
 // If the delete is confirmed...
 //
if ($_POST['form_submit']) {
    if ($patient) {
        if (!acl_check('admin', 'super') || !$GLOBALS['allow_pat_delete']) {
            die("Not authorized!");
        }

        row_modify("billing", "activity = 0", "pid = '" . add_escape_custom($patient) . "'");
        row_modify("pnotes", "deleted = 1", "pid = '" . add_escape_custom($patient) . "'");
       // row_modify("prescriptions" , "active = 0"  , "patient_id = '$patient'");
        row_delete("prescriptions", "patient_id = '" . add_escape_custom($patient) . "'");
        row_delete("claims", "patient_id = '" . add_escape_custom($patient) . "'");
        delete_drug_sales($patient);
        row_delete("payments", "pid = '" . add_escape_custom($patient) . "'");
        row_delete("ar_activity", "pid = '" . add_escape_custom($patient) . "'");
        row_delete("openemr_postcalendar_events", "pc_pid = '" . add_escape_custom($patient) . "'");
        row_delete("immunizations", "patient_id = '" . add_escape_custom($patient) . "'");
        row_delete("issue_encounter", "pid = '" . add_escape_custom($patient) . "'");
        row_delete("lists", "pid = '" . add_escape_custom($patient) . "'");
        row_delete("transactions", "pid = '" . add_escape_custom($patient) . "'");
        row_delete("employer_data", "pid = '" . add_escape_custom($patient) . "'");
        row_delete("history_data", "pid = '" . add_escape_custom($patient) . "'");
        row_delete("insurance_data", "pid = '" . add_escape_custom($patient) . "'");

        $res = sqlStatement("SELECT * FROM forms WHERE pid = ?", array($patient));
        while ($row = sqlFetchArray($res)) {
            form_delete($row['formdir'], $row['form_id'], $row['pid'], $row['encounter']);
        }

        row_delete("forms", "pid = '" . add_escape_custom($patient) . "'");

       // Delete all documents for the patient.
        $res = sqlStatement("SELECT id FROM documents WHERE foreign_id = ?", array($patient));
        while ($row = sqlFetchArray($res)) {
            delete_document($row['id']);
        }

        row_delete("patient_data", "pid = '" . add_escape_custom($patient) . "'");
    } else if ($encounterid) {
        if (!acl_check('admin', 'super')) {
            die("Not authorized!");
        }

        row_modify("billing", "activity = 0", "encounter = '" . add_escape_custom($encounterid) . "'");
        delete_drug_sales(0, $encounterid);
        row_delete("ar_activity", "encounter = '" . add_escape_custom($encounterid) . "'");
        row_delete("claims", "encounter_id = '" . add_escape_custom($encounterid) . "'");
        row_delete("issue_encounter", "encounter = '" . add_escape_custom($encounterid) . "'");
        $res = sqlStatement("SELECT * FROM forms WHERE encounter = ?", array($encounterid));
        while ($row = sqlFetchArray($res)) {
            form_delete($row['formdir'], $row['form_id'], $row['pid'], $row['encounter']);
        }

        row_delete("forms", "encounter = '" . add_escape_custom($encounterid) . "'");
    } else if ($formid) {
        if (!acl_check('admin', 'super')) {
            die("Not authorized!");
        }

        $row = sqlQuery("SELECT * FROM forms WHERE id = ?", array($formid));
        $formdir = $row['formdir'];
        if (! $formdir) {
            die("There is no form with id '" . text($formid) . "'");
        }
        form_delete($formdir, $row['form_id'], $row['pid'], $row['encounter']);
        row_delete("forms", "id = '" . add_escape_custom($formid) . "'");
    } else if ($issue) {
        if (!acl_check('admin', 'super')) {
            die("Not authorized!");
        }

        row_delete("issue_encounter", "list_id = '" . add_escape_custom($issue) ."'");
        row_delete("lists", "id = '" . add_escape_custom($issue) ."'");
    } else if ($document) {
        if (!acl_check('patients', 'docs_rm')) {
            die("Not authorized!");
        }

        delete_document($document);
    } else if ($payment) {
        if (!acl_check('admin', 'super')) {
            die("Not authorized!");
        }

        list($patient_id, $timestamp, $ref_id) = explode(".", $payment);
        // if (empty($ref_id)) $ref_id = -1;
        $timestamp = decorateString('....-..-.. ..:..:..', $timestamp);
        $payres = sqlStatement("SELECT * FROM payments WHERE " .
        "pid = ? AND dtime = ?", array($patient_id, $timestamp));
        while ($payrow = sqlFetchArray($payres)) {
            if ($payrow['encounter']) {
                $ref_id = -1;
                // The session ID passed in is useless. Look for the most recent
                // patient payment session with pay total matching pay amount and with
                // no adjustments. The resulting session ID may be 0 (no session) which
                // is why we start with -1.
                $tpmt = $payrow['amount1'] + $payrow['amount2'];
                $seres = sqlStatement("SELECT " .
                "SUM(pay_amount) AS pay_amount, session_id " .
                "FROM ar_activity WHERE " .
                "pid = ? AND " .
                "encounter = ? AND " .
                "payer_type = 0 AND " .
                "adj_amount = 0.00 " .
                "GROUP BY session_id ORDER BY session_id DESC", array($patient_id, $payrow['encounter']));
                while ($serow = sqlFetchArray($seres)) {
                    if (sprintf("%01.2f", $serow['adj_amount']) != 0.00) {
                        continue;
                    }

                    if (sprintf("%01.2f", $serow['pay_amount'] - $tpmt) == 0.00) {
                        $ref_id = $serow['session_id'];
                        break;
                    }
                }

                if ($ref_id == -1) {
                          die(xlt('Unable to match this payment in ar_activity') . ": " . text($tpmt));
                }

                // Delete the payment.
                row_delete(
                    "ar_activity",
                    "pid = '" . add_escape_custom($patient_id) . "' AND " .
                    "encounter = '" . add_escape_custom($payrow['encounter']) . "' AND " .
                    "payer_type = 0 AND " .
                    "pay_amount != 0.00 AND " .
                    "adj_amount = 0.00 AND " .
                    "session_id = '" . add_escape_custom($ref_id) . "'"
                );
                if ($ref_id) {
                        row_delete(
                            "ar_session",
                            "patient_id = '" . add_escape_custom($patient_id) ."' AND " .
                            "session_id = '" . add_escape_custom($ref_id) . "'"
                        );
                }
            } else {
                // Encounter is 0! Seems this happens for pre-payments.
                $tpmt = sprintf("%01.2f", $payrow['amount1'] + $payrow['amount2']);
                // Patched out 09/06/17- If this is prepayment can't see need for ar_activity when prepayments not stored there? In this case passed in session id is valid.
                // Was causing delete of wrong prepayment session in the case of delete from checkout undo and/or front receipt delete if payment happens to be same
                // amount of a previous prepayment. Much tested but look here if problems in postings.
                //
                /* row_delete("ar_session",
                 "patient_id = ' " . add_escape_custom($patient_id) . " ' AND " .
                 "payer_id = 0 AND " .
                 "reference = '" . add_escape_custom($payrow['source']) . "' AND " .
                 "pay_total = '" . add_escape_custom($tpmt) . "' AND " .
                 "(SELECT COUNT(*) FROM ar_activity where ar_activity.session_id = ar_session.session_id) = 0 " .
                 "ORDER BY session_id DESC LIMIT 1"); */

                row_delete("ar_session", "session_id = '" . add_escape_custom($ref_id) . "'");
            }

            row_delete("payments", "id = '" . add_escape_custom($payrow['id']) . "'");
        }
    } else if ($billing) {
        if (!acl_check('acct', 'disc')) {
            die("Not authorized!");
        }

        list($patient_id, $encounter_id) = explode(".", $billing);
        sqlStatement("DELETE FROM ar_activity WHERE " .
        "pid = ? AND encounter = ? ", array($patient_id, $encounter_id));
        sqlStatement("DELETE ar_session FROM ar_session LEFT JOIN " .
        "ar_activity ON ar_session.session_id = ar_activity.session_id " .
        "WHERE ar_activity.session_id IS NULL");
        row_modify(
            "billing",
            "activity = 0",
            "pid = '" . add_escape_custom($patient_id) . "'  AND " .
            "encounter = '" . add_escape_custom($encounter_id) . "' AND " .
            "code_type = 'COPAY' AND " .
            "activity = 1"
        );
        sqlStatement("UPDATE form_encounter SET last_level_billed = 0, " .
        "last_level_closed = 0, stmt_count = 0, last_stmt_date = NULL " .
        "WHERE pid = ? AND encounter = ?", array($patient_id, $encounter_id));
        sqlStatement("UPDATE drug_sales SET billed = 0 WHERE " .
        "pid = ? AND encounter = ?", array($patient_id, $encounter_id));
        updateClaim(true, $patient_id, $encounter_id, -1, -1, 1, 0, ''); // clears for rebilling
    } else if ($transaction) {
        if (!acl_check('admin', 'super')) {
            die("Not authorized!");
        }

        row_delete("transactions", "id = '" . add_escape_custom($transaction) . "'");
    } else {
        die("Nothing was recognized to delete!");
    }

    if (! $info_msg) {
        $info_msg = xl('Delete successful.');
    }

  // Close this window and tell our opener that it's done.
  // Not sure yet if the callback can be used universally.
    echo "<script language='JavaScript'>\n";
    if (!$encounterid) {
        if ($info_msg) {
            echo " alert('" . addslashes($info_msg) . "');\n";
        }
        echo " dlgclose('imdeleted',false);\n";
    } else {
        if ($GLOBALS['sql_string_no_show_screen']) {
            echo " dlgclose('imdeleted', $encounterid);\n";
        } else { // this allows dialog to stay open then close with button or X.
            echo " opener.dlgSetCallBack('imdeleted', $encounterid);\n";
        }
    }
    echo "</script></body></html>\n";
    exit();
}
?>

<form method='post' name="deletefrm" action='deleter.php?patient=<?php echo attr($patient) ?>&encounterid=<?php echo attr($encounterid) ?>&formid=<?php echo attr($formid) ?>&issue=<?php echo attr($issue) ?>&document=<?php echo attr($document) ?>&payment=<?php echo attr($payment) ?>&billing=<?php echo attr($billing) ?>&transaction=<?php echo attr($transaction) ?>' onsubmit="javascript:alert('1');document.deleform.submit();">

<p class="lead">&nbsp;<br><?php echo xlt('Do you really want to delete'); ?>

<?php
if ($patient) {
    echo xlt('patient') . " " . text($patient);
} else if ($encounterid) {
    echo xlt('encounter') . " " . text($encounterid);
} else if ($formid) {
    echo xlt('form') . " " . text($formid);
} else if ($issue) {
    echo xlt('issue') . " " .text($issue);
} else if ($document) {
    echo xlt('document') . " " . text($document);
} else if ($payment) {
    echo xlt('payment') . " " .text($payment);
} else if ($billing) {
    echo xlt('invoice') . " " . text($billing);
} else if ($transaction) {
    echo xlt('transaction') . " " . text($transaction);
}
?> <?php echo xlt('and all subordinate data? This action will be logged'); ?>!</p>
<div class="btn-group">
    <a href="#" onclick="submit_form()" class="btn btn-lg btn-save btn-default"><?php echo xlt('Yes, Delete and Log'); ?></a>
    <a href='#' class="btn btn-lg btn-link btn-cancel" onclick="popup_close();"><?php echo xlt('No, Cancel');?></a>
</div>
<input type='hidden' name='form_submit' value='<?php echo xla('Yes, Delete and Log'); ?>'/>
</form>
</body>
</html>
