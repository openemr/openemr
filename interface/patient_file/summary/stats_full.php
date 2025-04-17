<?php

/**
 * stats_full.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');
require_once($GLOBALS['srcdir'] . '/lists.inc.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');
require_once($GLOBALS['srcdir'] . '/options.inc.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\OeUI\OemrUI;
use OpenEMR\Services\ListService;

// Check if user has permission for any issue type.
$auth = false;
foreach ($ISSUE_TYPES as $type => $dummy) {
    if (AclMain::aclCheckIssue($type)) {
        $auth = true;
        break;
    }
}

if ($auth) {
    $tmp = getPatientData($pid, "squad");
    if ($tmp['squad'] && ! AclMain::aclCheckCore('squads', $tmp['squad'])) {
        die(xlt('Not authorized'));
    }
} else {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Patient Issues")]);
    exit;
}

 // Collect parameter(s)
 $category = empty($_REQUEST['category']) ? '' : $_REQUEST['category'];

// Get patient's preferred language for the patient education URL.
$tmp = getPatientData($pid, 'language');
$language = $tmp['language'];
?>
<html>
<head>
<?php Header::setupHeader('popper'); ?>
<title><?php echo xlt('Patient Issues'); ?></title>
<script>

// callback from add_edit_issue.php:
// The close logic in add_edit_issue not working so dialog will do refresh
function refreshIssue(issue, title) {
    top.restoreSession();
    window.location=window.location;
}

function dopclick(id, category) {
    top.restoreSession();
    category = (category == 0) ? '' : category;
    let dlg_url = 'add_edit_issue.php?issue=' + encodeURIComponent(id) + '&thistype=' + encodeURIComponent(category);
    // dlgopen will call top.restoreSession
    dlgopen(dlg_url, '_blank', 1280, 900, '', <?php echo xlj("Add/Edit Issue"); ?>, {
        allowDrag: false,
        allowResize: true,
        resolvePromiseOn: 'close',
    }).then(() => {
        top.restoreSession();
        location.reload();
    });
}

// Process click on number of encounters.
function doeclick(id) {
    top.restoreSession();
    dlgopen('../problem_encounter.php?issue=' + encodeURIComponent(id), '_blank', 700, 400);
}

function getSelectionCheckBoxes(issueType) {
    let issue = (issueType instanceof HTMLElement) ? issueType : document.getElementById(issueType)
    return Array.from(issue.getElementsByClassName("selection-check"));
}

function rowSelectionChanged(issue) {
    var deleteBtn = document.getElementById(issue + "-delete");
    if (deleteBtn) {
        deleteBtn.disabled = !getSelectionCheckBoxes(issue).some(e => e.checked);
    }
}

function headerSelectionChanged(groupBox, issueType) {
    let issueElm = document.getElementById(issueType);
    for (const c of getSelectionCheckBoxes(issueElm)) {
        c.checked = groupBox.checked;
    }
    rowSelectionChanged(issueType);
}

function deleteSelectedIssues(tableName) {
    var selBoxes = getSelectionCheckBoxes(tableName);
    var ids = ""
    var count = 0;
    for (var i = 0; i < selBoxes.length; i++) {
        if (selBoxes[i].checked) {
            if (count > 0) {
                ids += ","
            }
            ids += selBoxes[i].id.split("_")[1]
            count++
        }
    }

    dlgopen('../deleter.php?issue=' + ids + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 450);
}

// Called by the deleter.php window on a successful delete.
function imdeleted() {
    refreshIssue('', '')
}

// Process click on diagnosis for patient education popup.
function educlick(codetype, codevalue) {
  top.restoreSession();
  dlgopen('../education.php?type=' + encodeURIComponent(codetype) +
    '&code=' + encodeURIComponent(codevalue) +
    '&language=' + <?php echo js_url($language); ?>,
    '_blank', 1024, 750,true); // Force a new window instead of iframe to address cross site scripting potential
}

// Add Encounter button is clicked.
function newEncounter() {
    var f = document.forms[0];
    top.restoreSession();
    location.href='../../forms/newpatient/new.php?autoloaded=1&calenc=';
}

</script>
<script>
<?php
require_once("$include_root/patient_file/erx_patient_portal_js.php"); // jQuery for popups for eRx and patient portal
?>
</script>
<?php
$arrOeUiSettings = array(
    'heading_title' => xl('Medical Issues'),
    'include_patient_name' => true,
    'expandable' => true,
    'expandable_files' => array("stats_full_patient_xpd", "external_data_patient_xpd", "patient_ledger_patient_xpd"),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => true,
    'help_file_name' => "issues_dashboard_help.php"
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>

<style>
.selection-check {
    top: 0;
    left: 0;
    height: 16px;
    width: 16px;
}
</style>
</head>

<body class="patient-medical-issues">
    <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
        <div class="row">
            <div class="col-sm-12">
                <?php require_once("$include_root/patient_file/summary/dashboard_header.php") ?>
            </div>
        </div>
        <?php
        $list_id = "issues"; // to indicate nav item is active, count and give correct id
        // Collect the patient menu then build it
        $menuPatient = new PatientMenuRole();
        $menuPatient->displayHorizNavBarMenu();
        ?>

        <div id='patient_stats'>
            <form method='post' action='stats_full.php' onsubmit='return top.restoreSession()'>
                <?php foreach ($ISSUE_TYPES as $t => $focustitles) : ?>
                    <?php
                    if (!AclMain::aclCheckIssue($t)) {
                        continue;
                    }

                    if ($category && ($t !== $category)) {
                        continue;
                    }

                    $btnDelete = AclMain::aclCheckCore('admin', 'super');
                    $canSelect = $btnDelete;
                    $btnAdd = false;
                    if (AclMain::aclCheckIssue($t, '', ['write', 'addonly'])) {
                        if (in_array($t, ['allergy', 'medications']) && $GLOBALS['erx_enable']) {
                            $btnAdd = [
                                'href' => '../../eRx.php?page=medentry',
                                'onclick' => 'top.restoreSession()',
                            ];
                        } else {
                            $btnAdd = [
                                'href' => '#',
                                'onclick' => 'dopclick(0, ' . attr_js($t) . ')'
                            ];
                        }
                    }

                    $condition = ($GLOBALS['erx_enable'] && $GLOBALS['erx_medication_display'] && $t == 'medication') ? "AND erx_uploaded != '1'" :  '';
                    $pres = sqlStatement("SELECT * FROM lists WHERE pid = ? AND type = ? $condition ORDER BY begdate", [$pid, $t]);
                    $noIssues = false;
                    $nothingRecorded = false;

                    // if no issues (will place a 'None' text vs. toggle algorithm here)
                    if (sqlNumRows($pres) < 1) {
                        if (getListTouch($pid, $t)) {
                            // Data entry has happened to this type, so can display an explicit None.
                            $noIssues = true;
                        } else {
                            // Data entry has not happened to this type, so can show the none selection option.
                            $nothingRecorded = true;
                        }
                    }

                    ?>
                    <div class="bg-light w-100 p-3 d-flex sticky-top justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <?php if (!($noIssues || $nothingRecorded)) : ?>
                                <input type="checkbox" class="selection-check mr-1" onclick="headerSelectionChanged(this, <?php echo attr_js($t);?>);"/>
                                <button type="button" class="btn btn-text px-2" data-issue-type="<?php echo attr($t); ?>" data-action="toggle" data-expanded="false" aria-label="<?php echo xla("Expand or collapse all items in section"); ?>"><span class="fa fa-fw fa-expand" aria-hidden="true"></span></button>
                            <?php endif; ?>
                            <h4 class="d-inline-block p-0 m-0"><?php echo text($focustitles[0]); ?></h4>
                        </div>

                        <div class="btn-group" role="group">

                            <?php if ($btnAdd) : ?>
                            <a href="<?php echo attr($btnAdd['href']); ?>" class="btn btn-sm btn-text" onclick='<?php echo $btnAdd['onclick']; ?>'><span class="fa fa-fw fa-plus"></span>&nbsp;<?php echo xlt('Add'); ?></a>
                            <?php endif; ?>

                            <?php if ($btnDelete) : ?>
                            <button type="button" id="<?php echo attr($t); ?>-delete" class="btn btn-sm btn-text" disabled onclick="deleteSelectedIssues(<?php echo attr_js($t); ?>)"><span class="fa fa-fw fa-trash-can"></span>&nbsp;<?php echo xlt('Delete'); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="list-group list-group-flush" id="<?php echo attr($t); ?>">
                        <?php if ($noIssues && !$nothingRecorded) : ?>
                            <div class="list-group-item"><?php echo xlt("None{{Issue}}"); ?></div>
                        <?php elseif (!$noIssues && $nothingRecorded) : ?>
                            <div class="list-group-item">
                                <div class="form-check">
                                    <input class="form-check-input noneCheck" value="none" <?php echo (!AclMain::aclCheckIssue($t, '', 'write')) ? " disabled" : ""; ?> type="checkbox" name="<?php echo attr($t); ?>" id="<?php echo attr($t); ?>">
                                    <label class="form-check-label" for="<?php echo attr($t); ?>"><?php echo xlt("None{{Issue}}"); ?></label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php
                        // display issues
                        $encount = 0;
                        while ($row = sqlFetchArray($pres)) :
                            $rowid = $row['id'];

                            $disptitle = trim($row['title']) ? $row['title'] : "[Missing Title]";

                            $ierow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE list_id = ?", array($rowid));

                            // encount is used to toggle the color of the table-row output below
                            ++$encount;
                            $bgclass = (($encount & 1) ? "bg1" : "bg2");

                            $colorstyle = !empty($row['enddate']) ? "text-muted" : "";

                            // look up the diag codes
                            $codetext = "";
                            if ($row['diagnosis'] != "") {
                                $diags = explode(";", $row['diagnosis']);
                                foreach ($diags as $diag) {
                                    $codedesc = lookup_code_descriptions($diag);
                                    list($codetype, $code) = explode(':', $diag);
                                    if ($codetext) {
                                        $codetext .= "<br>";
                                    }

                                    $codetext .= "<a href='javascript:educlick(" . attr_js($codetype) . "," . attr_js($code) . ")' class='" . $colorstyle .  "'>" .
                                    text($diag . " (" . $codedesc . ")") . "</a>";
                                }
                            }

                            // calculate the status
                            $resolved = false;
                            if ($row['outcome'] == "1" && $row['enddate'] != null) {
                                // Resolved
                                $resolved = true;
                                $statusCompute = generate_display_field(array('data_type' => '1','list_id' => 'outcome'), $row['outcome']);
                            } elseif ($row['enddate'] == null) {
                                $statusCompute = xlt("Active");
                            } else {
                                // MU3 criteria, show medical problem's with end dates as a status of Completed.
                                $statusCompute = ($t == 'medical_problem') ? xlt("Completed") : xlt("Inactive");
                                $resolved = ($t == "medical_problems") ? true : false;
                            }

                            $click_class = 'statrow';
                            if ($row['erx_source'] == 1 && $t == 'allergy') {
                                $click_class = '';
                            } elseif ($row['erx_uploaded'] == 1 && $t == 'medication') {
                                $click_class = '';
                            }

                            $shortBegDate = trim(oeFormatShortDate($row['begdate']) ?? '');
                            $shortEndDate = trim(oeFormatShortDate($row['enddate']) ?? '');
                            $fullBegDate = trim(oeFormatDateTime($row['begdate']) ?? '');
                            $fullEndDate = trim(oeFormatDateTime($row['enddate']) ?? '');
                            $shortModDate = trim(oeFormatShortDate($row['modifydate']) ?? '');
                            $fullModDate = trim(oeFormatDateTime($row['modifydate']) ?? '');

                            $outcome = ($row['outcome']) ?  generate_display_field(['data_type' => 1, 'list_id' => 'outcome'], $row['outcome']) : false;
                            ?>
                        <div class="list-group-item p-1">
                            <div class="summary m-0 p-0 d-flex w-100 justify-content-end align-content-center">
                                <?php if ($canSelect) : ?>
                                    <input type="checkbox" class="selection-check mt-1 mr-2" data-issue="<?php echo attr($t); ?>" name="sel_<?php echo attr($rowid); ?>" id="sel_<?php echo attr($rowid); ?>">
                                <?php endif; ?>
                                <div class="flex-fill pl-2">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-text btn-sm collapsed" data-toggle="collapse" data-target="#details_<?php echo attr($row['id']); ?>" aria-expanded="false" aria-controls="details_<?php echo attr($row['id']); ?>"><span aria-hidden="true" class="fa fa-fw fa-chevron-right"></span></button>
                                        <button type="button" class="btn btn-outline-text btn-sm editenc" data-issue-id="<?php echo attr($row['id']); ?>"><span aria-hidden="true" class="fa fa-fw fa-link"></span></button>
                                    </div>
                                    <a href="#" data-issue-id="<?php echo attr($row['id']); ?>" class="font-weight-bold issue_title" data-toggle="tooltip" data-placement="right" title="<?php echo text(($diag ?? '') . ": " . ($codedesc ?? '')); ?>">
                                        <?php echo text($disptitle); ?>
                                    </a>&nbsp;(<?php echo $statusCompute; ?><?php echo (!$resolved && $outcome) ? ", $outcome" : ""; ?>)
                                    <?php
                                    if ($focustitles[0] == "Allergies") :
                                        echo generate_display_field(array('data_type' => '1','list_id' => 'reaction'), $row['reaction']);
                                    endif;
                                    ?>
                                </div>
                                <?php if ($focustitles[0] == "Allergies") :
                                    $l = new ListService();
                                    $sev = $l->getListOption('severity_ccda', $row['severity_al']);
                                    $hgl = (in_array($row['severity_al'], ['severe', 'life_threatening_severity', 'fatal'])) ? 'bg-warning font-weight-bold px-1' : '';
                                    ?>
                                <span class="mr-3 <?php echo attr($hgl); ?>">
                                    <?php echo text($sev['title']); ?>
                                </span>
                                <?php endif; ?>
                                <div class="text-right">
                                    <span class="font-weight-bold d-inline"><?php echo xlt("Occurrence"); ?></span>
                                    <span><?php echo generate_display_field(['data_type' => '1', 'list_id' => 'occurrence'], $row['occurrence']); ?></span>
                                </div>
                            </div>
                            <div id="details_<?php echo attr($row['id']); ?>" class="collapse">
                                <div class="d-flex flex-column w-100 my-3">
                                    <div class="d-flex w-100">
                                        <div class="pr-3">
                                            <div class="font-weight-bold"><?php echo xlt("Last Modified"); ?></div>
                                            <div class="pl-1" title="<?php echo attr($fullModDate); ?>"><?php echo text($shortModDate); ?></div>
                                        </div>
                                        <?php if ($row['begdate']) : ?>
                                            <div class="pr-3">
                                                <div class="font-weight-bold "><?php echo xlt("Start Date"); ?></div>
                                                <div class="" title="<?php echo text($fullBegDate); ?>"><?php echo text($shortBegDate); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($row['enddate']) : ?>
                                            <div class="pr-3">
                                                <div class="font-weight-bold "><?php echo xlt("End Date"); ?></div>
                                                <div title="<?php echo attr($fullEndDate); ?>"><?php echo text($shortEndDate); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($t == "allergy" || $t == "medical_problem") : ?>
                                            <div class="pr-3">
                                                <div class="font-weight-bold"><?php echo xlt("Verification"); ?></div>
                                                <div>
                                                <?php
                                                    $codeListName = (!empty($thistype) && ($thistype == 'medical_problem')) ? 'condition-verification' : 'allergyintolerance-verification';
                                                    echo generate_display_field(array('data_type' => '1','list_id' => $codeListName), $row['verification']);
                                                ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($row['referredby']) : ?>
                                            <div class="pr-3">
                                                <div class="font-weight-bold"><?php echo xlt("Referred By"); ?></div>
                                                <div><?php echo text($row['referredby']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($row['comments']) : ?>
                                            <div class="flex-fill">
                                                <div class="font-weight-bold"><?php echo xlt("Comments"); ?></div>
                                                <div><?php echo text($row['comments']); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex w-100 mt-2">
                                        <?php echo $codetext; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <?php endwhile; ?>
                    </div>
                <?php endforeach; ?>
            </form>
        </div> <!-- end patient_stats -->
    </div><!--end of container div -->
    <?php $oemr_ui->oeBelowContainerDiv();?>
</body>
<script>
$(function () {
    $("[data-toggle='collapse']").click(function() {
        $(this).children("span").toggleClass(["fa-chevron-right", "fa-chevron-down"]);
    });
    $(".selection-check").on('change', function(e) {
        rowSelectionChanged(this.getAttribute('data-issue'));
    });
    $('[data-action="toggle"]').on('click', function(e) {
        let type = this.getAttribute('data-issue-type');
        let isExp = this.getAttribute('data-expanded');
        let selector = (isExp === "false") ? "[data-toggle='collapse'].collapsed" : "[data-toggle='collapse']";
        console.debug(selector);
        $("#" + type + " " + selector).trigger('click');
        $(this).children(".fa").toggleClass(["fa-compress", 'fa-expand']);
        this.setAttribute('data-expanded', (isExp === "false" ? "true" : "false"));
    });
    $(".issue_title").click(function() { dopclick($(this).data('issue-id'),0); });
    $(".editenc").click(function(event) { doeclick($(this).data('issue-id'),0); });
    $("#newencounter").click(function() { newEncounter(); });
    $("#history").click(function() { GotoHistory(); });
    $("#back").click(function() { GoBack(); });

    $(".noneCheck").click(function() {
      top.restoreSession();
      $.post( "../../../library/ajax/lists_touch.php",
          {
              type: this.name,
              patient_id: <?php echo js_escape($pid); ?>,
              csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
          }
      );
      $(this).hide();
    });
});

var GotoHistory = function() {
    top.restoreSession();
    location.href='../history/history_full.php';
}

var GoBack = function () {
    top.restoreSession();
    location.href='demographics.php';
}
</script>
</html>
