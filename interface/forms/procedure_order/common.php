<?php

/**
 * Encounter form for entering procedure orders
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2010-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2025 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/options.inc.php");
require_once(__DIR__ . "/../../orders/qoe.inc.php");
require_once(__DIR__ . "/../../../custom/code_types.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\ReasonStatusCodes;
use OpenEMR\Common\Orders\Hl7OrderGenerationException;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\Header;
use OpenEMR\Events\Services\DornLabEvent;
use OpenEMR\Events\Services\QuestLabTransmitEvent;
use Symfony\Component\EventDispatcher\EventDispatcher;

if (!$encounter) { // comes from globals.php
    die("Internal error: we do not seem to be in an encounter!");
}
/**
 * @var EventDispatcher
 */
$ed = $GLOBALS['kernel']->getEventDispatcher();

// Defaults for new orders.
$provider_id = getProviderIdOfEncounter($encounter);
$row = [
    'provider_id' => $provider_id,
    'date_ordered' => date('Y-m-d'),
];

if ($_POST['bn_save_ereq'] ?? null) { //labcorp
    $_POST['bn_xmit'] = "transmit";
}

$patient = sqlQueryNoLog("SELECT * FROM `patient_data` WHERE `pid` = ?", [$pid]);

global $gbl_lab, $gbl_lab_title, $gbl_client_acct;
$eReqForm = '';
function saveEreq($pid, $form_id, $mpdfData)
{
    $category = sqlQuery("SELECT id FROM categories WHERE name LIKE ?", ["LabCorp"]);
    if (!$category['id']) {
        $category = sqlQuery("SELECT id FROM categories WHERE name LIKE ?", ['Lab Report']);
    }
    $unique = date('y-m-d-His', time());
    $filename = "ereq_" . $unique . "_order_" . $form_id . ".pdf";
    $d = new Document();
    $good = $d->createDocument($pid, $category['id'], $filename, "application/pdf", $mpdfData);
    if (!empty($good)) {
        return $good;
    }
    $unique = date('y-m-d-H:i:s', time());
    $documentationOf = "$unique";
    sqlStatement(
        "UPDATE documents SET documentationOf = ?, list_id = ? WHERE id = ?",
        [$documentationOf, $form_id, $d->id]
    );

    return $good;
}

function isDornLab($ppid): bool
{
    $sql = "SHOW TABLES LIKE 'mod_dorn_routes'";
    $result = sqlQuery($sql);
    if ($result === false) {
        return false;
    }

    $sql = "SELECT 1 FROM mod_dorn_routes WHERE ppid = ?";
    $dornRecord = sqlQuery($sql, [$ppid]);
    if ($dornRecord !== false) {
        return true;
    }
    return false;
}

function get_lab_name($id): string
{
    global $gbl_lab_title, $gbl_lab, $gbl_client_acct, $gbl_use_codes;
    if (empty($id)) {
        $id = 1;
    }
    $tmp = sqlQuery("SELECT name, send_fac_id as clientid, npi FROM procedure_providers Where ppid = ?", [$id]);
    $gbl_lab = $tmp['name'] ?? '';
    $gbl_lab = stripos($tmp['name'] ?? '', 'quest') !== false ? 'quest' : $gbl_lab;
    $gbl_lab = stripos($tmp['name'] ?? '', 'labcorp') !== false ? 'labcorp' : $gbl_lab;
    $gbl_lab = stripos($tmp['name'] ?? '', 'clarity') !== false ? 'clarity' : $gbl_lab;
    $gbl_lab_title = trim($tmp['name'] ?? '');
    $gbl_client_acct = trim($tmp['clientid'] ?? '');
    if (empty($gbl_lab)) {
        $gbl_lab = 'missingName';
    }
    return $gbl_lab;
}

if (!function_exists('ucname')) {
    function ucname($string): string
    {
        $string = ucwords(strtolower((string) $string));
        foreach (['-', '\''] as $delimiter) {
            if (str_contains($string, $delimiter)) {
                $string = implode($delimiter, array_map(ucfirst(...), explode($delimiter, $string)));
            }
        }
        return $string;
    }
}

function cbvalue($cbname): string
{
    return $_POST[$cbname] ? '1' : '0';
}

function cbinput($name, $colname)
{
    global $row;
    $ret = "<input type='checkbox' name='" . attr($name) . "' value='1'";
    if ($row[$colname]) {
        $ret .= " checked";
    }
    $ret .= " />";
    return $ret;
}

function cbcell($name, $desc, $colname): string
{
    return "<td width='25%' nowrap>" . cbinput($name, $colname) . text($desc) . "</td>\n";
}

function QuotedOrNull($fld)
{
    if (empty($fld)) {
        return null;
    }

    return $fld;
}

function getListOptions($list_id, $fieldnames = ['option_id', 'title', 'seq']): array
{
    $output = [];
    $query = sqlStatement("SELECT " . implode(',', $fieldnames) . " FROM list_options where list_id = ? AND activity = 1 order by seq", [$list_id]);
    while ($ll = sqlFetchArray($query)) {
        foreach ($fieldnames as $val) {
            $output[$ll['option_id']][$val] = $ll[$val];
        }
    }
    return $output;
}

function normalizeDirectoryName(string $input): string
{
    $normalized = $input;
    $normalized = str_replace(' ', '_', $normalized);
    $normalized = str_replace(['&', '+'], 'and', $normalized);
    $normalized = preg_replace('/[^A-Za-z0-9_-]/', '', $normalized);
    $normalized = preg_replace('/_+/', '_', (string) $normalized);
    $normalized = trim((string) $normalized, '_-');
    $normalized = strtolower($normalized);

    return $normalized;
}

// do not change from $_REQUEST.
$formid = (int)($_REQUEST['id'] ?? 0);

$reload_url = $rootdir . '/patient_file/encounter/view_form.php?formname=procedure_order&id=' . urlencode($formid);
$req_url = $GLOBALS['web_root'] . '/controller.php?document&retrieve&patient_id=' . urlencode((string) $pid) . '&document_id=';
$reqStr = "";

