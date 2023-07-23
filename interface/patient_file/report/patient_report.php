<?php

/**
 * Patient report
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2019 Stephen Nielson <stephen@nielson.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/lists.inc.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/patient.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Events\PatientReport\PatientReportEvent;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\OeUI\OemrUI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

if (!AclMain::aclCheckCore('patients', 'pat_rep')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Patient Reports")]);
    exit;
}
// get various authorization levels
$auth_notes_a  = AclMain::aclCheckCore('encounters', 'notes_a');
$auth_notes    = AclMain::aclCheckCore('encounters', 'notes');
$auth_coding_a = AclMain::aclCheckCore('encounters', 'coding_a');
$auth_coding   = AclMain::aclCheckCore('encounters', 'coding');
$auth_relaxed  = AclMain::aclCheckCore('encounters', 'relaxed');
$auth_med      = AclMain::aclCheckCore('patients', 'med');
$auth_demo     = AclMain::aclCheckCore('patients', 'demo');

/**
 * @var EventDispatcherInterface $eventDispatcher  The event dispatcher / listener object
 */
$eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
?>
<!DOCTYPE>
<html>
<head>
<title><?php echo xlt("Patient Reports"); ?></title>

<?php Header::setupHeader(['datetime-picker', 'common']); ?>
<script>

function checkAll(check) {
 var f = document.forms['report_form'];
 for (var i = 0; i < f.elements.length; ++i) {
  if (f.elements[i].type == 'checkbox') f.elements[i].checked = check;
 }
 return false;
}

