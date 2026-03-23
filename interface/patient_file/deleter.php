<?php

/**
 * delete tool, for logging and removing patient data.
 *
 * Called from many different pages.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2005-2020 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

if (!empty($_GET)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"] ?? '', session: $session)) {
        CsrfUtils::csrfNotVerified();
    }
}

$patient     = filter_input(INPUT_GET, 'patient', FILTER_VALIDATE_INT) ?: 0;
$encounterid = filter_input(INPUT_GET, 'encounterid', FILTER_VALIDATE_INT) ?: 0;
$formid      = filter_input(INPUT_GET, 'formid', FILTER_VALIDATE_INT) ?: 0;
$issue       = filter_input(INPUT_GET, 'issue') ?: '';
$document    = filter_input(INPUT_GET, 'document', FILTER_VALIDATE_INT) ?: 0;
$payment     = filter_input(INPUT_GET, 'payment') ?: '';
$billing     = filter_input(INPUT_GET, 'billing') ?: '';
$transaction = filter_input(INPUT_GET, 'transaction', FILTER_VALIDATE_INT) ?: 0;

$info_msg = "";

/**
 * Delete rows, with logging, for the specified table using the
 * specified WHERE clause.
 *
 * @param list<scalar> $binds
 */
function deleter_row_delete(string $table, string $where, array $binds = []): void
{
    $session = SessionWrapperFactory::getInstance()->getActiveSession();

    $tres = QueryUtils::sqlStatementThrowException("SELECT * FROM " . escape_table_name($table) . " WHERE $where", $binds);
    $count = 0;
    while ($trow = QueryUtils::fetchArrayFromResultSet($tres)) {
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

        EventAuditLogger::getInstance()->newEvent("delete", $session->get('authUser'), $session->get('authProvider'), 1, "$table: $logstring");
        ++$count;
    }

    if ($count) {
        $query = "DELETE FROM " . escape_table_name($table) . " WHERE $where";
        if (!OEGlobalsBag::getInstance()->getBoolean('sql_string_no_show_screen')) {
            echo text($query) . "<br />\n";
        }

        QueryUtils::sqlStatementThrowException($query, $binds);
    }
}

/**
 * Deactivate rows, with logging, for the specified table using the
 * specified SET and WHERE clauses.
 *
 * @param list<scalar> $binds
 */
function deleter_row_modify(string $table, string $set, string $where, array $binds = []): void
{
    $session = SessionWrapperFactory::getInstance()->getActiveSession();

    if (QueryUtils::querySingleRow("SELECT * FROM " . escape_table_name($table) . " WHERE $where", $binds)) {
        EventAuditLogger::getInstance()->newEvent("deactivate", $session->get('authUser'), $session->get('authProvider'), 1, "$table: $where");
        $query = "UPDATE " . escape_table_name($table) . " SET $set WHERE $where";
        if (!OEGlobalsBag::getInstance()->getBoolean('sql_string_no_show_screen')) {
            echo text($query) . "<br />\n";
        }

        QueryUtils::sqlStatementThrowException($query, $binds);
    }
}

// Delete and undo product sales for a given patient or visit.
// This is special because it has to replace the inventory.
//
function delete_drug_sales($patient_id, $encounter_id = 0): void
{
    if ($encounter_id) {
        QueryUtils::sqlStatementThrowException(
            "UPDATE drug_sales AS ds, drug_inventory AS di " .
            "SET di.on_hand = di.on_hand + ds.quantity " .
            "WHERE ds.encounter = ? AND di.inventory_id = ds.inventory_id",
            [$encounter_id]
        );
        deleter_row_delete("drug_sales", "encounter = ?", [$encounter_id]);
    } else {
        QueryUtils::sqlStatementThrowException(
            "UPDATE drug_sales AS ds, drug_inventory AS di " .
            "SET di.on_hand = di.on_hand + ds.quantity " .
            "WHERE ds.pid = ? AND ds.encounter != 0 AND di.inventory_id = ds.inventory_id",
            [$patient_id]
        );
        deleter_row_delete("drug_sales", "pid = ?", [$patient_id]);
    }
}

