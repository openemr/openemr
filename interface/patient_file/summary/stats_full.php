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
require_once($GLOBALS['srcdir'].'/lists.inc');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\OeUI\OemrUI;

// Check if user has permission for any issue type.
$auth = false;
foreach ($ISSUE_TYPES as $type => $dummy) {
    if (acl_check_issue($type)) {
        $auth = true;
        break;
    }
}

if ($auth) {
    $tmp = getPatientData($pid, "squad");
    if ($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) {
        die(xlt('Not authorized'));
    }
} else {
    die(xlt('Not authorized'));
}

 // Collect parameter(s)
 $category = empty($_REQUEST['category']) ? '' : $_REQUEST['category'];

// Get patient's preferred language for the patient education URL.
$tmp = getPatientData($pid, 'language');
$language = $tmp['language'];
?>
<html>

<head>

    <?php Header::setupHeader(); ?>

<title><?php echo xlt('Patient Issues'); ?></title>

<script language="JavaScript">

// callback from add_edit_issue.php:
function refreshIssue(issue, title) {
    top.restoreSession();
    location.reload();
}

function dopclick(id,category) {
    top.restoreSession();
    if (category == 0) category = '';
    dlgopen('add_edit_issue.php?issue=' + encodeURIComponent(id) + '&thistype=' + encodeURIComponent(category), '_blank', 650, 500, '', <?php echo xlj("Add/Edit Issue"); ?>);
    //dlgopen('add_edit_issue.php?issue=' + encodeURIComponent(id) + '&thistype=' + encodeURIComponent(category), '_blank', 650, 600);
}