function show_date_fun(){
  if(document.getElementById('show_date').checked == true){
    document.getElementById('date_div').style.display = '';
  }else{
    document.getElementById('date_div').style.display = 'none';
  }
  return;
}
<?php require_once("$include_root/patient_file/erx_patient_portal_js.php"); // jQuery for popups for eRx and patient portal ?>
</script>
<?php
$arrOeUiSettings = array(
    'heading_title' => xl('Patient Reports'),
    'include_patient_name' => true,
    'expandable' => false,
    'expandable_files' => array(),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => true,
    'help_file_name' => "report_dashboard_help.php"
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>

<body>
    <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?> mt-3">
        <div id="patient_reports"> <!-- large outer DIV -->
            <div class="row">
                <div class="col-sm-12">
                    <?php require_once("$include_root/patient_file/summary/dashboard_header.php");?>
                </div>
            </div>
            <?php
            $list_id = "report"; // to indicate nav item is active, count and give correct id
            // Collect the patient menu then build it
            $menuPatient = new PatientMenuRole();
            $menuPatient->displayHorizNavBarMenu();
            ?>

            <?php
            if ($GLOBALS['activate_ccr_ccd_report']) { // show CCR/CCD reporting options ?>
                <div class="mt-3" id="ccr_report">
                    <form name='ccr_form' id='ccr_form' method='post' action='../../../ccr/createCCR.php'>
                        <fieldset>
                            <div class="col-sm-12">
                                <span class='title oe-report-section-header'><?php echo xlt('Continuity of Care Record (CCR)'); ?></span>
                                <span class='text'>(<?php echo xlt('Pop ups need to be enabled to see these reports'); ?>)</span>
                                <br/>
                                <br/>
                                <input type='hidden' name='ccrAction' />
                                <input type='hidden' name='raw' />
                                <input type="checkbox" name="show_date" id="show_date" onchange="show_date_fun();" ><span class='text'><?php echo xlt('Use Date Range'); ?>
                                <br />
                                <br />
                                <div id="date_div" style="display: none">
                                    <div class="form-row">
                                        <div class="col-12 col-sm-2">
                                        <label for="Start" class='font-weight-bold'><?php echo xlt('Start Date');?>: </label>
                                        </div>
                                        <div class="col-12 col-sm-4">
                                            <input type='text' class='datepicker form-control' size='10' name='Start' id='Start' title='<?php echo xla('yyyy-mm-dd'); ?>' />
                                        </div>
                                        <div class="col-12 col-sm-2">
                                            <label for="End" class='font-weight-bold'><?php echo xlt('End Date');?>: </label>
                                        </div>
                                        <div class="col-12 col-sm-4">
                                            <input type='text' class='datepicker form-control' size='10' name='End' id='End' title='<?php echo xla('yyyy-mm-dd'); ?>' />
                                        </div>
                                    </div>
                                </div>
                                <br />
                                <button type="button" class="generateCCR btn btn-primary btn-save btn-sm" value="<?php echo xla('Generate Report'); ?>" ><?php echo xlt('Generate Report'); ?></button>
                                <!--<input type="button" class="generateCCR_raw" value="<?php echo xlt('Raw Report'); ?>" /> -->
                                <button type="button" class="generateCCR_download_p btn btn-primary btn-download btn-sm" value="<?php echo xla('Download'); ?>" ><?php echo xlt('Download'); ?></button>

                                <?php
                                if ($GLOBALS['phimail_enable'] == true && $GLOBALS['phimail_ccr_enable'] == true) { ?>
                                    <button type="button" class="viewCCR_send_dialog btn btn-primary btn-transmit btn-sm" value="<?php echo xla('Transmit'); ?>"><?php echo xlt('Transmit'); ?></button>
                                    <br />
                                    <div id="ccr_send_dialog" style="display: none">
                                        <br />
                                        <div class="table-responsive">
                                            <table class="table border-0">
                                                <tr>
                                                    <td>
                                                        <span class='font-weight-bold'><?php echo xlt('Enter Recipient\'s Direct Address');?>: </span>
                                                        <input type="text" size="64" name="ccr_send_to" id="ccr_send_to" value="" />
                                                        <input type="hidden" name="ccr_sent_by" id="ccr_sent_by" value="user" />
                                                        <button type="button" class="viewCCR_transmit btn btn-primary btn-send-msg btn-sm" value="<?php echo xla('Send CCR'); ?>"><?php echo xlt('Send CCR'); ?></button>
                                                        <div id="ccr_send_result" style="display: none">
                                                            <span class="text" id="ccr_send_message"></span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <?php } ?>
                            </div>
                        </fieldset>
                        <hr/>
                        <fieldset>
                            <div class="col-sm-12">
                                <span class='title oe-report-section-header'><?php echo xlt('Continuity of Care Document (CCD)'); ?></span>&nbsp;&nbsp;
                                <span class='text'>(<?php echo xlt('Pop ups need to be enabled to see these reports'); ?>)</span>
                                <br/>
                                <br/>
                                <button type="button" class="viewCCD btn btn-primary btn-save btn-sm" value="<?php echo xla('Generate Report'); ?>" ><?php echo xlt('Generate Report'); ?></button>
                                <button type="button" class="viewNewCCD btn btn-primary btn-save btn-sm" value="<?php echo xla('Generate Report'); ?>" ><?php echo xlt('Generate New Report'); ?></button>
                                <button type="button" class="viewCCD_download btn btn-primary btn-download btn-sm" value="<?php echo xla('Download'); ?>" ><?php echo xlt('Download'); ?></button>
                                <?php
                                if ($GLOBALS['phimail_enable'] == true && $GLOBALS['phimail_ccd_enable'] == true) { ?>
                                    <button type="button" class="viewCCD_send_dialog btn btn-primary btn-transmit btn-sm" value="<?php echo xla('Transmit'); ?>" ><?php echo xlt('Transmit'); ?></button>
                                    <br />
                                    <div id="ccd_send_dialog" style="display: none">
                                        <div class="form-row mt-3">
                                            <div class="col-12">
                                                <label for="" class="font-weight-bold">
                                                    <?php echo xlt('Enter Recipient\'s Direct Address');?>:
                                                </label>
                                            </div>
                                            <div class="col-md">
                                                <input type="text" class="form-control" size="64" name="ccd_send_to" id="ccd_send_to" value="" />
                                                <input type="hidden" name="ccd_sent_by" id="ccd_sent_by" value="user" />
                                            </div>
                                            <div class="col-md">
                                                <button type="button" class="viewCCD_transmit btn btn-primary btn-send-msg btn-sm" value="<?php echo xla('Send CCD'); ?>"><?php echo xlt('Send CCD'); ?></button>
                                            </div>
                                        </div>
                                        <div id="ccd_send_result" style="display: none">
                                            <span class="text" id="ccd_send_message"></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </fieldset>
                    </form>
                    <hr/>
                </div>
                <?php
            } // end CCR/CCD reporting options ?>

            <form name='report_form' id="report_form" method='post' action='custom_report.php'>
                <fieldset>
                    <div class="col-sm-12">
                        <span class='title oe-report-section-header'><?php echo xlt('Patient Report'); ?></span>&nbsp;&nbsp;
                        <!--
                        <a class="link_submit" href="full_report.php" onclick="top.restoreSession()">
                        [<?php echo xlt('View Comprehensive Patient Report'); ?>]</a>
                        -->
                        <a class="link_submit btn btn-secondary btn-sm btn-save" href="#" onclick="return checkAll(true)">
                            <?php echo xla('Check All'); ?>
                        </a>
                        <a class="link_submit btn btn-secondary btn-sm btn-undo" href="#" onclick="return checkAll(false)">
                            <?php echo xla('Clear All'); ?>
                        </a>

                        <table class="includes mt-3">
                            <tr>
                                <td class='text'>
                                    <input type='checkbox' name='include_demographics' id='include_demographics' value="demographics" checked /><?php echo xlt('Demographics'); ?>
                                    <br />
                                    <?php if (AclMain::aclCheckCore('patients', 'med')) : ?>
                                    <input type='checkbox' name='include_history' id='include_history' value="history" /><?php echo xlt('History'); ?>
                                    <br />
                                    <?php endif; ?>
                                    <!--
                                    <input type='checkbox' name='include_employer' id='include_employer' value="employer"><?php echo xlt('Employer'); ?><br />
                                    -->
                                    <input type='checkbox' name='include_insurance' id='include_insurance' value="insurance" /><?php echo xlt('Insurance'); ?>
                                    <br />
                                    <input type='checkbox' name='include_billing' id='include_billing' value="billing"
                                    <?php
                                    if (!$GLOBALS['simplified_demographics']) {
                                        echo 'checked';
                                    } ?> /><?php echo xlt('Billing'); ?>
                                    <br />
                                </td>
                                <td class='text'>
                                    <!--
                                    <input type='checkbox' name='include_allergies' id='include_allergies' value="allergies">Allergies<br />
                                    <input type='checkbox' name='include_medications' id='include_medications' value="medications">Medications<br />
                                    -->
                                    <input type='checkbox' name='include_immunizations' id='include_immunizations' value="immunizations" /><?php echo xlt('Immunizations'); ?>
                                    <br />
                                    <!--
                                    <input type='checkbox' name='include_medical_problems' id='include_medical_problems' value="medical_problems">Medical Problems<br />
                                    -->
                                    <input type='checkbox' name='include_notes' id='include_notes' value="notes" /><?php echo xlt('Patient Notes'); ?>
                                    <br />
                                    <input type='checkbox' name='include_transactions' id='include_transactions' value="transactions" /><?php echo xlt('Transactions'); ?>
                                    <br />
                                    <input type='checkbox' name='include_batchcom' id='include_batchcom' value="batchcom" /><?php echo xlt('Communications'); ?>
                                    <br />
                                </td>
                                <td class="text">
                                    <input type='checkbox' name='include_recurring_days' id='include_recurring_days' value="recurring_days" /><?php echo  xlt('Recurrent Appointments'); ?>
                                    <br />
                                </td>
                            </tr>
                        </table>
                        <br />
                        <button type="button" class="genreport btn btn-primary btn-save btn-sm" value="<?php echo xla('Generate Report'); ?>" ><?php echo xlt('Generate Report'); ?></button>
                        <button type="button" class="genpdfrep btn btn-primary btn-download btn-sm" value="<?php echo xla('Download PDF'); ?>" ><?php echo xlt('Download PDF'); ?></button>

                        <?php

                            $eventDispatcher->dispatch(new GenericEvent(), PatientReportEvent::ACTIONS_RENDER_POST);

                        ?>
                        <input type='hidden' name='pdf' value='0' />
                        <br />

                        <!-- old ccr button position -->
                        <hr/>

                        <div class="row issues_encounters_forms">
                            <!-- Issues -->
                            <div class="col-md-6">
                                <div class="issues table-responsive">
                                    <span class='font-weight-bold oe-report-section-header'><?php echo xlt('Issues'); ?>:</span>
                                    <br />
                                    <br />

                                    <?php if (! AclMain::aclCheckCore('patients', 'med')) { ?>
                                    <br />(Issues not authorized)
                                    <?php } else { ?>
                                    <table class="table table-borderless">
                                        <?php
                                        // get issues
                                        $pres = sqlStatement("SELECT * FROM lists WHERE pid = ? " .
                                        "ORDER BY type, begdate", array($pid));
                                        $lasttype = "";
                                        while ($prow = sqlFetchArray($pres)) {
                                            if ($lasttype != $prow['type']) {
                                                $lasttype = $prow['type'];

                                                /****
                                                $disptype = $lasttype;
                                                switch ($lasttype) {
                                                case "allergy"        : $disptype = "Allergies"       ; break;
                                                case "problem"        :
                                                case "medical_problem": $disptype = "Medical Problems"; break;
                                                case "medication"     : $disptype = "Medications"     ; break;
                                                case "surgery"        : $disptype = "Surgeries"       ; break;
                                                }
                                                ****/
                                                $disptype = $ISSUE_TYPES[$lasttype][0];

                                                echo " <tr>\n";
                                                echo "  <td colspan='4' class='font-weight-bold'><span class='oe-report-section-header'>" . xlt($disptype) . ":</span></td>\n";
                                                echo " </tr>\n";
                                            }

                                            $rowid = $prow['id'];
                                            $disptitle = trim($prow['title']) ? $prow['title'] : "[Missing Title]";

                                            $ieres = sqlStatement("SELECT encounter FROM issue_encounter WHERE " .
                                            "pid = ? AND list_id = ?", array($pid, $rowid));

                                            echo "    <tr class='text'>\n";
                                            echo "     <td>&nbsp;</td>\n";
                                            echo "     <td>";
                                            echo "<input type='checkbox' name='issue_" . attr($rowid) . "' id='issue_" . attr($rowid) . "' class='issuecheckbox' value='/";
                                            while ($ierow = sqlFetchArray($ieres)) {
                                                echo attr($ierow['encounter']) . "/";
                                            }

                                            echo "' />" . text($disptitle) . "</td>\n";
                                            echo "     <td>" . text($prow['begdate']);

                                            if ($prow['enddate']) {
                                                echo " - " . text($prow['enddate']);
                                            } else {
                                                echo " Active";
                                            }

                                            echo "</td>\n";
                                            echo "</tr>\n";
                                        }
                                        ?>
                                    </table>
                                    <?php } // end of Issues output ?>
                                </div> <!-- end issues DIV -->
                                <button type="button" class="genreport btn btn-primary btn-save btn-sm" value="<?php echo xla('Generate Report'); ?>" ><?php echo xlt('Generate Report'); ?></button>
                                <button type="button" class="genpdfrep btn btn-primary btn-download btn-sm" value="<?php echo xla('Download PDF'); ?>" ><?php echo xlt('Download PDF'); ?></button>
                            </div>
                            <!-- Encounters and Forms -->
                            <div class="col-md-6">
                                <div class='encounters table-responsive'>
                                    <span class='font-weight-bold oe-report-section-header'><?php echo xlt('Encounters & Forms'); ?>:</span>
                                    <br />
                                    <br />

                                    <?php
                                    if (!($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) { ?>
                                        (Encounters not authorized)
                                        <?php
                                    } else { ?>
                                        <?php
                                        $isfirst = 1;
                                        $res = sqlStatement("SELECT forms.encounter, forms.form_id, forms.form_name, " .
                                        "forms.formdir, forms.date AS fdate, form_encounter.date " .
                                        ",form_encounter.reason " .
                                        "FROM forms, form_encounter WHERE " .
                                        "forms.pid = ? AND form_encounter.pid = ? AND " .
                                        "form_encounter.encounter = forms.encounter " .
                                        " AND forms.deleted=0 " . // --JRM--
                                        "ORDER BY form_encounter.encounter DESC, form_encounter.date DESC, fdate ASC", array($pid, $pid));
                                        $res2 = sqlStatement("SELECT name FROM registry ORDER BY priority");
                                        $html_strings = array();
                                        $registry_form_name = array();
                                        while ($result2 = sqlFetchArray($res2)) {
                                            array_push($registry_form_name, trim($result2['name']));
                                        }

                                        while ($result = sqlFetchArray($res)) {
                                            if ($result["form_name"] == "New Patient Encounter") {
                                                if ($isfirst == 0) {
                                                    foreach ($registry_form_name as $var) {
                                                        if ($toprint = ($html_strings[$var] ?? '')) {
                                                            foreach ($toprint as $var) {
                                                                print $var;
                                                            }
                                                        }
                                                    }
                                                    $html_strings = array();
                                                    echo "</div>\n"; // end DIV encounter_forms
                                                    echo "</div>\n\n";  //end DIV encounter_data
                                                    echo "<br />";
                                                }
                                                $isfirst = 0;
                                                echo "<div class='encounter_data'>\n";
                                                echo "<input type=checkbox " .
                                                " name='" . attr($result["formdir"]) . "_" .  attr($result["form_id"]) . "'" .
                                                " id='" . attr($result["formdir"]) . "_" .  attr($result["form_id"]) . "'" .
                                                " value='" . attr($result["encounter"]) . "'" .
                                                " class='encounter'" .
                                                " >";
                                                // show encounter reason, not just 'New Encounter'
                                                // trim to a reasonable length for display purposes --cfapress
                                                $maxReasonLength = 20;
                                                if (strlen($result["reason"]) > $maxReasonLength) {
                                                    // The default encoding for this mb_substr() call is set near top of globals.php
                                                    $result['reason'] = mb_substr($result['reason'], 0, $maxReasonLength) . " ... ";
                                                }
                                                echo text($result["reason"]) .
                                                " (" . text(date("Y-m-d", strtotime($result["date"]))) .
                                                ")\n";
                                                echo "<div class='encounter_forms'>\n";
                                            } else {
                                                $form_name = trim($result["form_name"]);
                                                //if form name is not in registry, look for the closest match by
                                                // finding a registry name which is  at the start of the form name.
                                                //this is to allow for forms to put additional helpful information
                                                //in the database in the same string as their form name after the name
                                                $form_name_found_flag = 0;
                                                foreach ($registry_form_name as $var) {
                                                    if ($var == $form_name) {
                                                        $form_name_found_flag = 1;
                                                    }
                                                }
                                                // if the form does not match precisely with any names in the registry, now see if any front partial matches
                                                // and change $form_name appropriately so it will print above in $toprint = $html_strings[$var]
                                                if (!$form_name_found_flag) {
                                                    foreach ($registry_form_name as $var) {
                                                        if (strpos($form_name, $var) == 0) {
                                                            $form_name = $var;
                                                        }
                                                    }
                                                }
                                                if (empty($html_strings[$form_name]) || !is_array($html_strings[$form_name])) {
                                                    $html_strings[$form_name] = array();
                                                }
                                                array_push($html_strings[$form_name], "<input type='checkbox' " .
                                                    " name='" . attr($result["formdir"]) . "_" . attr($result["form_id"]) . "'" .
                                                    " id='" . attr($result["formdir"]) . "_" . attr($result["form_id"]) . "'" .
                                                    " value='" . attr($result["encounter"]) . "'" .
                                                    " class='encounter_form' " .
                                                    ">" . text(xl_form_title($result["form_name"])) . "<br />\n");
                                            }
                                        }

                                        foreach ($registry_form_name as $var) {
                                            if (!empty($html_strings[$var])) {
                                                if ($toprint = $html_strings[$var]) {
                                                    foreach ($toprint as $var) {
                                                        print $var;
                                                    }
                                                }
                                            }
                                        }
                                        ?>

                                        <?php
                                    } ?>
                                </div> <!-- end encounters DIV -->
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <!-- Procedure Orders -->
                        <hr/>
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tr>
                                    <td class='font-weight-bold'><span class='oe-report-section-header'><?php echo xlt('Procedures'); ?>:</span></td>
                                    <td class='text'>&nbsp;<?php echo xlt('Order Date'); ?>&nbsp;&nbsp;</td>
                                    <td class='text'><?php echo xlt('Encounter Date'); ?>&nbsp;&nbsp;</td>
                                    <td class='text'><?php echo xlt('Order Descriptions'); ?></td>
                                </tr>
                                <?php
                                $res = sqlStatement(
                                    "SELECT po.procedure_order_id, po.date_ordered, fe.date " .
                                    "FROM procedure_order AS po " .
                                    "LEFT JOIN forms AS f ON f.pid = po.patient_id AND f.formdir = 'procedure_order' AND " .
                                    "f.form_id = po.procedure_order_id AND f.deleted = 0 " .
                                    "LEFT JOIN form_encounter AS fe ON fe.pid = f.pid AND fe.encounter = f.encounter " .
                                    "WHERE po.patient_id = ? " .
                                    "ORDER BY po.date_ordered DESC, po.procedure_order_id DESC",
                                    array($pid)
                                );
                                while ($row = sqlFetchArray($res)) {
                                    $poid = $row['procedure_order_id'];
                                    echo " <tr>\n";
                                    echo "  <td class='text text-center'>" .
                                    "<input type='checkbox' name='procedures[]' value='" . attr($poid) . "' />&nbsp;&nbsp;</td>\n";
                                    echo "  <td class='text'>" . text(oeFormatShortDate($row['date_ordered'])) . "&nbsp;&nbsp;</td>\n";
                                    echo "  <td class='text'>" . text(oeFormatShortDate($row['date'])) . "&nbsp;&nbsp;</td>\n";
                                    echo "  <td class='text'>";
                                    $opres = sqlStatement(
                                        "SELECT procedure_code, procedure_name FROM procedure_order_code " .
                                        "WHERE procedure_order_id = ? ORDER BY procedure_order_seq",
                                        array($poid)
                                    );
                                    while ($oprow = sqlFetchArray($opres)) {
                                        $tmp = $oprow['procedure_name'];
                                        if (empty($tmp)) {
                                            $tmp = $oprow['procedure_code'];
                                        }
                                        echo text($tmp) . "<br />";
                                    }
                                    echo "</td>\n";
                                    echo " </tr>\n";
                                }
                                ?>
                            </table>
                        </div>

                        <button type="button" class="genreport btn btn-primary btn-save btn-sm" value="<?php echo xla('Generate Report'); ?>" ><?php echo xlt('Generate Report'); ?></button>
                        <button type="button" class="genpdfrep btn btn-primary btn-download btn-sm" value="<?php echo xla('Download PDF'); ?>" ><?php echo xlt('Download PDF'); ?></button>
                        <hr/>
                    <div>

                    <span class="font-weight-bold oe-report-section-header"><?php echo xlt('Documents'); ?>:</span><br />
                    <ul>
                        <?php
                        // show available documents
                        $db = $GLOBALS['adodb']['db'];
                        $sql = "SELECT d.id, d.url, d.name as document_name, c.name, c.aco_spec FROM documents AS d " .
                                "LEFT JOIN categories_to_documents AS ctd ON d.id=ctd.document_id " .
                                "LEFT JOIN categories AS c ON c.id = ctd.category_id WHERE " .
                                "d.foreign_id = ? AND d.deleted = 0";
                        $result = $db->Execute($sql, array($pid));
                        if ($db->ErrorMsg()) {
                            echo $db->ErrorMsg();
                        }
                        while ($result && !$result->EOF) {
                            if (empty($result->fields['aco_spec']) || AclMain::aclCheckAcoSpec($result->fields['aco_spec'])) {
                                echo "<li class='font-weight-bold'>";
                                echo '<input type="checkbox" name="documents[]" value="' .
                                attr($result->fields['id']) . '">';
                                echo '&nbsp;&nbsp;<i>' .  text(xl_document_category($result->fields['name'])) . "</i>";
                                echo '&nbsp;&nbsp;' . xlt('Name') . ': <i>' . text($result->fields['document_name']) . '-' . text($result->fields['id']) . "</i>";
                                echo '</li>';
                            }
                            $result->MoveNext();
                        }
                        ?>
                    </ul>
                    <button type="button" class="genreport btn btn-primary btn-save btn-sm" value="<?php echo xla('Generate Report'); ?>" ><?php echo xlt('Generate Report'); ?></button>
                    <button type="button" class="genpdfrep btn btn-primary btn-download btn-sm" value="<?php echo xla('Download PDF'); ?>" ><?php echo xlt('Download PDF'); ?></button>
                </fieldset>
            </form>
        </div>  <!-- close patient_reports DIV -->
    </div><!--end of container div-->
    <?php $oemr_ui->oeBelowContainerDiv();?>

<script>

// jQuery stuff to make the page a little easier to use
$(function () {
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });

    $(".genreport").click(function() { top.restoreSession(); document.report_form.pdf.value = 0; $("#report_form").submit(); });
    $(".genpdfrep").click(function() { top.restoreSession(); document.report_form.pdf.value = 1; $("#report_form").submit(); });
    $(".genportal").click(function() { top.restoreSession(); document.report_form.pdf.value = 2; $("#report_form").submit(); });
    $("#genfullreport").click(function() { location.href='<?php echo "$rootdir/patient_file/encounter/" . ($returnurl ?? ''); ?>'; });
    //$("#printform").click(function() { PrintForm(); });
    $(".issuecheckbox").click(function() { issueClick(this); });

    // check/uncheck all Forms of an encounter
    $(".encounter").click(function() { SelectForms($(this)); });

    $(".generateCCR").click(function() {
        if(document.getElementById('show_date').checked == true){
            if(document.getElementById('Start').value == '' || document.getElementById('End').value == ''){
                alert(<?php echo xlj('Please select a start date and end date') ?>);
                return false;
            }
        }
        var ccrAction = document.getElementsByName('ccrAction');
        ccrAction[0].value = 'generate';
        var raw = document.getElementsByName('raw');
        raw[0].value = 'no';
        top.restoreSession();
        ccr_form.setAttribute("target", "_blank");
        $("#ccr_form").submit();
        ccr_form.setAttribute("target", "");
    });
    $(".generateCCR_raw").click(function() {
        var ccrAction = document.getElementsByName('ccrAction');
        ccrAction[0].value = 'generate';
        var raw = document.getElementsByName('raw');
        raw[0].value = 'yes';
        top.restoreSession();
        ccr_form.setAttribute("target", "_blank");
        $("#ccr_form").submit();
        ccr_form.setAttribute("target", "");
    });
    $(".generateCCR_download_h").click(function() {
        var ccrAction = document.getElementsByName('ccrAction');
        ccrAction[0].value = 'generate';
        var raw = document.getElementsByName('raw');
        raw[0].value = 'hybrid';
        top.restoreSession();
        $("#ccr_form").submit();
    });
    $(".generateCCR_download_p").click(function() {
        if(document.getElementById('show_date').checked == true){
            if(document.getElementById('Start').value == '' || document.getElementById('End').value == ''){
                alert(<?php echo xlj('Please select a start date and end date'); ?>);
                return false;
            }
        }
        var ccrAction = document.getElementsByName('ccrAction');
        ccrAction[0].value = 'generate';
        var raw = document.getElementsByName('raw');
        raw[0].value = 'pure';
        top.restoreSession();
        $("#ccr_form").submit();
    });
    $(".viewNewCCD").click(function() {
        // there's a lot of ways to do this but for now, we'll go with this!
        top.restoreSession();
        let url = './../../../ccdaservice/ccda_gateway.php?action=report_ccd_view&csrf_token_form=' +
            encodeURIComponent("<?php echo CsrfUtils::collectCsrfToken() ?>");
        fetch(url, {
            credentials: 'same-origin',
            method: 'GET',
        })
        .then(response => response.text())
        .then(response => {
            let view = window.open('about:blank', '_blank');
            view.document.write(response);
            view.document.close();
            return false;
        })
    });
    $(".viewCCD").click(function() {
        var ccrAction = document.getElementsByName('ccrAction');
        ccrAction[0].value = 'viewccd';
        var raw = document.getElementsByName('raw');
        raw[0].value = 'no';
        top.restoreSession();
        ccr_form.setAttribute("target", "_blank");
        $("#ccr_form").submit();
        ccr_form.setAttribute("target", "");
    });
    $(".viewCCD_raw").click(function() {
        var ccrAction = document.getElementsByName('ccrAction');
        ccrAction[0].value = 'viewccd';
        var raw = document.getElementsByName('raw');
        raw[0].value = 'yes';
        top.restoreSession();
        ccr_form.setAttribute("target", "_blank");
        $("#ccr_form").submit();
        ccr_form.setAttribute("target", "");
    });
    $(".viewCCD_download").click(function() {
        var ccrAction = document.getElementsByName('ccrAction');
        ccrAction[0].value = 'viewccd';
        var raw = document.getElementsByName('raw');
        raw[0].value = 'pure';
        $("#ccr_form").submit();
    });

    <?php if ($GLOBALS['phimail_enable'] == true && $GLOBALS['phimail_ccr_enable'] == true) { ?>
        $(".viewCCR_send_dialog").click(function() {
            $("#ccr_send_dialog").toggle();
        });
        $(".viewCCR_transmit").click(function() {
            $(".viewCCR_transmit").attr('disabled','disabled');
            var ccrAction = document.getElementsByName('ccrAction');
            ccrAction[0].value = 'generate';
            var ccrRecipient = $("#ccr_send_to").val();
            var raw = document.getElementsByName('raw');
            raw[0].value = 'send '+ccrRecipient;
            if(ccrRecipient=="") {
                    $("#ccr_send_message").html(<?php
                        echo xlj('Please enter a valid Direct Address above.'); ?>);
                    $("#ccr_send_result").show();
            } else {
                    $(".viewCCR_transmit").attr('disabled','disabled');
                    $("#ccr_send_message").html(<?php
                        echo xlj('Working... this may take a minute.'); ?>);
                    $("#ccr_send_result").show();
                    var action=$("#ccr_form").attr('action');
                    $.post(action,
                        {
                            ccrAction:'generate',
                            raw:'send '+ccrRecipient,
                            requested_by:'user'
                        },
                        function(data) {
                            if(data=="SUCCESS") {
                                $("#ccr_send_message").html(<?php
                                    echo xlj('Your message was submitted for delivery to');
                                ?>+ " " + ccrRecipient);
                                $("#ccr_send_to").val("");
                            } else {
                                $("#ccr_send_message").html(data);
                            }
                            $(".viewCCR_transmit").removeAttr('disabled');
                    });
                    }
        });
    <?php }

    if ($GLOBALS['phimail_enable'] == true && $GLOBALS['phimail_ccd_enable'] == true) { ?>
        $(".viewCCD_send_dialog").click(function() {
            $("#ccd_send_dialog").toggle();
        });
        $(".viewCCD_transmit").click(function() {
            $(".viewCCD_transmit").attr('disabled','disabled');
            var ccrAction = document.getElementsByName('ccrAction');
            ccrAction[0].value = 'viewccd';
            var ccdRecipient = $("#ccd_send_to").val();
            var raw = document.getElementsByName('raw');
            raw[0].value = 'send '+ccdRecipient;
                if(ccdRecipient=="") {
                        $("#ccd_send_message").html(<?php
                            echo xlj('Please enter a valid Direct Address above.'); ?>);
                        $("#ccd_send_result").show();
                } else {
                        $(".viewCCD_transmit").attr('disabled','disabled');
                        $("#ccd_send_message").html(<?php
                            echo xlj('Working... this may take a minute.'); ?>);
                        $("#ccd_send_result").show();
                        var action=$("#ccr_form").attr('action');
                        $.post(action,
                            {
                            ccrAction:'viewccd',
                            raw:'send '+ccdRecipient,
                            requested_by:'user'
                            },
                            function(data) {
                            if(data=="SUCCESS") {
                                $("#ccd_send_message").html(<?php
                                    echo xlj('Your message was submitted for delivery to');
                                ?> + " " + ccdRecipient);
                                $("#ccd_send_to").val("");
                            } else {
                                $("#ccd_send_message").html(data);
                            }
                            $(".viewCCD_transmit").removeAttr('disabled');
                        });
                }
        });
    <?php } ?>

    <?php
        //event dispatch
        $eventDispatcher->dispatch(new GenericEvent(), PatientReportEvent::JAVASCRIPT_READY_POST);

    ?>

});

// select/deselect the Forms related to the selected Encounter
// (it ain't pretty code folks)
var SelectForms = function (selectedEncounter) {
    if ($(selectedEncounter).prop("checked")) {
        $(selectedEncounter).parent().children().each(function(i, obj) {
            $(this).children().each(function(i, obj) {
                $(this).prop("checked", true);
            });
        });
    }
    else {
        $(selectedEncounter).parent().children().each(function(i, obj) {
            $(this).children().each(function(i, obj) {
                $(this).prop("checked", false);
            });
        });
    }
}

// When an issue is checked, auto-check all the related encounters and forms
function issueClick(issue) {
    // do nothing when unchecked
    if (! $(issue).prop("checked")) return;

    $("#report_form :checkbox").each(function(i, obj) {
        if ($(issue).val().indexOf('/' + $(this).val() + '/') >= 0) {
            $(this).attr("checked", "checked");
        }
    });
}

var listId = '#' + <?php echo js_escape($list_id); ?>;
$(function () {
    $(listId).addClass("active");
});

</script>
</body>
</html>