// Delete a form's data that is specific to that form.
//
function form_delete($formdir, $formid, $patient_id, $encounter_id): void
{
    $formdir = ($formdir == 'newpatient') ? 'encounter' : $formdir;
    $formdir = ($formdir == 'newGroupEncounter') ? 'groups_encounter' : $formdir;
    if (str_starts_with((string) $formdir, 'LBF')) {
        deleter_row_delete("lbf_data", "form_id = ?", [$formid]);
        // Delete the visit's "source=visit" attributes that are not used by any other form.
        $where = "pid = ? AND encounter = ? AND field_id NOT IN (" .
          "SELECT lo.field_id FROM forms AS f, layout_options AS lo WHERE " .
          "f.pid = ? AND f.encounter = ? AND f.formdir LIKE 'LBF%' AND " .
          "f.deleted = 0 AND f.form_id != ? AND " .
          "lo.form_id = f.formdir AND lo.source = 'E' AND lo.uor > 0)";
        $binds = [$patient_id, $encounter_id, $patient_id, $encounter_id, $formid];
        // echo "<!-- $where -->\n"; // debugging
        deleter_row_delete("shared_attributes", $where, $binds);
    } elseif ($formdir == 'procedure_order') {
        $tres = QueryUtils::sqlStatementThrowException("SELECT procedure_report_id FROM procedure_report " .
        "WHERE procedure_order_id = ?", [$formid]);
        while ($trow = QueryUtils::fetchArrayFromResultSet($tres)) {
            $reportid = (int)$trow['procedure_report_id'];
            deleter_row_delete("procedure_result", "procedure_report_id = ?", [$reportid]);
        }

        deleter_row_delete("procedure_report", "procedure_order_id = ?", [$formid]);
        deleter_row_delete("procedure_order_code", "procedure_order_id = ?", [$formid]);
        deleter_row_delete("procedure_order", "procedure_order_id = ?", [$formid]);
    } elseif ($formdir == 'physical_exam') {
        deleter_row_delete("form_$formdir", "forms_id = ?", [$formid]);
    } elseif ($formdir == 'eye_mag') {
        $tables = ['form_eye_base','form_eye_hpi','form_eye_ros','form_eye_vitals',
            'form_eye_acuity','form_eye_refraction','form_eye_biometrics',
            'form_eye_external', 'form_eye_antseg','form_eye_postseg',
            'form_eye_neuro','form_eye_locking','form_eye_mag_orders'];
        foreach ($tables as $table_name) {
            deleter_row_delete($table_name, "id = ?", [$formid]);
        }
        deleter_row_delete("form_eye_mag_impplan", "form_id = ?", [$formid]);
        deleter_row_delete("form_eye_mag_wearing", "FORM_ID = ?", [$formid]);
    } else {
        deleter_row_delete("form_$formdir", "id = ?", [$formid]);
    }
}

// Delete a specified document including its associated relations.
//  Note the specific file is not deleted (instead flagged as deleted), since required to keep file for
//   ONC certification purposes.
//
function delete_document($document): void
{
    QueryUtils::sqlStatementThrowException("UPDATE `documents` SET `deleted` = 1 WHERE id = ?", [$document]);
    deleter_row_delete("categories_to_documents", "document_id = ?", [$document]);
    deleter_row_delete("gprelations", "type1 = 1 AND id1 = ?", [$document]);
}
?>
<html>
<head>
    <?php Header::setupHeader('opener'); ?>
<title><?php echo xlt('Delete Patient, Encounter, Form, Issue, Document, Payment, Billing or Transaction'); ?></title>

<script>
function submit_form() {
    top.restoreSession();
    document.deletefrm.submit();
}

// Javascript function for closing the popup
function popup_close() {
    dlgclose();
}
</script>
</head>