// Process click on number of encounters.
function doeclick(id) {
    top.restoreSession();
    dlgopen('../problem_encounter.php?issue=' + encodeURIComponent(id), '_blank', 700, 400);
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
</head>

<body class="body_top patient-medical-issues">
    <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
        <div class="row">
            <div class="col-sm-12">
                <?php require_once("$include_root/patient_file/summary/dashboard_header.php") ?>
            </div>
        </div>
        <div class="row" >
            <div class="col-sm-12">
                <?php
                $list_id = "issues"; // to indicate nav item is active, count and give correct id
                // Collect the patient menu then build it
                $menuPatient = new PatientMenuRole();
                $menuPatient->displayHorizNavBarMenu();
                ?>
            </div>
        </div>
    </div>
    <form method='post' action='stats_full.php' onsubmit='return top.restoreSession()'>
    <div class="container">
        <div class="row" id="patient_stats">
                <?php
                $encount = 0;
                $lasttype = "";
                foreach ($ISSUE_TYPES as $focustype => $focustitles) {
                    if (!acl_check_issue($focustype)) {
                        continue;
                    }

                    if ($category) {
                        // Only show this category
                        if ($focustype != $category) {
                            continue;
                        }
                    }

                    // Show header
                    $disptype = $focustitles[0];
                    if (acl_check_issue($focustype, '', array('write', 'addonly'))) {
                        if (($focustype=='allergy' || $focustype=='medication') && $GLOBALS['erx_enable']) {
                            $sectionHref = "../../eRx.php?page=medentry";
                            $onClick = "top.restoreSession()";
                        } else {
                            $sectionHref = "#";
                            $onClick = "dopclick(0," . attr_js($focustype) . ")";
                        }
                    }
                    ?>
                    <div class="col-sm-12">
                        <div class="page-header">
                            <h3><a href="<?php echo $sectionHref;?>" onclick="<?php echo $onClick;?>" class="btn btn-text btn-add"><?php echo xlt("Add");?></a></small><?php echo text($disptype);?>&nbsp;<small></h3>
                        </div>
                        <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo xlt('Title'); ?></th>
                                    <th><?php echo xlt('Begin'); ?></th>
                                    <th><?php echo xlt('End'); ?></th>
                                    <th><?php echo xlt('Coding (click for education)'); ?></th>
                                    <th><?php echo xlt('Status'); ?></th>
                                    <th><?php echo xlt('Occurrence'); ?></th>
                                    <?php if ($focustype == "allergy") { ?>
                                    <th><?php echo xlt('Reaction'); ?></th>
                                    <?php } ?>
                                    <th><?php echo xlt('Referred By'); ?></th>
                                    <th><?php echo xlt('Modify Date'); ?></th>
                                    <th><?php echo xlt('Comments'); ?></th>
                                    <th><?php echo xlt('Enc'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // collect issues
                                $condition = '';
                                if ($GLOBALS['erx_enable'] && $GLOBALS['erx_medication_display'] && $focustype=='medication') {
                                    $condition .= "and erx_uploaded != '1' ";
                                }

                                $pres = sqlStatement("SELECT * FROM lists WHERE pid = ? AND type = ? $condition ORDER BY begdate", array($pid,$focustype));

                                // if no issues (will place a 'None' text vs. toggle algorithm here)
                                if (sqlNumRows($pres) < 1) {
                                    if (getListTouch($pid, $focustype)) {
                                        // Data entry has happened to this type, so can display an explicit None.
                                        $totalCol = 10;
                                        if ($focustype == "allergy") {
                                            $totalCol = $totalCol++;
                                        }
                                        echo "<tr><td colspan=\"" . $totalCol . "\"><b>" . xlt("None") . "</b></td></tr>";
                                    } else {
                                        // Data entry has not happened to this type, so can show the none selection option.
                                        echo "<tr><td colspan=\"{$totalCol}\"><input type='checkbox' class='noneCheck' name='" .
                                        attr($focustype) . "' value='none'";
                                        if (!acl_check_issue($focustype, '', 'write')) {
                                            echo " disabled";
                                        }

                                        echo " /><b>" . xlt("None{{Issue}}") . "</b></td></tr>";
                                    }
                                }

                                // display issues
                                while ($row = sqlFetchArray($pres)) {
                                    $rowid = $row['id'];

                                    $disptitle = trim($row['title']) ? $row['title'] : "[Missing Title]";

                                    $ierow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE list_id = ?", array($rowid));

                                    // look up the diag codes
                                    $codetext = "";
                                    if ($row['diagnosis'] != "") {
                                        $diags = explode(";", $row['diagnosis']);
                                        foreach ($diags as $diag) {
                                            $codedesc = lookup_code_descriptions($diag);
                                            list($codetype, $code) = explode(':', $diag);
                                            if ($codetext) {
                                                $codetext .= "<br />";
                                            }

                                            $codeTypeAttr = attr($codetype);
                                            $codeAttr = attr($code);
                                            $text = text($diag . " (" . $codedesc . ")");
                                            $id = attr($rowid);

                                            $codetext .= "<a href=\"#{$id}\" data-code-type=\"{$codeTypeAttr}\" data-code=\"{$codeAttr}\" class=\"code-link btn btn-sm btn-text\">{$text}</a>";

//                                            $codetext .= "<a data-code-type=\"" . attr_js($codetype) . "\" data-code='" . attr_js($codetype) . "' href='#' class='code btn btn-sm btn-text'>" .
//                                            text($diag . " (" . $codedesc . ")") . "</a>";
                                        }
                                    }

                                    // calculate the status
                                    if ($row['outcome'] == "1" && $row['enddate'] != null) {
                                        // Resolved
                                        $statusCompute = generate_display_field(array('data_type'=>'1','list_id'=>'outcome'), $row['outcome']);
                                    } elseif ($row['enddate'] == null) {
                                        $statusCompute = xlt("Active");
                                    } else {
                                        $statusCompute = xlt("Inactive");
                                    }

                                    $click_class='statrow';
                                    if ($row['erx_source']==1 && $focustype=='allergy') {
                                        $click_class='';
                                    } elseif ($row['erx_uploaded']==1 && $focustype=='medication') {
                                        $click_class='';
                                    }

                                    echo " <tr class={$click_class}>\n";
                                    echo "  <td id='" . attr($rowid) . "'>" . text($disptitle) . "</td>\n";
                                    echo "  <td>" . text(oeFormatShortDate($row['begdate'])) . "&nbsp;</td>\n";
                                    echo "  <td>" . text(oeFormatShortDate($row['enddate'])) . "&nbsp;</td>\n";
                                    // both codetext and statusCompute have already been escaped above with htmlspecialchars)
                                    echo "  <td>" . $codetext . "</td>\n";
                                    echo "  <td>" . $statusCompute . "&nbsp;</td>\n";
                                    echo "  <td>";
                                    echo generate_display_field(array('data_type'=>'1','list_id'=>'occurrence'), $row['occurrence']);
                                    echo "</td>\n";
                                    if ($focustype == "allergy") {
                                        echo "  <td>";
                                            echo generate_display_field(array('data_type'=>'1','list_id'=>'reaction'), $row['reaction']);
                                        echo "</td>\n";
                                    }

                                    echo "  <td>" . text($row['referredby']) . "</td>\n";
                                    echo "  <td>" . text(oeFormatShortDate($row['modifydate'])) . "</td>\n";
                                    echo "  <td>" . text($row['comments']) . "</td>\n";
                                    echo "  <td id='e_" . attr($rowid) . "' class='noclick' title='" . xla('View related encounters') . "'>";
                                    echo "  <input type='button' value='" . attr($ierow['count']) . "' class='editenc' id='" . attr($rowid) . "' />";
                                    echo "  </td>";
                                    echo " </tr>\n";
                                }
                                ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                <?php } ?>
        </div>
    </div>
    </form>

    <?php $oemr_ui->oeBelowContainerDiv();?>
</body>

<script>
var listId = '#' + <?php echo js_escape($list_id); ?>;

/**
 * Diagnosis link clicked
 * @param e
 */
function codeClick(e) {
    top.restoreSession();
    let code = $(this).data("code");
    let codeType = $(this).data("code-type");
    dlgopen('../education.php?type=' + encodeURIComponent(codeType) +
        '&code=' + encodeURIComponent(code) +
        '&language=' + <?php echo js_url($language); ?>,
        '_blank', 1024, 750,true);
    // Stop this click event from bubbling up
    e.stopPropogation();
}

$(document).ready(function(){
    $("tbody").on("click", ".code-link", codeClick)
              .on("click", ".statrow", function(){dopclick(this.id,0)});
    $(".editenc").click(function(event) { doeclick(this.id); });
    $("#newencounter").click(function() { newEncounter(); });
    $("#history").click(function() { GotoHistory(); });
    $("#back").click(function() { GoBack(); });
    $(listId).addClass("active");

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
