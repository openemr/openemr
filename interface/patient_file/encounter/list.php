<?php

use ESign\Api as ESignApi;

require_once "../../globals.php";
require_once "{$GLOBALS['srcdir']}/registry.inc";
require_once("$srcdir/forms.inc");
require_once("$srcdir/group.inc");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/amc.php");
require_once $GLOBALS['srcdir'] . '/ESign/Api.php';
require_once("$srcdir/../controllers/C_Document.class.php");
require_once("forms_review_header.php");

$is_group = ($attendant_type == 'gid') ? true : false;
if ($attendant_type == 'gid') {
    $groupId = $therapy_group;
}
$attendant_id = $attendant_type == 'pid' ? $pid : $therapy_group;
if ($is_group && !acl_check("groups", "glog", false, array('view', 'write'))) {
    echo xlt("access not allowed");
    exit();
}

$esignApi = new ESignApi();
$providerIDres = getProviderIdOfEncounter($encounter);
$providerNameRes = getProviderName($providerIDres);
?>

<div class='encounter-summary-container'>
    <div class='encounter-summary-column'>
        <div>
            <?php
            $auth_notes_a = acl_check('encounters', 'notes_a');
            $auth_notes = acl_check('encounters', 'notes');
            $auth_relaxed = acl_check('encounters', 'relaxed');

            if ($attendant_type == 'pid' && is_numeric($pid)) {
                // Check for no access to the patient's squad.
                $result = getPatientData($pid, "fname,lname,squad");
                if ($result['squad'] && !acl_check('squads', $result['squad'])) {
                    $auth_notes_a = $auth_notes = $auth_relaxed = 0;
                }
                // Check for no access to the encounter's sensitivity level.
                $result = sqlQuery("SELECT sensitivity FROM form_encounter WHERE " .
                    "pid = '$pid' AND encounter = '$encounter' LIMIT 1");
                if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
                    $auth_notes_a = $auth_notes = $auth_relaxed = 0;
                }
                // for therapy group
            } else {
                // Check for no access to the patient's squad.
                $result = getGroup($groupId);
                if ($result['squad'] && !acl_check('squads', $result['squad'])) {
                    $auth_notes_a = $auth_notes = $auth_relaxed = 0;
                }
                // Check for no access to the encounter's sensitivity level.
                $result = sqlQuery("SELECT sensitivity FROM form_groups_encounter WHERE " .
                    "group_id = ? AND encounter = ? LIMIT 1", array($groupId, $encounter));
                if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
                    $auth_notes_a = $auth_notes = $auth_relaxed = 0;
                }
            }
            ?>
        </div>
        <div style='margin-top:8px;'>
            <?php
            // ESign for entire encounter
            $esign = $esignApi->createEncounterESign($encounter);
            if ($esign->isButtonViewable()) {
                echo $esign->buttonHtml();
            }
            ?>


        </div>
    </div>

    <div class='encounter-summary-column'>
        <?php if ($esign->isLogViewable()) {
            $esign->renderLog();
        } ?>
    </div>

    <div class='encounter-summary-column'>
        <?php if ($GLOBALS['enable_amc_prompting']) { ?>
            <div
                style='float:right;margin-right:25px;border-style:solid;border-width:1px;'>
                <div style='float:left;margin:5px 5px 5px 5px;'>
                    <table>
                        <tr>
                            <td>
                                <?php // Display the education resource checkbox (AMC prompting)
                                $itemAMC = amcCollect("patient_edu_amc", $pid, 'form_encounter', $encounter);
                                ?>
                                <?php if (!(empty($itemAMC))) { ?>
                                    <input type="checkbox" id="prov_edu_res"
                                           checked>
                                <?php } else { ?>
                                    <input type="checkbox"
                                           id="prov_edu_res">
                                <?php } ?>
                            </td>
                            <td>
                        <span
                            class="text"><?php echo xl('Provided Education Resource(s)?') ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php // Display the Provided Clinical Summary checkbox (AMC prompting)
                                $itemAMC = amcCollect("provide_sum_pat_amc", $pid, 'form_encounter', $encounter);
                                ?>
                                <?php if (!(empty($itemAMC))) { ?>
                                    <input type="checkbox"
                                           id="provide_sum_pat_flag"
                                           checked>
                                <?php } else { ?>
                                    <input type="checkbox"
                                           id="provide_sum_pat_flag">
                                <?php } ?>
                            </td>
                            <td>
                        <span
                            class="text"><?php echo xl('Provided Clinical Summary?') ?></span>
                            </td>
                        </tr>
                        <?php // Display the medication reconciliation checkboxes (AMC prompting)
                        $itemAMC = amcCollect("med_reconc_amc", $pid, 'form_encounter', $encounter);
                        ?>
                        <?php if (!(empty($itemAMC))) { ?>
                        <tr>
                            <td>
                                <input type="checkbox" id="trans_trand_care"
                                       checked>
                            </td>
                            <td>
                        <span
                            class="text"><?php echo xl('Transition/Transfer of Care?') ?></span>
                            </td>
                        </tr>
                    </table>
                    <table style="margin-left:2em;">
                        <tr>
                            <td>
                                <?php if (!(empty($itemAMC['date_completed']))) { ?>
                                    <input type="checkbox"
                                           id="med_reconc_perf" checked>
                                <?php } else { ?>
                                    <input type="checkbox"
                                           id="med_reconc_perf">
                                <?php } ?>
                            </td>
                            <td>
                        <span
                            class="text"><?php echo xl('Medication Reconciliation Performed?') ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php if (!(empty($itemAMC['soc_provided']))) { ?>
                                    <input type="checkbox" id="soc_provided"
                                           checked>
                                <?php } else { ?>
                                    <input type="checkbox"
                                           id="soc_provided">
                                <?php } ?>
                            </td>
                            <td>
                        <span
                            class="text"><?php echo xl('Summary Of Care Provided?') ?></span>
                            </td>
                        </tr>
                    </table>
                    <?php } else { ?>
                        <tr>
                            <td>
                                <input type="checkbox"
                                       id="trans_trand_care">
                            </td>
                            <td>
                        <span
                            class="text"><?php echo xl('Transition/Transfer of Care?') ?></span>
                            </td>
                        </tr>
                        </table>
                        <table style="margin-left:2em;">
                            <tr>
                                <td>
                                    <input type="checkbox"
                                           id="med_reconc_perf" DISABLED>
                                </td>
                                <td>
                            <span
                                class="text"><?php echo xl('Medication Reconciliation Performed?') ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" id="soc_provided"
                                           DISABLED>
                                </td>
                                <td>
                            <span
                                class="text"><?php echo xl('Summary of Care Provided?') ?></span>
                                </td>
                            </tr>
                        </table>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>

<!-- Get the documents tagged to this encounter and display the links and notes as the tooltip -->
<?php
if ($attendant_type == 'pid') {
    $docs_list = getDocumentsByEncounter($pid, $_SESSION['encounter']);
} else {
    // already doesn't exist document for therapy groups
    $docs_list = array();
}
if (count($docs_list) > 0) {
    ?>
    <div class='enc_docs'>
        <span class="bold"><?php echo xlt("Document(s)"); ?>:</span>
        <?php
        $doc = new C_Document();
        foreach ($docs_list as $doc_iter) {
            $doc_url = $doc->_tpl_vars[CURRENT_ACTION] . "&view&patient_id=" . attr($pid) . "&document_id=" . attr($doc_iter[id]) . "&";
            // Get notes for this document.
            $queryString = "SELECT GROUP_CONCAT(note ORDER BY date DESC SEPARATOR '|') AS docNotes, GROUP_CONCAT(date ORDER BY date DESC SEPARATOR '|') AS docDates
FROM notes WHERE foreign_id = ? GROUP BY foreign_id";
            $noteData = sqlQuery($queryString, array($doc_iter[id]));
            $note = '';
            if ($noteData) {
                $notes = array();
                $notes = explode("|", $noteData['docNotes']);
                $dates = explode("|", $noteData['docDates']);
                for ($i = 0; $i < count($notes); $i++)
                    $note .= oeFormatShortDate(date('Y-m-d', strtotime($dates[$i]))) . " : " . $notes[$i] . "\n";
            }
            ?>
            <br>
            <a href="<?php echo $doc_url; ?>" style="font-size:small;"
               onsubmit="return top.restoreSession()"><?php echo oeFormatShortDate($doc_iter[docdate]) . ": " . text(basename($doc_iter[url])); ?></a>
            <?php if ($note != '') { ?>
                <a href="javascript:void(0);"
                   title="<?php echo attr($note); ?>"><img
                        src="../../../images/info.png"/></a>
            <?php } ?>
        <?php } ?>
    </div>
<?php } ?>
<br/>

<?php

if ($result = getFormByEncounter($attendant_id, $encounter, "id, date, form_id, form_name, formdir, user, deleted")) {
    echo "<table width='100%' id='partable'>";
    $divnos = 1;
    foreach ($result as $iter) {
        $formdir = $iter['formdir'];

        // skip forms whose 'deleted' flag is set to 1
        if ($iter['deleted'] == 1) continue;

        $aco_spec = false;

        if (substr($formdir, 0, 3) == 'LBF') {
            // Skip LBF forms that we are not authorized to see.
            $lrow = sqlQuery("SELECT * FROM list_options WHERE " .
                "list_id = 'lbfnames' AND option_id = ? AND activity = 1",
                array($formdir));
            if (!empty($lrow)) {
                $jobj = json_decode($lrow['notes'], true);
                if (!empty($jobj['aco'])) {
                    $aco_spec = explode('|', $jobj['aco']);
                    if (!acl_check($aco_spec[0], $aco_spec[1])) continue;
                }
            }
        } else {
            // Skip non-LBF forms that we are not authorized to see.
            $tmp = getRegistryEntryByDirectory($formdir, 'aco_spec');
            if (!empty($tmp['aco_spec'])) {
                $aco_spec = explode('|', $tmp['aco_spec']);
                if (!acl_check($aco_spec[0], $aco_spec[1])) continue;
            }
        }

        // $form_info = getFormInfoById($iter['id']);
        if (strtolower(substr($iter['form_name'], 0, 5)) == 'camos') {
            //CAMOS generates links from report.php and these links should
            //be clickable without causing view.php to come up unexpectedly.
            //I feel that the JQuery code in this file leading to a click
            //on the report.php content to bring up view.php steps on a
            //form's autonomy to generate it's own html content in it's report
            //but until any other form has a problem with this, I will just
            //make an exception here for CAMOS and allow it to carry out this
            //functionality for all other forms.  --Mark
            echo '<tr title="' . xl('Edit form') . '" ' .
                'id="' . $formdir . '~' . $iter['form_id'] . '">';
        } else {
            echo '<tr title="' . xl('Edit form') . '" ' .
                'id="' . $formdir . '~' . $iter['form_id'] . '" class="text onerow">';
        }

        $acl_groups = acl_check("groups", "glog", false, 'write') ? true : false;
        $user = getNameFromUsername($iter['user']);

        $form_name = ($formdir == 'newpatient') ? xl('Patient Encounter') : xl_form_title($iter['form_name']);

        // Create the ESign instance for this form
        $esign = $esignApi->createFormESign($iter['id'], $formdir, $encounter);
        echo "<tr>";
        echo "<td style='border-bottom:1px solid'>";
        // a link to edit the form
        echo "<div class='form_header_controls'>";

        // If the form is locked, it is no longer editable
        if ($esign->isLocked()) {
            echo "<a href=# class='css_button_small form-edit-button-locked' id='form-edit-button-" . attr($formdir) . "-" . attr($iter['id']) . "'><span>" . xlt('Locked') . "</span></a>";
        } else {
            if ((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write') AND $is_group == 0)
                OR (((!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write')) AND $is_group AND acl_check("groups", "glog", false, 'write')))
            ) {
                echo "<a class='css_button_small form-edit-button' id='form-edit-button-" . attr($formdir) . "-" . attr($iter['id']) . "' target='" .
                    "_parent" .
                    "' href='$rootdir/patient_file/encounter/view_form.php?" .
                    "formname=" . attr($formdir) . "&id=" . attr($iter['form_id']) .
                    "' onclick='top.restoreSession()'>";
                echo "<span>" . xlt('Edit') . "</span></a>";
            }
        }

        if (($esign->isButtonViewable() AND $is_group == 0) OR ($esign->isButtonViewable() AND $is_group AND acl_check("groups", "glog", false, 'write'))) {
            if (!$aco_spec || acl_check($aco_spec[0], $aco_spec[1], '', 'write')) {
                echo $esign->buttonHtml();
            }
        }

        if (acl_check('admin', 'super')) {
            if ($formdir != 'newpatient' && $formdir != 'newGroupEncounter') {
                // a link to delete the form from the encounter
                echo "<a target='_parent'" .
                    " href='$rootdir/patient_file/encounter/delete_form.php?" .
                    "formname=" . $formdir .
                    "&id=" . $iter['id'] .
                    "&encounter=" . $encounter .
                    "&pid=" . $pid .
                    "' class='css_button_small' title='" . xl('Delete this form') . "' onclick='top.restoreSession()'><span>" . xl('Delete') . "</span></a>";
            } else {
                ?><a href='javascript:;' class='css_button_small'
                     style='color:gray'>
                <span><?php xl('Delete', 'e'); ?></span></a><?php
            }
        }

        echo "<div class='form_header'>";

        // Figure out the correct author (encounter authors are the '$providerNameRes', while other
        // form authors are the '$user['fname'] . "  " . $user['lname']').
        if ($formdir == 'newpatient') {
            $form_author = $providerNameRes;
        } else {
            $form_author = $user['fname'] . "  " . $user['lname'];
        }
        echo "<a href='#' onclick='divtoggle(\"spanid_$divnos\",\"divid_$divnos\");' class='small' id='aid_$divnos'><b>$form_name</b> <span class='text'>" . xl('by') . " " . htmlspecialchars($form_author) . "</span> (<span id=spanid_$divnos class=\"indicator\">" . xl('Collapse') . "</span>)</a></div>";

        echo "</td>\n";
        echo "</tr>";
        echo "<tr>";
        echo "<td valign='top' class='formrow'><div class='tab' id='divid_$divnos' style='display:block'>";

        // Use the form's report.php for display.  Forms with names starting with LBF
        // are list-based forms sharing a single collection of code.
        //
        if (substr($formdir, 0, 3) == 'LBF') {
            include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");

            call_user_func("lbf_report", $attendant_id, $encounter, 2, $iter['form_id'], $formdir, true);
        } else {
            include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
            call_user_func($formdir . "_report", $attendant_id, $encounter, 2, $iter['form_id']);
        }

        if ($esign->isLogViewable()) {
            $esign->renderLog();
        }

        echo "</div></td></tr>";
        $divnos = $divnos + 1;
    }
    echo "</table>";
}
?>