<body>
    <div class="container mt-3">
        <?php
        // If the delete is confirmed...
        //
        if (!empty($_POST['form_submit'])) {
            if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], session: $session)) {
                CsrfUtils::csrfNotVerified();
            }

            if ($patient) {
                if (!AclMain::aclCheckCore('admin', 'super') || !OEGlobalsBag::getInstance()->getBoolean('allow_pat_delete')) {
                    AccessDeniedHelper::deny('Unauthorized patient deletion attempt');
                }

                deleter_row_modify("billing", "activity = 0", "pid = ?", [$patient]);
                deleter_row_modify("pnotes", "deleted = 1", "pid = ?", [$patient]);
                deleter_row_delete("prescriptions", "patient_id = ?", [$patient]);
                deleter_row_delete("claims", "patient_id = ?", [$patient]);
                delete_drug_sales($patient);
                deleter_row_delete("payments", "pid = ?", [$patient]);
                deleter_row_modify("ar_activity", "deleted = NOW()", "pid = ? AND deleted IS NULL", [$patient]);
                deleter_row_delete("openemr_postcalendar_events", "pc_pid = ?", [$patient]);
                deleter_row_delete("immunizations", "patient_id = ?", [$patient]);
                deleter_row_delete("issue_encounter", "pid = ?", [$patient]);
                deleter_row_delete("lists", "pid = ?", [$patient]);
                deleter_row_delete("transactions", "pid = ?", [$patient]);
                deleter_row_delete("employer_data", "pid = ?", [$patient]);
                deleter_row_delete("history_data", "pid = ?", [$patient]);
                deleter_row_delete("insurance_data", "pid = ?", [$patient]);
                deleter_row_delete("patient_history", "pid = ?", [$patient]);

                $res = QueryUtils::sqlStatementThrowException("SELECT * FROM forms WHERE pid = ?", [$patient]);
                while ($row = QueryUtils::fetchArrayFromResultSet($res)) {
                    deleter_row_delete("forms", "pid = ? AND form_id = ?", [$row['pid'], $row['form_id']]);
                    deleter_row_delete("form_encounter", "pid = ?", [$row['pid']]);
                }

                // Delete all documents for the patient.
                $res = QueryUtils::sqlStatementThrowException("SELECT id FROM documents WHERE foreign_id = ? AND deleted = 0", [$patient]);
                while ($row = QueryUtils::fetchArrayFromResultSet($res)) {
                    delete_document($row['id']);
                }

                deleter_row_delete("patient_data", "pid = ?", [$patient]);
            } elseif ($encounterid) {
                if (!AclMain::aclCheckCore('admin', 'super')) {
                    AccessDeniedHelper::deny('Unauthorized encounter deletion attempt');
                }

                deleter_row_modify("billing", "activity = 0", "encounter = ?", [$encounterid]);
                delete_drug_sales(0, $encounterid);
                deleter_row_modify("ar_activity", "deleted = NOW()", "encounter = ? AND deleted IS NULL", [$encounterid]);
                deleter_row_delete("claims", "encounter_id = ?", [$encounterid]);
                deleter_row_delete("issue_encounter", "encounter = ?", [$encounterid]);
                $res = QueryUtils::sqlStatementThrowException("SELECT * FROM forms WHERE encounter = ?", [$encounterid]);
                while ($row = QueryUtils::fetchArrayFromResultSet($res)) {
                    form_delete($row['formdir'], $row['form_id'], $row['pid'], $row['encounter']);
                }

                deleter_row_delete("forms", "encounter = ?", [$encounterid]);
            } elseif ($formid) {
                if (!AclMain::aclCheckCore('admin', 'super')) {
                    AccessDeniedHelper::deny('Unauthorized form deletion attempt');
                }

                $row = QueryUtils::querySingleRow("SELECT * FROM forms WHERE id = ?", [$formid]);
                $formdir = $row['formdir'];
                if (! $formdir) {
                    throw new \RuntimeException("There is no form with id '" . text($formid) . "'");
                }
                form_delete($formdir, $row['form_id'], $row['pid'], $row['encounter']);
                deleter_row_delete("forms", "id = ?", [$formid]);
            } elseif ($issue) {
                if (!AclMain::aclCheckCore('admin', 'super')) {
                    AccessDeniedHelper::deny('Unauthorized issue deletion attempt');
                }

                $ids = explode(",", (string) $issue);
                foreach ($ids as $id) {
                    $id = (int) $id;
                    deleter_row_delete("issue_encounter", "list_id = ?", [$id]);
                    deleter_row_delete("lists_medication", "list_id = ?", [$id]);
                    deleter_row_delete("lists", "id = ?", [$id]);
                }
            } elseif ($document) {
                if (!AclMain::aclCheckCore('patients', 'docs_rm')) {
                    AccessDeniedHelper::deny('Unauthorized document deletion attempt');
                }

                delete_document($document);
            } elseif ($payment) {
                if (!AclMain::aclCheckCore('admin', 'super')) {
                    // allow biller to delete misapplied payments
                    if (!AclMain::aclCheckCore('acct', 'bill')) {
                        AccessDeniedHelper::deny('Unauthorized payment deletion attempt');
                    }
                }

                [$patient_id, $timestamp, $ref_id] = explode(".", (string) $payment);
                $patient_id = (int) $patient_id;
                $ref_id = (int) $ref_id;
                // if (empty($ref_id)) $ref_id = -1;
                $timestamp = decorateString('....-..-.. ..:..:..', $timestamp);
                $payres = QueryUtils::sqlStatementThrowException("SELECT * FROM payments WHERE " .
                "pid = ? AND dtime = ?", [$patient_id, $timestamp]);
                while ($payrow = QueryUtils::fetchArrayFromResultSet($payres)) {
                    if ($payrow['encounter']) {
                        $ref_id = -1;
                        // The session ID passed in is useless. Look for the most recent
                        // patient payment session with pay total matching pay amount and with
                        // no adjustments. The resulting session ID may be 0 (no session) which
                        // is why we start with -1.
                        $tpmt = $payrow['amount1'] + $payrow['amount2'];
                        $seres = QueryUtils::sqlStatementThrowException("SELECT " .
                        "SUM(pay_amount) AS pay_amount, session_id " .
                        "FROM ar_activity WHERE " .
                        "pid = ? AND " .
                        "encounter = ? AND " .
                        "deleted IS NULL AND " .
                        "payer_type = 0 AND " .
                        "adj_amount = 0.00 " .
                        "GROUP BY session_id ORDER BY session_id DESC", [$patient_id, $payrow['encounter']]);
                        while ($serow = QueryUtils::fetchArrayFromResultSet($seres)) {
                            if (sprintf("%01.2f", $serow['pay_amount'] - $tpmt) == 0.00) {
                                $ref_id = $serow['session_id'];
                                break;
                            }
                        }

                        if ($ref_id == -1) {
                                throw new \RuntimeException(xlt('Unable to match this payment in ar_activity') . ": " . text($tpmt));
                        }

                        // Delete the payment.
                        deleter_row_modify(
                            "ar_activity",
                            "deleted = NOW()",
                            "pid = ? AND " .
                            "encounter = ? AND " .
                            "deleted IS NULL AND " .
                            "payer_type = 0 AND " .
                            "pay_amount != 0.00 AND " .
                            "adj_amount = 0.00 AND " .
                            "session_id = ?",
                            [$patient_id, $payrow['encounter'], $ref_id]
                        );
                        if ($ref_id) {
                            deleter_row_delete(
                                "ar_session",
                                "patient_id = ? AND " .
                                "session_id = ?",
                                [$patient_id, $ref_id]
                            );
                        }
                    } else {
                        // Encounter is 0! Seems this happens for pre-payments.
                        $tpmt = sprintf("%01.2f", $payrow['amount1'] + $payrow['amount2']);
                        // Patched out 09/06/17- If this is prepayment can't see need for ar_activity when prepayments not stored there? In this case passed in session id is valid.
                        // Was causing delete of wrong prepayment session in the case of delete from checkout undo and/or front receipt delete if payment happens to be same
                        // amount of a previous prepayment. Much tested but look here if problems in postings.
                        //
                        /* deleter_row_delete("ar_session",
                        "patient_id = ? AND " .
                        "payer_id = 0 AND " .
                        "reference = ? AND " .
                        "pay_total = ? AND " .
                        "(SELECT COUNT(*) FROM ar_activity where ar_activity.session_id = ar_session.session_id) = 0 " .
                        "ORDER BY session_id DESC LIMIT 1", [$patient_id, $payrow['source'], $tpmt]); */

                        deleter_row_delete("ar_session", "session_id = ?", [$ref_id]);
                    }

                    deleter_row_delete("payments", "id = ?", [$payrow['id']]);
                }
            } elseif ($billing) {
                if (!AclMain::aclCheckCore('acct', 'disc')) {
                    AccessDeniedHelper::deny('Unauthorized billing deletion attempt');
                }

                [$patient_id, $encounter_id] = explode(".", (string) $billing);
                $patient_id = (int) $patient_id;
                $encounter_id = (int) $encounter_id;

                deleter_row_modify(
                    "ar_activity",
                    "deleted = NOW()",
                    "pid = ? AND encounter = ? AND deleted IS NULL",
                    [$patient_id, $encounter_id]
                );

                // Looks like this deletes all ar_session rows that have no matching ar_activity rows.
                QueryUtils::sqlStatementThrowException(
                    "DELETE ar_session FROM ar_session LEFT JOIN " .
                    "ar_activity ON ar_session.session_id = ar_activity.session_id AND ar_activity.deleted IS NULL " .
                    "WHERE ar_activity.session_id IS NULL"
                );

                deleter_row_modify(
                    "billing",
                    "activity = 0",
                    "pid = ? AND " .
                    "encounter = ? AND " .
                    "code_type = 'COPAY' AND " .
                    "activity = 1",
                    [$patient_id, $encounter_id]
                );
                QueryUtils::sqlStatementThrowException("UPDATE form_encounter SET last_level_billed = 0, " .
                "last_level_closed = 0, stmt_count = 0, last_stmt_date = NULL " .
                "WHERE pid = ? AND encounter = ?", [$patient_id, $encounter_id]);
                QueryUtils::sqlStatementThrowException("UPDATE drug_sales SET billed = 0 WHERE " .
                "pid = ? AND encounter = ?", [$patient_id, $encounter_id]);
                BillingUtilities::updateClaim(true, $patient_id, $encounter_id, -1, -1, 1, 0, ''); // clears for rebilling
            } elseif ($transaction) {
                if (!AclMain::aclCheckCore('admin', 'super')) {
                    AccessDeniedHelper::deny('Unauthorized transaction deletion attempt');
                }

                deleter_row_delete("transactions", "id = ?", [$transaction]);
            } else {
                throw new \RuntimeException("Nothing was recognized to delete!");
            }

            if (! $info_msg) {
                $info_msg = xl('Delete successful.');
            }

        // Close this window and tell our opener that it's done.
        // Not sure yet if the callback can be used universally.
            echo "<script>\n";
            if (!$encounterid) {
                if ($info_msg) {
                    echo "let message = " . js_escape($info_msg) . ";
                    (async (message, time) => {
                    await asyncAlertMsg(message, time, 'success', 'lg');
                    })(message, 2000)
                    .then(res => {";
                    // auto close on msg timeout with just enough time to show success or errors.
                    if (OEGlobalsBag::getInstance()->getBoolean('sql_string_no_show_screen')) {
                        echo "dlgclose();";
                    }
                    echo "});"; // close function.
                    // any close will call below.
                    echo " opener.dlgSetCallBack('imdeleted', false);\n";
                } else {
                    echo " dlgclose('imdeleted', false);\n";
                }
            } else {
                if (OEGlobalsBag::getInstance()->getBoolean('sql_string_no_show_screen')) {
                    echo " dlgclose('imdeleted', " . js_escape($encounterid) . ");\n";
                } else { // this allows dialog to stay open then close with button or X.
                    echo " opener.dlgSetCallBack('imdeleted', " . js_escape($encounterid) . ");\n";
                }
            }
            echo "</script></body></html>\n";
            exit();
        }
        ?>

        <form method='post' name="deletefrm" action='deleter.php?patient=<?php echo $patient ?>&encounterid=<?php echo $encounterid ?>&formid=<?php echo $formid ?>&issue=<?php echo attr_url($issue) ?>&document=<?php echo $document ?>&payment=<?php echo attr_url($payment) ?>&billing=<?php echo attr_url($billing) ?>&transaction=<?php echo $transaction; ?>&csrf_token_form=<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>'>
            <input type="hidden" name="csrf_token_form"
                value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
            <p>
            <?php
            $type = '';
            $id = '';
            if ($patient) {
                $id = $patient;
                $type = 'patient';
            } elseif ($encounterid) {
                $id = $encounterid;
                $type = 'encounter';
            } elseif ($formid) {
                $id = $formid;
                $type = 'form';
            } elseif ($issue) {
                $id = $issue;
                $type = ('issue');
            } elseif ($document) {
                $id = $document;
                $type = 'document';
            } elseif ($payment) {
                $id = $payment;
                $type = 'payment';
            } elseif ($billing) {
                $id = $billing;
                $type = 'invoice';
            } elseif ($transaction) {
                $id = $transaction;
                $type = 'transaction';
            }

            $ids = explode(",", (string) $id);
            if (count($ids) > 1) {
                $type .= 's';
            }

            $msg = xl("You have selected to delete") . ' ' . count($ids) . ' ' . xl($type) . ". " . xl("Are you sure you want to continue?");
            echo text($msg);
            ?>
            </p>
            <div class="btn-group">
                <button onclick="submit_form()" class="btn btn-sm btn-primary mr-2"><?php echo xlt('Yes'); ?></button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="popup_close();"><?php echo xlt('No');?></button>
            </div>
            <input type='hidden' name='form_submit' value='delete'/>
        </form>
    </div>
</body>
</html>