// If Save or Transmit was clicked, save the info.
if (($_POST['bn_save'] ?? null) || !empty($_POST['bn_xmit']) || !empty($_POST['bn_save_exit'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    $ppid = (int)($_POST['form_lab_id'] ?? null);
    if (get_lab_name($ppid) === 'labcorp') {
        if (!empty($_POST['form_account_facility'])) {
            $location = sqlQueryNoLog("SELECT f.id, f.facility_code, f.name FROM facility as f " .
                "WHERE f.id = ?", [$_POST['form_account_facility']]);
            $_POST['form_account'] = $location['facility_code'];
        } else {
            $_POST['form_account'] = '';
        }
    }

    $sets =
        "date_ordered = ?, " .
        "provider_id = ?, " .
        "lab_id = ?, " .
        "date_collected = ?, " .
        "order_priority = ?, " .
        "order_status = ?, " .
        "billing_type = ?, " .
        "order_psc = ?, " .
        "specimen_fasting = ?, " .
        "clinical_hx = ?, " .
        "patient_instructions = ?, " .
        "patient_id = ?, " .
        "encounter_id = ?, " .
        "history_order = ?, " .
        "order_abn = ?, " .
        "order_diagnosis = ?, " .
        "account = ?, " .
        "account_facility = ?, " .
        "collector_id = ?, " .
        "procedure_order_type = ?, " .
        // NEW US Core 8.0 fields
        "order_intent = ?, " .
        "scheduled_date = ?, " .
        "scheduled_start = ?, " .
        "scheduled_end = ?, " .
        "performer_type = ?, " .
        "location_id = ?";

    // REPLACE THE $set_array variable with this updated version:
    $set_array = [
        QuotedOrNull($_POST['form_date_ordered']),
        (int)$_POST['form_provider_id'],
        $ppid,
        QuotedOrNull($_POST['form_date_collected']),
        $_POST['form_order_priority'],
        $_POST['form_order_status'],
        $_POST['form_billing_type'],
        $_POST['form_order_psc'],
        $_POST['form_specimen_fasting'] ?? '',
        trim((string) $_POST['form_clinical_hx']),
        trim((string) $_POST['form_patient_instructions']),
        $pid,
        $encounter,
        trim((string) $_POST['form_history_order']),
        trim((string) $_POST['form_order_abn']),
        trim((string) $_POST['form_order_diagnosis']),
        trim((string) $_POST['form_account']),
        (int)$_POST['form_account_facility'],
        (int)$_POST['form_collector_id'],
        trim((string) $_POST['procedure_type_names']),
        // NEW US Core 8.0 fields
        trim($_POST['form_order_intent'] ?? 'order'),
        QuotedOrNull($_POST['form_scheduled_date']),
        QuotedOrNull($_POST['form_scheduled_start']),
        QuotedOrNull($_POST['form_scheduled_end']),
        trim($_POST['form_performer_type'] ?? ''),
        (int)($_POST['form_location_id'] ?? 0),
    ];

    require_once(__DIR__ . "/procedure_order_save_functions.php");

    if ($formid) {
        $query = "UPDATE procedure_order SET $sets WHERE procedure_order_id = ?";
        $set_array_temp = $set_array;
        $set_array_temp[] = $formid;
        sqlStatement($query, $set_array_temp);
        $gbl_lab = get_lab_name($ppid);
        $order_date = oeFormatShortDate($_POST['form_date_ordered'] ?? '');
        $tmp = $_POST['procedure_type_names'] ?: $formid;
        $lab_title = $gbl_lab_title . "-$tmp-$formid-$order_date";
        $query = "UPDATE forms SET form_name = ? WHERE encounter = ? AND form_id = ? AND formdir = ?";
        sqlStatement($query, [$lab_title, $encounter, $formid, 'procedure_order']);
    } else {
        $query = "INSERT INTO procedure_order SET $sets";
        $formid = sqlInsert($query, $set_array);
        UuidRegistry::createMissingUuidsForTables(['procedure_order']);
        $gbl_lab = get_lab_name($ppid);
        $order_date = oeFormatShortDate($_POST['form_date_ordered'] ?? '');
        $tmp = $_POST['procedure_type_names'] ?: $formid;
        $lab_title = $gbl_lab_title . "-$tmp-$formid-$order_date";
        addForm($encounter, $lab_title, $formid, "procedure_order", $pid, $userauthorized);
        $mode = 'update';
        $viewmode = true;
    }

    $lab_name = normalizeDirectoryName(get_lab_name($ppid ?? 0));
    $log_file = $GLOBALS["OE_SITE_DIR"] . "/documents/labs/" . check_file_dir_name($lab_name) . "/logs/" . check_file_dir_name($formid) . "_order_log.log";
    $order_log = $_POST['order_log'] ?? '';
    if ($order_log) {
        file_put_contents($log_file, $order_log);
    }

    sqlStatement("DELETE FROM procedure_answers WHERE procedure_order_id = ?", [$formid]);
    saveProcedureOrderCodes($formid, $_POST);

    if (isset($_POST['bn_save_exit'])) {
        formHeader("Redirecting....");
        if ($alertmsg ?? '') {
            $msg = xl('Transmit failed') . ': ' . $alertmsg;
            echo "\n<script>alert(" . js_escape($msg) . ")</script>\n";
        }
        formJump();
        formFooter();
        exit;
    }

    $alertmsg = '';
    $isDorn = isDornLab($ppid) ?? false;

    if (!empty($_POST['bn_xmit'])) {
        // Validate, log and send order. Sets up documents and requisition buttons
        $gbl_lab = get_lab_name($ppid);
        $hl7 = '';
        $order_data = '';
        if ($_POST['form_provider_id'] + 0 < 1) {
            $order_data .= "\n" . xlt("Ordering Provider is required but not selected!");
        }
        $diag_flag = 0;
        foreach ($_POST['form_proc_type_diag'] as $diag) {
            $diag_flag = (!empty($diag) || !empty($_POST['form_order_diagnosis'])) ? (++$diag_flag) : $diag_flag;
        }
        if ($diag_flag === 0) {
            $order_data .= "\n" . xlt("At least one diagnosis is required! Please add a diagnosis for this order.");
        }
        if ($_POST['form_order_abn'] === 'required') {
            $order_data .= "\n" . xlt("ABN is required but not signed!");
        }
        if (!$_POST['form_date_collected'] && !$_POST['form_order_psc']) {
            $order_data .= "\n" . xlt("Specimen Collections date has not been entered and this is not a PSC Hold Order!");
        }
        if (empty($_POST['form_billing_type'])) {
            $order_data .= "\n" . xlt("Billing Type is required but not selected!");
        }
        if ($order_data) {
            $alertmsg = date('Y-m-d H:i') . " " . xlt("Prior Validations Errors") . $order_data;
            $order_data .= "\n<span class='text-danger'>" . "- " .
                xlt("Please resolve errors and resubmit order.") .
                "</span>";
            $order_data = nl2br($order_data);
            if ($order_log) {
                $alertmsg = $order_log . "\n" . $alertmsg;
                $order_log = $alertmsg; // persist log
            } else {
                $order_log = $alertmsg;
                $alertmsg = '';
            }
            file_put_contents($log_file, $order_log);
        } else { // drop through if no errors.
            if ($isDorn) {
                $event = new DornLabEvent($formid, $ppid, $hl7, $reqStr);
                // Generate HL7 order using the DornLabEvent.
                $ed->dispatch($event, DornLabEvent::GEN_HL7_ORDER);
                $alertmsg .= $event->getMessagesAsString('Generate Order:', true);
            } else {
                // Lab-specific configuration: maps lab identifier to required files
                $interfaceDir = realpath(dirname(__DIR__, 2));
                $procToolsDir = $interfaceDir . DIRECTORY_SEPARATOR . 'procedure_tools';
                $labConfigs = [
                    'ammon' => ["{$procToolsDir}/gen_universal_hl7/gen_hl7_order.inc.php"],
                    'clarity' => ["{$procToolsDir}/gen_universal_hl7/gen_hl7_order.inc.php"],
                    'labcorp' => [
                        "{$procToolsDir}/labcorp/ereq_form.php",
                        "{$procToolsDir}/labcorp/gen_hl7_order.inc.php",
                    ],
                    'quest' => ["{$procToolsDir}/quest/gen_hl7_order.inc.php"],
                    'default' => [
                        "{$procToolsDir}/ereqs/ereq_universal_form.php",
                        "{$interfaceDir}/orders/gen_hl7_order.inc.php",
                    ],
                ];
                // Load the appropriate implementation files
                $requiredFiles = $labConfigs[$gbl_lab] ?? $labConfigs['default'];
                foreach ($requiredFiles as $file) {
                    require_once($file);
                }

                try {
                    // Generate the HL7 order
                    $result = gen_hl7_order($formid);
                    $hl7 = $result->hl7;
                    $reqStr = $result->requisitionData;
                } catch (Hl7OrderGenerationException $e) {
                    $alertmsg = $e->getMessage();
                }
            }

            if (empty($alertmsg)) {
                if (empty($_POST['bn_save_ereq'])) {
                    if ($isDorn) {
                        $event = new DornLabEvent($formid, $ppid, $hl7, $reqStr);
                        $alertmsg = '';
                        $ed->dispatch($event, DornLabEvent::SEND_ORDER);
                        $orderResponse = $event->getSendOrderResponse();
                        if (!$orderResponse->isSuccess) {
                            $alertmsg = $orderResponse->responseMessage ?? $orderResponse;
                        }
                        if (!empty($_POST['form_order_psc'] ?? '')) {
                            // todo: check if more than one requisition document can be returned
                            $eReqForm = $orderResponse->orders[0]->requisitionDocumentBase64 ?? '';
                            if (!empty($eReqForm)) {
                                $eReqForm = base64_decode((string) $eReqForm);
                                if (empty($eReqForm)) {
                                    $alertmsg .= "\n" . xlt("Error decoding eReq PDF document.");
                                } else {
                                    $error_save = saveEreq($pid, $formid, $eReqForm);
                                    if (empty($error_save)) {
                                        $order_log .= "\n" . xlt("Order Requisition PDF Document saved successfully.");
                                    } else {
                                        $alertmsg .= "\n" . xlt("Error saving eReq PDF document.") . ': ' . $error_save;
                                    }
                                }
                            }
                        }
                    } else {
                        $alertmsg = send_hl7_order($ppid, $hl7);
                    }
                }
            } else {
                $order_data .= $alertmsg;
            }
            if (empty($alertmsg)) {
                $savereq = true;
                if (empty($_POST['bn_save_ereq'])) {
                    sqlStatement("UPDATE procedure_order SET date_transmitted = NOW() WHERE procedure_order_id = ?", [$formid]);
                    $order_log .= "\n" . date('Y-m-d H:i') . " " .
                        xlt("Order Successfully Sent") . "...\n" .
                        xlt("Order HL7 Content") .
                        ":\n" . $hl7 . "\n";
                    if ($isDorn) {
                        $order_log .= xlt("DORN Order Transaction.");
                    }
                    if ($gbl_lab === 'quest' && $isDorn === false) {
                        $order_log .= xlt("Transmitting order to Quest");
                        $ed->dispatch(new QuestLabTransmitEvent($hl7), QuestLabTransmitEvent::EVENT_LAB_TRANSMIT);
                        $ed->dispatch(new QuestLabTransmitEvent($pid), QuestLabTransmitEvent::EVENT_LAB_POST_ORDER_LOAD);
                    }

                    if ($_POST['form_order_psc']) {
                        if ($gbl_lab === 'labcorp' && $isDorn === false) {
                            $order_log .= "\n" . date('Y-m-d H:i') . " " .
                                xlt("Generating and charting requisition for PSC Hold Order") . "...\n";
                            ereqForm($pid, $encounter, $formid, $reqStr, $savereq);
                        }
                    }
                } else {
                    $savereq = false;
                    if ($gbl_lab !== 'quest') {
                        // Manual requisition
                        $order_log .= "\n" . date('Y-m-d H:i') . " " .
                            xlt("Generating requisition based on order HL7 content") . "...\n" . $hl7 . "\n";
                        ereqForm($pid, $encounter, $formid, $reqStr, $savereq);
                    }
                }
            } else {
                $order_data .= "\n" . xlt("Transmit failed. See Order Log for details.");
                $alertmsg .= "\n" . xlt("Transmit failed. Lab response") . ': ' . $alertmsg . "\n" . xlt("Failed HL7 Content for Review") . ":\n" . $hl7 . "\n";
                if ($order_log) {
                    $alertmsg = $order_log . "\n" . $alertmsg;
                    $order_log = $alertmsg; // persist log
                } else {
                    $order_log = $alertmsg;
                }
            }
            file_put_contents($log_file, $order_log);
        }

        unset($_POST['bn_xmit']);
    }
    unset($_POST['bn_save']);
    $reload_url = $rootdir . '/patient_file/encounter/view_form.php?formname=procedure_order&id=' . attr($formid);
    if (empty($order_data)) {
        header('Location:' . $reload_url);
    }
}

if (!empty($formid)) {
    $row = sqlQuery("SELECT * FROM procedure_order WHERE procedure_order_id = ?", [$formid]);
}

$enrow = sqlQuery(
    "SELECT p.fname, p.mname, p.lname, fe.date FROM " .
    "form_encounter AS fe, forms AS f, patient_data AS p WHERE " .
    "p.pid = ? AND f.pid = p.pid AND f.encounter = ? AND " .
    "f.formdir = 'newpatient' AND f.deleted = 0 AND " .
    "fe.id = f.form_id LIMIT 1",
    [$pid, $encounter]
);

$bill_type = $row['billing_type'] ?? '';
$gbl_lab = get_lab_name($row['lab_id'] ?? '');

if ($formid) {
    $location = sqlQueryNoLog("SELECT f.id, f.facility_code, f.name FROM facility as f " .
        "WHERE f.id = ?", [$row['account_facility']]);
} else {
    $location = sqlQueryNoLog("SELECT f.id, f.facility_code, f.name FROM users as u " .
        "INNER JOIN facility as f ON u.facility_id = f.id WHERE u.id = ?", [$row['provider_id']]);
}
$account = $location['facility_code'] ?? '';
$account_name = $location['name'] ?? '';
$account_facility = $location['id'] ?? '';
if (!empty($row['lab_id'])) {
    $isDorn = isDornLab($row['lab_id']) ?? false;
    $lab_name = normalizeDirectoryName(get_lab_name($row['lab_id']));
    $log_file = $GLOBALS["OE_SITE_DIR"] . "/documents/labs/" . check_file_dir_name($lab_name) . "/logs/";

    if (!is_dir($log_file)) {
        if (!mkdir($log_file, 0755, true) && !is_dir($log_file)) {
            throw new Exception(sprintf('Directory "%s" was not created', $log_file));
        }
    }
// filename
    $log_file .= check_file_dir_name($formid) . '_order_log.log';
    if (file_exists($log_file)) {
        $order_log = file_get_contents($log_file);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker', 'reason-code-widget']); ?>

    <script>
        // Some JS Globals that will be useful.
        let gbl_formseq;
        let currentLabId = <?php echo js_escape($row['lab_id'] ?? ''); ?>;
        let currentLab = <?php echo js_escape($gbl_lab); ?>;
        let currentLabTitle = <?php echo js_escape($gbl_lab_title); ?>;
        let viewmode = <?php echo !empty($viewmode) ? 1 : 0 ?>;
        let refreshForm = <?php echo js_escape($reload_url); ?>;
        const formId = $("input[name='id']").val();

        // we want to setup our reason code widgets
        window.addEventListener('DOMContentLoaded', function () {
            if (oeUI.reasonCodeWidget) {
                oeUI.reasonCodeWidget.init(<?php echo js_url($GLOBALS['webroot']); ?>, <?php echo js_url(collect_codetypes("medical_problem", "csv")) ?>);
            } else {
                console.error("Missing required dependency reasonCodeWidget");
                return;
            }
            // Auto-open specimen panels that already have at least one row
            $('.specimenContainer').each(function () {
                if ($(this).find('tbody tr').length > 0 &&
                    $(this).find('tbody tr input,tbody tr select').filter(function () {
                        return $(this).val();
                    }).length > 0) {
                    $(this).collapse('show');
                }
            });
            // If editing an existing order, close the order options panel real estate matters!
            if ($("input[name='id']").val() > 0) {
                $('#orderOptions').collapse();
            }
        });

        $(document).on('change', 'select[name^="form_proc_specimen_type_code"]', function () {
            const $row = $(this).closest('tr');
            const description = $(this).find('option:selected').text().trim();
            $row.find('input[name^="form_proc_specimen_type"]').val(description);
        });

        $(document).on('change', 'select[name^="form_proc_collection_method_code"]', function () {
            const $row = $(this).closest('tr');
            const description = $(this).find('option:selected').text().trim();
            $row.find('input[name^="form_proc_collection_method"]').val(description);
        });

        $(document).on('change', 'select[name^="form_proc_specimen_location_code"]', function () {
            const $row = $(this).closest('tr');
            const description = $(this).find('option:selected').text().trim();
            $row.find('input[name^="form_proc_specimen_location"]').val(description);
        });

        $(document).on('change', 'select[name^="form_proc_specimen_condition_code"]', function () {
            const $row = $(this).closest('tr');
            const description = $(this).find('option:selected').text().trim();
            $row.find('input[name^="form_proc_specimen_condition"]').val(description);
        });

        // Handle deletions of procedure order codes and related data
        function deleteRow(event) {
            event.preventDefault();
            event.stopPropagation();

            let $row = $(event.currentTarget).closest('tr');
            let target = $row.find("input[name^='form_proc_type_desc']").val();

            if (!target) {
                // Just remove the row if it's a new unsaved procedure
                $(event.currentTarget).closest(".proc-table").remove();
                return;
            }

            if (!confirm(<?php echo xlj("Confirm to remove item") ?> +"\n" + target)) {
                return;
            }

            // Check if this is a saved procedure (has order_seq)
            let $procTable = $(event.currentTarget).closest(".proc-table");
            let orderSeqInput = $procTable.find("input[name^='form_proc_order_seq']");

            if (orderSeqInput.length && orderSeqInput.val()) {
                // This is a saved procedure - delete via AJAX
                let formId = $("input[name='id']").val();
                let orderSeq = orderSeqInput.val();

                $.ajax({
                    url: top.webroot_url + '/interface/forms/procedure_order/handle_deletions.php',
                    type: 'POST',
                    data: {
                        action: 'delete_procedure',
                        order_id: formId,
                        order_seq: orderSeq,
                        csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                    },
                    success: function (response) {
                        if (response.success) {
                            $procTable.remove();

                            if (response.orderEmpty) {
                                // Order is now empty, redirect or reload
                                alert(<?php echo xlj('Last procedure removed. Order marked as inactive.'); ?>);
                                location.reload();
                            }
                        } else {
                            alert(<?php echo xlj('Error deleting procedure:'); ?> +' ' + (response.error || 'Unknown error'));
                        }
                    },
                    error: function () {
                        alert(<?php echo xlj('Failed to delete procedure. Please try again.'); ?>);
                    }
                });
            } else {
                // Unsaved procedure - just remove from DOM
                $procTable.remove();
            }
        }

        // Specimen row removal with AJAX
        $(document).on('click', '.remove-specimen-row', function (e) {
            e.preventDefault();

            let $row = $(this).closest('tr');
            let specimenIdInput = $row.find("input[name^='form_proc_specimen_id']");

            // Check if any fields in this row have data
            let hasData = false;
            $row.find('input, select').each(function () {
                if ($(this).val() && $(this).attr('name') !== specimenIdInput.attr('name')) {
                    hasData = true;
                    return false; // break
                }
            });

            if (hasData && !confirm(<?php echo xlj('Remove this specimen?'); ?>)) {
                return;
            }

            if (specimenIdInput.length && specimenIdInput.val()) {
                // This is a saved specimen - delete via AJAX
                let specimenId = specimenIdInput.val();

                $.ajax({
                    url: top.webroot_url + '/interface/forms/procedure_order/handle_deletions.php',
                    type: 'POST',
                    data: {
                        action: 'delete_specimen',
                        specimen_id: specimenId,
                        csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                    },
                    success: function (response) {
                        if (response.success) {
                            $row.remove();
                        } else {
                            alert(<?php echo xlj('Error deleting specimen:'); ?> +' ' + (response.error || 'Unknown error'));
                        }
                    },
                    error: function () {
                        alert(<?php echo xlj('Failed to delete specimen. Please try again.'); ?>);
                    }
                });
            } else {
                // Unsaved specimen - just remove from DOM
                $row.remove();
            }
        });

        // Add hidden order_seq field to track saved procedures
        function addProcedureOrderSeqField(lineCount, orderSeq) {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'form_proc_order_seq[' + lineCount + ']';
            input.value = orderSeq;

            let container = document.querySelector('#procedures_item_' + lineCount);
            if (container) {
                container.appendChild(input);
            }
        }

        // Initialize deletion handlers
        function initDeletes() {
            $(".itemDelete").off("click").on("click", function (event) {
                deleteRow(event);
            });
        }

        // end deletion

        function processSubmit(od) { // not used yet
            $("#form_order_abn").val(od.order_abn);
            $("#bn_save").click();
        }

        function initCalendars() {
            let datepicker = {
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            };
            let datetimepicker = {
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = false; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            };
            $('.datepicker').datetimepicker(datepicker);
            $('.datetimepicker').datetimepicker(datetimepicker);
        }

        // This invokes the find-procedure-type popup.
        // formseq = 0-relative index in the form.
        function sel_proc_type(formseq) {
            let f = document.forms[0];
            gbl_formseq = formseq;
            let ptvarname = 'form_proc_type[' + formseq + ']';

            let title = <?php echo xlj("Find Procedure Order"); ?>;
            // This replaces the previous search for an easier/faster order picker tool.
            const params = new URLSearchParams({
                formid: <?php echo js_escape($formid); ?>,
                formseq: formseq,
                labid: f.form_lab_id.value,
                order: f[ptvarname].value
            });
            dlgopen('../../orders/find_order_popup.php?' + params,
                '_blank', 850, 500, '', title);
        }

        // This is for callback by the find-procedure-type popup.
        // Sets both the selected type ID and its descriptive name.
        // Also set diagnosis if supplied in configuration and custom test groups.
        function set_proc_type(typeid, typename, diagcodes = '', temptype, typetitle, testid, newCnt = 0) {
            let f = document.forms[0];
            let ptvarname = 'form_proc_type[' + gbl_formseq + ']';
            let ptdescname = 'form_proc_type_desc[' + gbl_formseq + ']';
            let ptcodes = 'form_proc_type_diag[' + gbl_formseq + ']';
            let pttransport = 'form_transport[' + gbl_formseq + ']';
            let ptproccode = 'form_proc_code[' + gbl_formseq + ']';
            let ptproctypename = 'form_proc_order_title[' + gbl_formseq + ']';
            let psc = '';

            f[pttransport].value = temptype;
            f[ptvarname].value = typeid;
            f[ptdescname].value = typename;
            f[ptproccode].value = testid;
            f[ptproctypename].value = typetitle ? typetitle : 'procedure';
            if (diagcodes)
                f[ptcodes].value = diagcodes;
            if (newCnt > 1) {
                gbl_formseq = addProcLine(true);
            }
        }

        // This is also for callback by the find-procedure-type popup.
        // Sets the contents of the table containing the form fields for questions.
        function set_proc_html(s, js) {
            document.getElementById('qoetable[' + gbl_formseq + ']').innerHTML = s;
            initCalendars();
        }

        function addProcedure() {
            $(".procedure-order-container").append($(".procedure-order").clone());
            let newOrder = $(".procedure-order-container .procedure-order:last");
            $(newOrder + " label:first").append("1");
        }

        // New lab selected so clear all procedures and questions from the form.
        // Replace your lab_id_changed function with this:
        function lab_id_changed(el) {
            if (viewmode) {
                let msg = "Changing Labs will clear this order. Are you sure you want to continue?";
                if (!confirm(msg)) {
                    $("#form_lab_id").val(currentLabId);
                    return false;
                }
            }
            top.restoreSession();
            let lab = $("#form_lab_id option:selected").text();
            if (lab.toLowerCase().indexOf('quest') !== -1) currentLab = 'quest';
            if (lab.toLowerCase().indexOf('ammon') !== -1) currentLab = 'ammon';
            if (lab.toLowerCase().indexOf('labcorp') !== -1) currentLab = 'labcorp';
            if (lab.toLowerCase().indexOf('clarity') !== -1) currentLab = 'clarity';

            let f = document.forms[0];
            // Remove all existing procedure items
            for (let i = 0; true; ++i) {
                let ix = '[' + i + ']';
                if (!f['form_proc_type' + ix]) break;
                let target = '#procedures_item_' + i;
                $(target).closest(".proc-table").remove();
            }

            if (viewmode) {
                $("#bn_save").click();
            } else {
                // Add new line and wait for DOM to update before opening popup
                setTimeout(function () {
                    let lineCount = addProcLine(true); // Add line without auto-popup
                    if (lineCount !== false && lineCount >= 0) {
                        // Now manually trigger the popup after DOM is ready
                        sel_proc_type(lineCount);
                    }
                }, 100);
            }
        }

        // Update addProcLine to always return lineCount:
        function addProcLine(flag = false) {
            if (!('content' in document.createElement('template'))) {
                console.error("Old browser detected as template content property is not supported");
                return false;
            }

            let template = document.getElementById('procedure_order_code_template');
            if (!template) {
                console.error("Cannot add procedure line due to missing template node with id procedure_order_code_template");
                return false;
            }

            // grab our content, update all our ids, setup any event listeners and add the item in
            let node = template.content.cloneNode(true);

            // now we need to update our procedure key names here
            let lineCountNodes = document.querySelectorAll(".procedure-order-container .proc-table-main");
            let lineCount = 0;
            if (lineCountNodes && lineCountNodes.length) {
                lineCount = lineCountNodes.length;
            }

            // now we are going to rename all of our templated nodes to be our newest index.
            let remapArrayIndex = function (value) {
                if (value && value.indexOf("[")) {
                    let parts = value.split("[");
                    return parts[0] + "[" + lineCount + "]";
                } else {
                    return value;
                }
            };
            let remapNames = function (node) {
                node.name = remapArrayIndex(node.name);
            };
            // wierdly all of our mapped ids use array indexes as part of the id.
            let remapIds = function (node) {
                node.id = remapArrayIndex(node.id);
            };
            let remapSelectors = function (selector, map) {
                let mapNodes = node.querySelectorAll(selector);
                if (mapNodes && mapNodes.length) {
                    mapNodes.forEach(map);
                }
            };
            remapSelectors('input,select', remapNames);
            remapSelectors('.qoe-table-sel-procedure', remapIds);
            remapSelectors('[data-toggle-container]', function (node) {
                node.dataset.toggleContainer = "reason_code_" + lineCount;
            });
            remapSelectors('.reasonCodeContainer', function (node) {
                node.id = "reason_code_" + lineCount;
            });

            // Map specimen panel ids/datasets to current line index
            remapSelectors('[data-toggle-specimen]', function (node) {
                node.dataset.toggleSpecimen = "specimen_code_" + lineCount;
            });
            remapSelectors('.specimenContainer', function (node) {
                node.id = "specimen_code_" + lineCount;
            });

            // now we need to add our events
            let nullableFunction = function (selector, event, callback) {
                let nodeForCallback = node.querySelector(selector);
                if (nodeForCallback) {
                    nodeForCallback.addEventListener(event, callback);
                } else {
                    console.error("Failed to find node with selector ", selector);
                }
            };
            // once our node is in the DOM, we need to add event listeners to it.
            nullableFunction('.itemTransport', 'click', function (event) {
                // we have to bind to our lineCount at the time of instantiation in case addProcLine is called again
                // and we curry against the outer lineCount
                var boundLineCount = lineCount + 0; // should be copy by value, but some JS contexts are wierd
                getDetails(event, boundLineCount);
            });
            nullableFunction('.btn-secondary.btn-search', 'click', function (event) {
                // we have to bind to our lineCount at the time of instantiation in case addProcLine is called again
                // and we curry against the outer lineCount
                var boundLineCount = lineCount + 0; // should be copy by value, but some JS contexts are wierd
                selectProcedureCode(boundLineCount);
            });
            nullableFunction('.search-current-diagnoses', 'click', function (event) {
                current_diagnoses(event.currentTarget); // use the bound target
            });

            nullableFunction('.add-diagnosis-sel-related', 'click', function (event) {
                sel_related(event.currentTarget.name);
            });

            nullableFunction('.add-diagnosis-sel-related', 'focus', function (event) {
                event.currentTarget.blur();
            });

            nullableFunction('.sel-proc-type', 'click', function (event) {
                var boundLineCount = lineCount + 0; // should be copy by value, but some JS contexts are wierd
                sel_proc_type(boundLineCount);
            });
            nullableFunction('.sel-proc-type', 'focus', function (event) {
                event.currentTarget.blur();
            });

            nullableFunction('.itemDelete', 'click', deleteRow);

            // now we will take all the children of the doc fragment and stuff it into the DOM
            $(".procedure-order-container").append(node);


            initForm();

            if (!flag) {// flag true indicates add procedure item from custom group callback with current index.
                // note the proc type order id is -1 for a new row... this makes the popup happy, not sure why this was originally set to -1.
                sel_proc_type(lineCount);
            }

            return lineCount; // Always return the line count
        }

        // The name of the form field for find-code popup results.
        var rcvarname, targetElement, targetProcedure, promiseData;

        /*
        * I could have used a callback like set_related_target but I wanted
        * to show an example of a dialog promise. Handy for cleanup or showing
        * a dialog.alert etc..
        *
        * */
        function selectProcedureCode(offset) {
            let f = document.forms[0];
            rcvarname = f.elements['form_proc_code[' + offset + ']'].name;
            codes = f.elements['form_proc_code[' + offset + ']'].value;
            targetProcedure = f.elements['form_proc_code[' + offset + ']'];
            targetElement = f.elements['form_proc_type_desc[' + offset + ']'];
            let title = <?php echo xlj('Select Procedure Code'); ?>;
            let url = top.webroot_url + '/interface/patient_file/encounter/find_code_dynamic.php?codetype=LOINC,SNOMED-CT,SNOMED,RXCUI,CPT4,VALUESET,OID&singleCodeSelection=1';
            dlgopen(url, '_blank', 985, 750, '', title, {
                resolvePromiseOn: 'close'
            }).then(dialogObject => {
                let codeData = JSON.parse(promiseData);
                let codes = codeData.codetype + ':' + codeData.code;
                targetProcedure.value = codes;
                targetElement.value = codeData.codedesc;
                let type = f.elements['procedure_type_names'].value ?? '';
                if (type !== '') {
                    f.elements['form_procedure_type[' + offset + ']'].value = type;
                }
                f.elements['form_proc_type[' + offset + ']'].value = -2; // lookup type id on save.
            });
        }

        function current_diagnoses(whereElement) {
            targetProcedure = whereElement.parentElement.parentElement.parentElement.previousElementSibling;
            targetElement = whereElement.parentElement.parentElement.nextElementSibling;
            let title = <?php echo xlj("Diagnosis Codes History"); ?>;
            dlgopen('find_code_history.php', 'dxDialog', 'modal-mlg', 450, '', title, {
                buttons: [
                    {text: '<?php echo xla('Save'); ?>', id: 'saveDx', style: 'primary btn-save'},
                    {text: '<?php echo xla('Help'); ?>', id: 'showTips', style: 'primary btn-show'},
                    {text: '<?php echo xla('Cancel'); ?>', close: true, style: 'secondary btn-cancel'},
                ],
                type: 'iframe'
            });
        }

        // This is for callback by the find-code popup.
        // Appends to or erases the current list of related codes.
        function set_related(codetype, code, selector, codedesc) {
            let f = document.forms[0];
            let s = f[rcvarname].value;
            if (code) {
                if (s.length > 0) s += ';';
                s += codetype + ':' + code;
            } else {
                s = '';
            }
            f[rcvarname].value = s;
        }

        // This invokes the find-code popup.
        function sel_related(varname) {
            rcvarname = varname;
            // codetype is just to make things easier and avoid mistakes.
            // Might be nice to have a lab parameter for acceptable code types.
            // Also note the controlling script here runs from interface/patient_file/encounter/.
            let title = '<?php echo xla("Select Diagnosis Codes"); ?>';
            <?php /*echo attr(collect_codetypes("diagnosis", "csv")); */?>
            let url = top.webroot_url + '/interface/patient_file/encounter/find_code_dynamic.php?codetype=' + <?php echo js_url(collect_codetypes("diagnosis", "csv")); ?>;
            dlgopen(url, '_blank', 985, 750, '', title);
        }

        // This is for callback by the find-code popup.
        // Returns the array of currently selected codes with each element in codetype:code format.
        function get_related() {
            return document.forms[0][rcvarname].value.split(';');
        }

        // This is for callback by the find-code popup.
        // Deletes the specified codetype:code from the currently selected list.
        function del_related(s) {
            my_del_related(s, document.forms[0][rcvarname], false);
            if (targetElement != '') {
                targetElement.value = '';
            }
        }

        let transmitting = false;

        // Issue a Cancel/OK warning if a previously transmitted order is being transmitted again.
        function validate(f, e) {
            <?php if (!empty($row['date_transmitted'])) { ?>
            if (transmitting) {
                if (!confirm(<?php echo xlj('This order was already transmitted on') ?> +' ' +
                    <?php echo js_escape($row['date_transmitted']) ?> +'. ' +
                    <?php echo xlj('Are you sure you want to transmit it again?'); ?>)) {
                    return false;
                }
            }
            <?php } ?>
            $(".wait").removeClass('d-none');
            top.restoreSession();
            return true;
        }

        $(function () {
            // calendars need to be available to init dynamically for procedure item adds.
            initCalendars();
            initDeletes();
            initForm();
            $(function () {
                $.ajaxSetup({
                    error: function (jqXHR, exception) {
                        if (jqXHR.status === 0) {
                            alert('Not connected to network.');
                        } else if (jqXHR.status == 404) {
                            alert('Requested page not found. [404]');
                        } else if (jqXHR.status == 500) {
                            alert('Internal Server Error [500].');
                        } else if (exception === 'parsererror') {
                            alert('Requested JSON parse failed.');
                        } else if (exception === 'timeout') {
                            alert('Time out error.');
                        } else if (exception === 'abort') {
                            alert('Ajax request aborted.');
                        } else {
                            alert('Uncaught Error.\n' + jqXHR.responseText);
                        }
                    }
                })
                return false;
            });

            // Toggle the specimen panel
            $(document).on('click', '.specimen-code-btn', function (e) {
                e.preventDefault();
                const id = this.getAttribute('data-toggle-specimen');
                $('#' + id).collapse('toggle');
            });
            // Add specimen row
            $(document).on('click', '.add-specimen-row', function (e) {
                e.preventDefault();
                const line = this.getAttribute('data-specimen-line');
                const tbody = document.getElementById('specimen_rows_' + line);
                const tpl = document.getElementById('specimen_row_template_' + line);
                if (!tbody || !tpl) return;
                const node = tpl.content.cloneNode(true);
                tbody.appendChild(node);
                // (re)init date pickers on the new inputs
                initCalendars();
            });
            // Remove specimen row
            $(document).on('click', '.remove-specimen-row', function (e) {
                e.preventDefault();
                $(this).closest('tr').remove();
            });

            <?php if ($row['date_transmitted'] ?? '') { ?>
            $("#summary").collapse("toggle");
            <?php } ?>
        });

        function getDetails(e, id) {
            top.restoreSession();
            let f = document.forms[0];
            let codeattr = 'form_proc_code[' + id + ']';
            let codetitle = 'form_proc_type_desc[' + id + ']';
            let code = f[codeattr].value;
            let url = top.webroot_url + "/interface/procedure_tools/libs/labs_ajax.php";
            const params = new URLSearchParams({
                action: 'code_detail)',
                code: code,
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            });
            url += "?" + params;
            let title = <?php echo xlj("Test") ?> +": " + code + " " + f[codetitle].value;
            dlgopen(url, 'details', 'modal-md', 200, '', title, {
                buttons: [
                    {text: '<?php echo xla('Got It'); ?>', close: true, style: 'secondary btn-sm'}
                ]
            });
        }

        function initForm(reload = false) {
            if (reload) {
                location.reload();
            }
            $(".ammon").hide();
            $(".quest").hide();
            $(".labcorp").hide();
            $(".clarity").hide();
            $(".defaultProcedure").hide();

            if (currentLab === 'ammon') {
                $(".ammon").show();
            } else if (currentLab === 'quest') {
                $(".quest").show();
            } else if (currentLab === 'labcorp') {
                $(".labcorp").show();
            } else if (currentLab === 'clarity') {
                $(".clarity").show();
            } else if (currentLab === '') {
                $(".defaultProcedure").show();
            }
            if (oeUI.reasonCodeWidget) {
                oeUI.reasonCodeWidget.reload();
            }
        }

        function createLabels(e) {
            e.preventDefault();
            let prmt = <?php echo js_escape(xla("How many sets of specimen labels to create?") .
                "\n" . xla("Each test in order gets a label.")); ?>;
            let count = prompt(prmt, '1');
            if (!count) return false;
            let tarray = "";
            let f = document.forms[0];
            let transport = '';
            let i = 0;
            for (; f['form_transport[' + i + ']']; ++i) {
                transport = f['form_transport[' + i + ']'].value;
                transport = transport > '' ? transport : 'none';
                tarray += transport + ";";
            }
            let printer = 'file';
            let acctid = <?php echo js_escape($gbl_client_acct); ?>;
            let order = f.id.value;
            let patient = <?php echo js_escape($patient['lname'] . ', ' . $patient['fname'] . ' ' . $patient['mname']); ?>;
            let dob = <?php echo js_escape($patient['DOB']); ?>;
            let pid = <?php echo js_escape($patient['pid']);  ?>;
            let url = top.webroot_url + "/interface/procedure_tools/libs/labs_ajax.php";
            // this escapes above
            const params = new URLSearchParams({
                acctid: acctid,
                action: 'print_labels',
                count: count,
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>,
                dob: dob,
                order: order,
                patient: patient,
                pid: pid,
                specimen: tarray
            });
            const uri = "?" + params;

            // retrieve the labels
            dlgopen(url + uri, 'pdf', 'modal-md', 750, '');
        }

        function doWait(e) {
            $(".wait").removeClass('d-none');
            return true;
        }
    </script>
    <style>
      @media only screen and (max-width: 768px) {
        [class*="col-"] {
          width: 100%;
          text-align: left !important;
        }
      }

      .qoe-table {
        margin-bottom: 0px;
      }

      .proc-table {
        margin-bottom: 5px;
      }

      .proc-table .itemDelete {
        width: 25px;
        color: var(--danger);
        cursor: pointer;
      }

      .proc-table .itemTransport {
        width: 45px;
        margin: 5px 2px;
        padding: 2px 2px;
        cursor: hand;
      }

      .proc-table .procedure-div {
        min-width: 40%;
      }

      .proc-table .diagnosis-div {
        min-width: 20%;
      }

      .c-hand {
        cursor: pointer;
      }

      .lfont1 {
        font-size: 1.25rem;
      }
    </style>
</head>
<?php
$name = $enrow['fname'] . ' ';
$name .= (!empty($enrow['mname'])) ? $enrow['mname'] . ' ' . $enrow['lname'] : $enrow['lname'];
$date = xl('on') . ' ' . oeFormatShortDate(substr((string) $enrow['date'], 0, 10));
$title = [xl('Order for'), $name, $formid ? xl('Order Id') . ' ' . text($formid) : xl('New Order')];
$reasonCodeStatii = ReasonStatusCodes::getCodesWithDescriptions();
$reasonCodeStatii[ReasonStatusCodes::NONE]['description'] = xl("Select a status code");
?>
<body class="body_top" onsubmit="doWait(event)">
    <div class="container-fluid">
        <div class="page-header text-center">
            <h4><?php echo text(implode(" ", $title)); ?></h4>
        </div>
        <?php

        $oparr = [];
        $oprow = [];
        if ($formid) {
            $opres = sqlStatement(
                "SELECT " .
                "pc.procedure_order_seq, pc.procedure_code, pc.procedure_name, " .
                "pc.reason_code, pc.reason_description, pc.reason_status, pc.reason_date_low, pc.reason_date_high, " .
                "pc.diagnoses, pc.procedure_order_title, pc.transport, pc.procedure_type, " .
                // In case of duplicate procedure codes this gets just one.
                "(SELECT pt.procedure_type_id FROM procedure_type AS pt WHERE " .
                "(pt.procedure_type LIKE 'ord%' OR pt.procedure_type LIKE 'for%' OR pt.procedure_type LIKE 'pro%') AND pt.lab_id = ? AND " .
                "pt.procedure_code = pc.procedure_code ORDER BY " .
                "pt.activity DESC, pt.procedure_type_id LIMIT 1) AS procedure_type_id " .
                "FROM procedure_order_code AS pc " .
                "WHERE pc.procedure_order_id = ? " .
                "ORDER BY pc.procedure_order_seq",
                [$row['lab_id'], $formid]
            );
            while ($oprow = sqlFetchArray($opres)) {
                $oparr[] = $oprow;
            }
            $reqres = $opres = sqlStatement(
                "Select id, url, documentationOf From documents where foreign_id = ? And list_id = ? Order By id",
                [$pid, $formid]
            );
            $req = [];
            while ($oprow = sqlFetchArray($reqres)) {
                $doc_type = stripos((string) $oprow['url'], 'ABN') ? 'ABN' : 'REQ';
                if ($gbl_lab === "labcorp") {
                    $doc_type = "eREQ";
                }
                $this_req = $req_url . $oprow['id'];
                $this_name = $oprow['documentationOf'];
                $this_name = $this_name && $this_name !== "ABN" ? ($doc_type . '_' . $this_name) : $doc_type;
                $req[] = ['url' => $this_req, 'type' => $doc_type, 'name' => $this_name];
            }
            $req_count = count($req);
        }
        if (empty($oparr)) {
            $oparr[] = ['procedure_name' => ''];
        }

        // Build specimen map: [procedure_order_seq] => array(rows)
        $specimen_by_seq = [];
        if (!empty($formid)) {
            $spq = sqlStatement(
                "SELECT * FROM procedure_specimen WHERE procedure_order_id = ? AND `deleted` = 0 ORDER BY procedure_order_seq, procedure_specimen_id",
                [$formid]
            );
            while ($sp = sqlFetchArray($spq)) {
                $specimen_by_seq[(int)$sp['procedure_order_seq']][] = $sp;
            }
        }
        ?>

        <div class="col-md-12">
            <form class="form form-horizontal" method="post" action="" onsubmit="return validate(this,event)">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <input type='hidden' name='id' value='<?php echo attr($formid) ?>' />
                <fieldset class="container-xl clearfix">
                    <legend class="lfont1" data-toggle="collapse" data-target="#orderOptions" role="button">
                        <i class="fa fa-plus"></i>
                        <?php echo xlt('Select Options for Current Order Id') . ' ' . (text($formid) ?: 'New Order') ?>
                    </legend>
                    <div class="col-md-12 collapse show" id="orderOptions">
                        <div class="form-group form-row">
                            <label for="provider_id" class="col-form-label col-md-2"><?php echo xlt('Ordering Provider'); ?></label>
                            <div class="col-md-2">
                                <?php generate_form_field(['data_type' => 10, 'field_id' => 'provider_id'], $row['provider_id']); ?>
                            </div>
                            <label for="form_date_ordered" class="col-form-label col-md-2"><?php echo xlt('Order Date'); ?></label>
                            <div class="col-md-2">
                                <input type='text' class='datepicker form-control'
                                    name='form_date_ordered'
                                    id='form_date_ordered'
                                    value="<?php echo attr($row['date_ordered']); ?>"
                                    title="<?php echo xla('Date of this order'); ?>" />
                            </div>
                            <label for="lab_id" class="col-form-label col-md-2"><?php echo xlt('Sending To'); ?></label>
                            <div class="col-md-2">
                                <select name='form_lab_id' id='form_lab_id' onchange='lab_id_changed(this)' class='form-control'>
                                    <?php
                                    $ppres = sqlStatement("SELECT `ppid`, name FROM `procedure_providers` WHERE `active` = 1 ORDER BY name, ppid");
                                    while ($pprow = sqlFetchArray($ppres)) {
                                        echo "<option value='" . attr($pprow['ppid']) . "'";
                                        if ($pprow['ppid'] == ($row['lab_id'] ?? '')) {
                                            echo " selected";
                                            $gbl_lab = get_lab_name($pprow['ppid']);
                                        }
                                        echo ">" . text($pprow['name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <!-- Order Intent & Status & Priority -->
                        <div class="form-group form-row">
                            <label for="form_order_intent" class="col-form-label col-md-2"><?php echo xlt('Order Intent'); ?></label>
                            <div class="col-md-2">
                                <?php
                                generate_form_field([
                                    'data_type' => 1,
                                    'field_id' => 'order_intent',
                                    'list_id' => 'order_intent'
                                ], $row['order_intent'] ?? 'order');
                                ?>
                            </div>
                            <label for="form_order_status" class="col-form-label col-md-2"><?php echo xlt('Status'); ?></label>
                            <div class="col-md-2">
                                <?php
                                generate_form_field([
                                    'data_type' => 1,
                                    'field_id' => 'order_status',
                                    'list_id' => 'ord_status'
                                ], $row['order_status'] ?? '');
                                ?>
                            </div>

                            <label for="form_order_priority"
                                class="col-form-label col-md-2"><?php echo xlt('Priority'); ?></label>
                            <div class="col-md-2">
                                <?php
                                generate_form_field(['data_type' => 1, 'field_id' => 'order_priority',
                                    'list_id' => 'ord_priority'], $row['order_priority'] ?? '');
                                ?>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <!--  Service Details todo sjp lot into collapse -->
                        <div class="form-group form-row">
                            <label for="form_performer_type" class="col-form-label col-md-2"><?php echo xlt('Performer Type'); ?></label>
                            <div class="col-md-2">
                                <?php
                                generate_form_field([
                                    'data_type' => 1,
                                    'field_id' => 'performer_type',
                                    'list_id' => 'performer_type'
                                ], $row['performer_type'] ?? '');
                                ?>
                            </div>
                            <label for="form_location_id" class="col-form-label col-md-2"><?php echo xlt('Service Location'); ?></label>
                            <div class="col-md-2">
                                <select name='form_location_id' id='form_location_id' class='form-control'>
                                    <option value=""><?php echo xlt('Select Location'); ?></option>
                                    <?php
                                    $locres = sqlStatement("SELECT id, name FROM facility WHERE service_location = 1 ORDER BY name");
                                    while ($locrow = sqlFetchArray($locres)) {
                                        echo "<option value='" . attr($locrow['id']) . "'";
                                        if ($locrow['id'] == ($row['location_id'] ?? '')) {
                                            echo " selected";
                                        }
                                        echo ">" . text($locrow['name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <!-- Scheduling Information -->
                        <div class="form-group form-row bg-light py-2 my-2">
                            <div class="col-md-12">
                                <h6 class="text-primary mb-2">
                                    <i class="fa fa-calendar mr-2"></i><?php echo xlt('Scheduling Information'); ?>
                                </h6>
                            </div>
                            <label for="form_scheduled_date" class="col-form-label col-md-2"><?php echo xlt('Scheduled Date'); ?></label>
                            <div class="col-md-2">
                                <input type='text' class='datepicker form-control'
                                    name='form_scheduled_date'
                                    id='form_scheduled_date'
                                    value="<?php echo attr($row['scheduled_date'] ?? ''); ?>"
                                    placeholder="<?php echo xla('When service should occur'); ?>"
                                    title="<?php echo xla('Date when service should be performed'); ?>" />
                            </div>
                            <label for="form_scheduled_start" class="col-form-label col-md-2"><?php echo xlt('Start Time'); ?></label>
                            <div class="col-md-2">
                                <input type='text' class='datetimepicker form-control'
                                    name='form_scheduled_start'
                                    id='form_scheduled_start'
                                    value="<?php echo attr($row['scheduled_start'] ?? ''); ?>"
                                    placeholder="<?php echo xla('Optional start time'); ?>"
                                    title="<?php echo xla('Scheduled start time for procedure'); ?>" />
                            </div>
                            <label for="form_scheduled_end" class="col-form-label col-md-2"><?php echo xlt('End Time'); ?></label>
                            <div class="col-md-2">
                                <input type='text' class='datetimepicker form-control'
                                    name='form_scheduled_end'
                                    id='form_scheduled_end'
                                    value="<?php echo attr($row['scheduled_end'] ?? ''); ?>"
                                    placeholder="<?php echo xla('Optional end time'); ?>"
                                    title="<?php echo xla('Scheduled end time for procedure'); ?>" />
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="form-group form-row">
                            <label for="form_order_psc" class="col-form-label col-md-2"><?php echo xlt('PSC Hold Order'); ?></label>
                            <div class="col-md-2">
                                <?php
                                $pscOrderOpts = [
                                    'data_type' => 1,
                                    'field_id' => 'order_psc',
                                    'list_id' => 'boolean'
                                ];
                                generate_form_field($pscOrderOpts, $row['order_psc'] ?? '');
                                ?>
                            </div>
                            <label for='form_order_abn' class="col-form-label col-md-2"><?php echo xlt('ABN Status'); ?></label>
                            <div class="col-md-2">
                                <select name='form_order_abn' id='form_order_abn' class='form-control'>
                                    <option value="not_required" <?php echo $row['order_abn'] == 'not_required' ? ' selected' : '' ?>><?php echo xlt('Not Required'); ?></option>
                                    <option value="required" <?php echo $row['order_abn'] == 'required' ? ' selected' : '' ?>><?php echo xlt('Required'); ?></option>
                                    <option value="signed" <?php echo $row['order_abn'] == 'signed' ? ' selected' : '' ?>><?php echo xlt('Signed'); ?></option>
                                </select>
                            </div>
                            <label for="form_billing_type" class="col-form-label col-md-2"><?php echo xlt('Billing'); ?></label>
                            <div class="col-md-2">
                                <?php
                                generate_form_field([
                                    'data_type' => 1,
                                    'field_id' => 'billing_type',
                                    'list_id' => 'procedure_billing'
                                ], $row['billing_type'] ?? '');
                                ?>
                            </div>
                            <label for="form_account_facility" class="col-form-label col-md-2 labcorp"><?php echo xlt('Sending From'); ?></label>
                            <div class="col-md-2 labcorp">
                                <select name='form_account_facility' id='form_account_facility' class='form-control'>
                                    <option value=""><?php echo xlt('Select Location'); ?></option>
                                    <?php
                                    $ppres = sqlStatement("SELECT id, name, facility_code FROM facility WHERE facility_code > '' ORDER BY name, id");
                                    while ($facrow = sqlFetchArray($ppres)) {
                                        echo "<option value='" . attr($facrow['id']) . "'";
                                        if ($facrow['id'] == $row['account_facility'] && !$formid) {
                                            $account = $facrow['facility_code'];
                                            $account_facility = $facrow['account_facility'];
                                            $account_name = $facrow['name'];
                                            echo " selected";
                                        } elseif ($facrow['id'] == $account_facility) {
                                            echo " selected";
                                            $account = $facrow['facility_code'];
                                            $account_facility = $facrow['account_facility'];
                                            $account_name = $facrow['name'];
                                        }
                                        echo ">" . text($facrow['name']) . "</option>";
                                    }
                                    ?>
                                </select>
                                <input type='hidden' class="input-sm" name="form_account" value="<?php echo attr($account); ?>">
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="form-group form-row">
                            <label for="form_specimen_fasting" class="col-form-label col-md-2"><?php echo xlt('Fasting'); ?></label>
                            <div class="col-md-2">
                                <?php
                                generate_form_field(['data_type' => 1, 'field_id' => 'specimen_fasting',
                                    'list_id' => 'yesno'], $row['specimen_fasting'] ?? '');
                                ?>
                            </div>
                            <label for="collector_id" class="col-form-label col-md-2"><?php echo xlt('Collected By'); ?></label>
                            <div class="col-md-2">
                                <?php generate_form_field(['data_type' => 10, 'field_id' => 'collector_id'], $row['collector_id'] ?? ''); ?>
                            </div>
                            <label for="form_date_collected" class="col-form-label col-md-2"><?php echo xlt('Time Collected'); ?></label>
                            <div class="col-md-2">
                                <input class='datetimepicker form-control'
                                    type='text'
                                    name='form_date_collected'
                                    id='form_date_collected'
                                    value="<?php echo attr(substr($row['date_collected'] ?? '', 0, 16)); ?>"
                                    title="<?php echo xla('Date and time that the sample was collected'); ?>" />
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="form-group form-row">
                            <label for="form_history_order"
                                class="col-form-label col-md-2"><?php echo xlt('History Order'); ?></label>
                            <div class="col-md-2">
                                <?php
                                $historyOrderOpts = [
                                    'data_type' => 1,
                                    'field_id' => 'history_order',
                                    'list_id' => 'boolean'
                                ];
                                generate_form_field($historyOrderOpts, $row['history_order'] ?? ''); ?>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="form-group form-row">
                            <div class="col-md-6">
                                <label for='form_clinical_hx' class='col-form-label'><?php echo xlt('Clinical History'); ?></label>
                                <textarea class='form-control text' rows='2' cols='60' wrap='hard'
                                    name="form_clinical_hx" id="form_clinical_hx"><?php echo text($row['clinical_hx'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label for='form_patient_instructions' class='col-form-label'><?php echo xlt('Patient Instructions'); ?></label>
                                <textarea class='form-control text' rows='2' cols="60" wrap="hard" id='form_patient_instructions'
                                    name='form_patient_instructions'
                                    title='<?php echo xla('Instructions for patient preparation (fasting, etc.)'); ?>'
                                    placeholder='<?php echo xla('Example: Fast for 12 hours before test'); ?>'><?php echo text($row['patient_instructions'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="form-group form-row bg-dark text-light my-2 py-1">
                            <label for="form_order_diagnosis" class="col-form-label"><?php echo xlt('Primary Diagnosis'); ?></label>
                            <div class="col-md-4">
                                <?php
                                if (!$formid) {
                                    $diagres = sqlStatement(
                                        "SELECT diagnosis FROM lists " .
                                        "Where activity = 1 And type = ? And pid = ?",
                                        ['medical_problem', $pid]
                                    );
                                    $problem_diags = '';
                                    while ($probrow = sqlFetchArray($diagres)) {
                                        if (!str_contains((string) $probrow['diagnosis'], 'ICD')) {
                                            continue;
                                        }
                                        $problem_diags .= $probrow['diagnosis'] . ';';
                                    }
                                } ?>
                                <input class='form-control c-hand' type='text' name='form_order_diagnosis' id='form_order_diagnosis'
                                    value='<?php echo $problem_diags ?? '' ? attr($problem_diags) : attr($row['order_diagnosis'] ?? '') ?>'
                                    onclick='sel_related(this.name)'
                                    title='<?php echo xla('Required Primary Diagnosis for Order. This will be automatically added to any missing test order diagnosis.'); ?>'
                                    onfocus='this.blur()' />
                            </div>
                            <label for="procedure_type_names" class="col-form-label"><?php echo xlt('Default Procedure Type'); ?></label>
                            <div class="col-md-4">
                                <?php $procedure_order_type = getListOptions('order_type', ['option_id', 'title']); ?>
                                <select name="procedure_type_names" id="procedure_type_names" class='form-control'>
                                    <?php foreach ($procedure_order_type as $ordered_types) { ?>
                                        <option value="<?php echo attr($ordered_types['option_id']); ?>"
                                            <?php echo $ordered_types['option_id'] == ($row['procedure_order_type'] ?? '') ? " selected" : ""; ?>><?php echo text(xl_list_label($ordered_types['title'])); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="container-xl card clearfix">
                    <legend class="card-heading collapsed lfont1" data-toggle="collapse" data-target="#summary" role="button">
                        <i class="fa fa-plus mr-2"></i><?php echo xlt("Order Documents and Logs"); ?>
                        <i class="wait fa fa-cog fa-spin ml-2 d-none"></i>
                    </legend>
                    <div class="card-body collapse" id="summary">
                        <div class="form-group"> <!--Order document links-->
                            <div class="col-md-12 text-left position-override">
                                <legend class="bg-dark text-light lfont1" role="button"><?php echo xlt("Order Documents"); ?></legend>
                                <div class="btn-group" role="group">
                                    <?php
                                    if (!empty($req)) {
                                        foreach ($req as $reqdoc) {
                                            $title = $reqdoc['name'];
                                            $rpath = $reqdoc['url']; ?>
                                            <a class="btn btn-outline-primary"
                                                href="<?php echo attr($rpath); ?>"><?php echo text($title) ?></a>
                                        <?php }
                                    } ?>
                                    <a class='btn btn-success ml-1' href='#'
                                        onclick="createLabels(event, this)"><?php echo xlt('Labels'); ?></a>
                                    <?php
                                    if ($row['order_psc'] && !$isDorn) { ?>
                                        <button type="submit" class="btn btn-outline-primary btn-save"
                                            name='bn_save_ereq' id='bn_save_ereq' value="save_ereq"
                                            onclick='transmitting = false;'><?php echo xlt('Manual eREQ'); ?>
                                        </button>
                                    <?php } elseif ($gbl_lab === 'clarity') {
                                        echo "<a class='btn btn-outline-primary' target='_blank' href='$rootdir/procedure_tools/ereqs/ereq_universal_form.php?debug=1&formid=" . attr_url($formid) . "'>" . xlt("Manual eREQ") . "</a>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <legend class="bg-dark text-light lfont1"><?php echo xlt('Order Log'); ?></legend>
                            <div class="jumbotron m-0 px-2 py-0 overflow-auto" id="processLog" style="max-height: 500px;">
                                <?php
                                if (!empty($order_log)) {
                                    $alertmsg = $order_log;
                                } else {
                                    $order_log = $alertmsg ?? '';
                                }
                                if (!empty($alertmsg)) {
                                    echo nl2br(text($alertmsg));
                                }
                                ?>
                                <input type="hidden" name="order_log" value="<?php echo attr($order_log); ?>">
                            </div>
                        </div>
                    </div>
                </fieldset>
                <fieldset class="col-md-12">
                    <div class="my-0 py-0 text-center">
                        <?php $t = "<span class='lfont1'>" .
                            ($gbl_lab === "labcorp" ? "Location Account: $account_name $account" : "") .
                            "</span>";
                        echo "<h4>" . xlt('Procedure Order Details') . " " . text($gbl_lab_title) . "</h4> " . $t; ?>
                    </div>
                    <?php if (!empty($order_data ?? null)) { ?>
                        <div id="errorAlerts" class="alert alert-danger alert-dismissible col-6 offset-3" role="alert">
                            <button type="button" class="close" data-dismiss="alert"><span class="text-dark">&times;</span></button>
                            <p>
                                <?php echo $order_data;
                                unset($order_data); ?>
                            </p>
                        </div>
                    <?php } ?>
                    <div class="col-md-12 procedure-order-container table-responsive">
                        <?php
                        // This section merits some explanation. :)
                        //
                        // If any procedures have already been saved for this form, then a top-level table row is
                        // created for each of them, and includes the relevant questions and any existing answers.
                        // Otherwise, a single empty table row is created for entering the first or only procedure.
                        //
                        // If a new procedure is selected or changed, the questions for it are (re)generated from
                        // the dialog window from which the procedure is selected, via JavaScript.  The sel_proc_type
                        // function and the types.php script that it invokes collaborate to support this feature.
                        //
                        // The generate_qoe_html function in qoe.inc.php contains logic to generate the HTML for
                        // the questions, and can be invoked either from this script or from types.php.
                        //
                        // The $i counter that you see below is to resolve the need for unique names for form fields
                        // that may occur for each of the multiple procedure requests within the same order.
                        // procedure_order_seq serves a similar need for uniqueness at the database level.
                        ?>
                        <?php
                        $i = -1;
                        // we need to generate our template here
                        ?>
                        <template id="procedure_order_code_template">
                            <table class="table table-sm proc-table proc-table-main">
                                <tbody>
                                <tr class="bg-primary">
                                    <input type='hidden' name='form_proc_code[<?php echo $i; ?>]' value='' />
                                    <input type='hidden' name='form_proc_order_seq[<?php echo $i; ?>]' value='' />
                                    <td class="itemDelete"><i class="fa fa-trash fa-lg"></i></td>
                                    <td class="itemTransport quest">
                                        <input class="itemTransport form-control" readonly
                                            name='form_transport[]'
                                            placeholder='<?php echo xla('Click to review the Directory of Service for this test'); ?>'
                                            value=''>
                                    </td>
                                    <td class="procedure-div">
                                        <?php if (empty($formid) || empty($oprow['procedure_order_title'])) : ?>
                                            <input type="hidden" name="form_proc_order_title[<?php echo $i; ?>]"
                                                value="procedure">
                                        <?php else : ?>
                                            <input type='hidden' name='form_proc_order_title[<?php echo $i; ?>]'
                                                value=''>
                                        <?php endif; ?>
                                        <div class='input-group-prepend'>
                                            <button type="button" class='btn btn-secondary btn-search' title='<?php echo xla('Click to use procedure code from code popup'); ?>'>
                                            </button>
                                            <input type='hidden' name='form_procedure_type[<?php echo $i; ?>]' value='' />
                                            <input type='text' name='form_proc_type_desc[<?php echo $i; ?>]'
                                                value=''
                                                title='<?php echo xla('Click to select the desired procedure'); ?>'
                                                placeholder='<?php echo xla('Click to select the desired procedure'); ?>'
                                                class='form-control c-hand sel-proc-type' />
                                            <!-- the configuration type id -->
                                            <input type='hidden' name='form_proc_type[<?php echo $i; ?>]' value='-1' />
                                        </div>
                                    </td>
                                    <td class='diagnosis-div input-group'>
                                        <div class='input-group-prepend'>
                                            <span class='btn btn-secondary input-group-text'>
                                                <i class='fa fa-search search-current-diagnoses' title='<?php echo xla('Click to search past and current diagnoses history'); ?>'></i>
                                            </span>
                                        </div>
                                        <input class='form-control c-hand add-diagnosis-sel-related' type='text'
                                            name='form_proc_type_diag[<?php echo $i; ?>]'
                                            value=''
                                            title='<?php echo xla('Click to add diagnosis for this test'); ?>'
                                        />
                                    </td>
                                    <td>
                                        <!-- MSIE innerHTML property for a TABLE element is read-only, so using a DIV here. -->
                                        <div class="table-responsive qoe-table-sel-procedure" id='qoetable[<?php echo attr($i); ?>]'>
                                            <?php
                                            $qoe_init_javascript = '';
                                            echo generate_qoe_html($ptid ?? '', $formid, null, $i);
                                            if ($qoe_init_javascript) {
                                                echo "<script>$qoe_init_javascript</script>";
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <!-- Action button for reason panel -->
                                        <button class="btn btn-secondary reason-code-btn mt-2 float-right"
                                            title='<?php echo xla('Click here to provide an explanation for procedure order (or why an order was not performed)'); ?>'
                                            data-toggle-container="reason_code_<?php echo attr($i); ?>"><i class="fa fa-chevron-down"></i>
                                        </button>
                                        <button class="btn btn-secondary specimen-code-btn mt-2 mr-1 float-right"
                                            title="<?php echo xla('Click here to add one or more specimens for this test'); ?>"
                                            data-toggle-specimen="specimen_code_<?php echo attr($i); ?>">
                                            <i class="fa fa-flask"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                                $specimens = [];
                                $sp = sqlStatement(
                                    "SELECT *
                                             FROM procedure_specimen
                                            WHERE procedure_order_id = ? AND procedure_order_seq = ? AND `deleted` = 0
                                            ORDER BY procedure_specimen_id",
                                    [$formid, $oprow['procedure_order_seq']]
                                );
                                while ($r = sqlFetchArray($sp)) {
                                    // convert uuid bytes to string if your template needs to display it
                                    if (!empty($r['uuid'])) {
                                        $r['uuid_str'] = UuidRegistry::uuidToBytes($r['uuid']);
                                    }
                                    $specimens[] = $r;
                                }
                                ?>

                                <?php include "templates/procedure_reason_row.php" ?>
                                <?php include "templates/procedure_specimen_row.php" ?>
                                </tbody>
                            </table>
                        </template>
                        <?php
                        $i = 0;
                        foreach ($oparr as $oprow) {
                            $ptid = -1; // -1 means no procedure is selected yet
                            if (!empty($oprow['procedure_type_id'])) {
                                $ptid = $oprow['procedure_type_id'];
                            }
                            ?>
                            <table class="table table-sm proc-table proc-table-main" id="procedures_item_<?php echo (string)attr($i) ?>">
                                <?php if ($i < 1) { ?>
                                    <thead class="thead-dark">
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th class="quest">&nbsp;</th>
                                        <th><?php echo xlt('Procedure Test'); ?></th>
                                        <th><?php echo xlt('Diagnosis Codes'); ?></th>
                                        <th><?php echo xlt("Order Questions"); ?></th>
                                        <th class="float-right"><?php echo xlt("Actions"); ?></th>
                                    </tr>
                                    </thead>
                                <?php } ?>
                                <tbody>
                                <tr class="bg-primary">
                                    <input type='hidden' name='form_proc_code[<?php echo $i; ?>]' value='<?php echo attr($oprow['procedure_code'] ?? '') ?>' />
                                    <input type='hidden' name='form_proc_order_seq[<?php echo $i; ?>]' value='<?php echo attr($oprow['procedure_order_seq'] ?? '') ?>' />
                                    <td class="itemDelete"><i class="fa fa-trash fa-lg"></i></td>
                                    <td class="itemTransport quest">
                                        <input class="itemTransport form-control" readonly
                                            name='form_transport[<?php echo $i; ?>]' onclick='getDetails(event, <?php echo $i; ?>)'
                                            placeholder='<?php echo xla('Click to review the Directory of Service for this test'); ?>'
                                            value='<?php echo attr($oprow['transport'] ?? '') ?>'>
                                    </td>
                                    <td class="procedure-div">
                                        <?php if (empty($formid) || empty($oprow['procedure_order_title'])) : ?>
                                            <input type="hidden" name="form_proc_order_title[<?php echo $i; ?>]"
                                                value="procedure">
                                        <?php else : ?>
                                            <input type='hidden' name='form_proc_order_title[<?php echo $i; ?>]'
                                                value='<?php echo attr($oprow['procedure_order_title']) ?>'>
                                        <?php endif; ?>
                                        <div class='input-group-prepend'>
                                            <button type="button" class='btn btn-secondary btn-search' onclick='selectProcedureCode(<?php echo $i; ?>)' title='<?php echo xla('Click to use procedure code from code popup'); ?>'>
                                            </button>
                                            <input type='hidden' name='form_procedure_type[<?php echo $i; ?>]' value='<?php echo attr($oprow['procedure_type'] ?? ''); ?>' />
                                            <input type='text' name='form_proc_type_desc[<?php echo $i; ?>]'
                                                value='<?php echo attr($oprow['procedure_name']) ?>'
                                                onclick="sel_proc_type(<?php echo $i; ?>)"
                                                onfocus='this.blur()'
                                                title='<?php echo xla('Click to select the desired procedure'); ?>'
                                                placeholder='<?php echo xla('Click to select the desired procedure'); ?>'
                                                class='form-control c-hand' />
                                            <!-- the configuration type id -->
                                            <input type='hidden' name='form_proc_type[<?php echo $i; ?>]' value='<?php echo attr($ptid); ?>' />
                                        </div>
                                    </td>
                                    <td class='diagnosis-div input-group'>
                                        <div class='input-group-prepend'>
                                            <span class='btn btn-secondary input-group-text'>
                                                <i onclick='current_diagnoses(this)' class='fa fa-search' title='<?php echo xla('Click to search past and current diagnoses history'); ?>'></i>
                                            </span>
                                        </div>
                                        <input class='form-control c-hand' type='text'
                                            name='form_proc_type_diag[<?php echo $i; ?>]'
                                            value='<?php echo attr($oprow['diagnoses'] ?? '') ?>'
                                            onclick='sel_related(this.name)'
                                            title='<?php echo xla('Click to add diagnosis for this test'); ?>'
                                            onfocus='this.blur()' />
                                    </td>
                                    <td>
                                        <!-- MSIE innerHTML property for a TABLE element is read-only, so using a DIV here. -->
                                        <div class="table-responsive" id='qoetable[<?php echo attr($i); ?>]'>
                                            <?php
                                            $qoe_init_javascript = '';
                                            echo generate_qoe_html($ptid, $formid, ($oprow['procedure_order_seq'] ?? null), $i);
                                            if ($qoe_init_javascript) {
                                                echo "<script>$qoe_init_javascript</script>";
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-secondary reason-code-btn mt-2 float-right"
                                            title='<?php echo xla('Click here to provide an explanation for procedure order (or why an order was not performed)'); ?>'
                                            data-toggle-container="reason_code_<?php echo attr($i); ?>"><i class="fa fa-chevron-down"></i>
                                        </button>
                                        <button class="btn btn-secondary specimen-code-btn mt-2 mr-1 float-right"
                                            title="<?php echo xla('Click here to add one or more specimens for this test'); ?>"
                                            data-toggle-specimen="specimen_code_<?php echo attr($i); ?>">
                                            <i class="fa fa-flask"></i>
                                        </button>
                                    </td>
                                </tr>

                                <?php include "templates/procedure_reason_row.php"; ?>
                                <?php include "templates/procedure_specimen_row.php"; ?>
                                </tbody>
                            </table>
                            <?php
                            ++$i;
                        }
                        ?>
                    </div>
                    <div class="btn=group ml-4">
                        <div class="text-md-center">
                            <div class="position-override mt-2">
                                <span class="wait fa fa-cog fa-spin fa-1x ml-2 d-none"></span>
                                <button type="button" class="btn btn-success btn-add" onclick="addProcLine()"><?php echo xlt('Add Procedure'); ?></button>
                                <div class="btn-group" role="group">
                                    <button type="submit" class="btn btn-primary btn-save"
                                        name="bn_save" id="bn_save" value="save"
                                        title="<?php echo xla('Click to save current order details then continue working.'); ?>"
                                        onclick='top.restoreSession();transmitting = false;'><?php echo xlt('Save and Continue'); ?>
                                    </button>
                                    <button type="submit" class="btn btn-success btn-save"
                                        name='bn_save_exit' id='bn_save_exit' value="save_exit"
                                        title="<?php echo xla('Click to save current order details and exit.'); ?>"
                                        onclick='top.restoreSession();transmitting = false;'><?php echo xlt('Save and Exit'); ?>
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-transmit"
                                        name='bn_xmit' value="transmit"
                                        title='<?php echo xla('Click to transmit the order. Order will be saved prior to sending.'); ?>'
                                        onclick='top.restoreSession();transmitting = true;'><?php echo xlt('Transmit Order'); ?>
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-cancel"
                                        onclick="top.restoreSession();location='<?php echo $GLOBALS['form_exit_url']; ?>'"><?php echo xlt('Cancel/Exit'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div><!--end of .container -->
</body>
</html>
