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

        <div id='patient_stats'>
            <form method='post' action='stats_full.php' onsubmit='return top.restoreSession()'>

            <table>

            <?php
            $encount = 0;
            $lasttype = "";
            $first = 1; // flag for first section
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

                if ($first) {
                    $first = 0;
                } else {
                    echo "</table>";
                }

                // Show header
                $disptype = $focustitles[0];
                if (acl_check_issue($focustype, '', array('write', 'addonly'))) {
                    if (($focustype=='allergy' || $focustype=='medication') && $GLOBALS['erx_enable']) {
                        echo "<a href='../../eRx.php?page=medentry' class='css_button_small' onclick='top.restoreSession()' ><span>" .
                        xlt('Add') . "</span></a>\n";
                    } else {
                        echo "<a href='javascript:;' class='css_button_small' onclick='dopclick(0," .
                        attr_js($focustype)  . ")'><span>" . xlt('Add') . "</span></a>\n";
                    }
                }

                echo "  <span class='title'>" . text($disptype) . "</span>\n";
                // echo " <table style='margin-bottom:1em;text-align:center'>";
                echo " <table style='margin-bottom:1em;'>";
                ?>
              <tr class='head'>
                <th style='text-align:left'><?php echo xlt('Title'); ?></th>
                <th style='text-align:left'><?php echo xlt('Begin'); ?></th>
                <th style='text-align:left'><?php echo xlt('End'); ?></th>
                <th style='text-align:left'><?php echo xlt('Coding (click for education)'); ?></th>
                <th style='text-align:left'><?php echo xlt('Status'); ?></th>
                <th style='text-align:left'><?php echo xlt('Occurrence'); ?></th>
                <?php if ($focustype == "allergy") { ?>
                  <th style='text-align:left'><?php echo xlt('Reaction'); ?></th>
                <?php } ?>
                <th style='text-align:left'><?php echo xlt('Referred By'); ?></th>
                <th style='text-align:left'><?php echo xlt('Modify Date'); ?></th>
                <th style='text-align:left'><?php echo xlt('Comments'); ?></th>
                <th><?php echo xlt('Enc'); ?></th>
                </tr>
                <?php

              // collect issues
                $condition = '';
                if ($GLOBALS['erx_enable'] && $GLOBALS['erx_medication_display'] && $focustype=='medication') {
                    $condition .= "and erx_uploaded != '1' ";
                }

                $pres = sqlStatement("SELECT * FROM lists WHERE pid = ? AND type = ? $condition" .
                "ORDER BY begdate", array($pid,$focustype));

              // if no issues (will place a 'None' text vs. toggle algorithm here)
                if (sqlNumRows($pres) < 1) {
                    if (getListTouch($pid, $focustype)) {
                        // Data entry has happened to this type, so can display an explicit None.
                        echo "<tr><td class='text'><b>" . xlt("None") . "</b></td></tr>";
                    } else {
                          // Data entry has not happened to this type, so can show the none selection option.
                          echo "<tr><td class='text'><input type='checkbox' class='noneCheck' name='" .
                        attr($focustype) . "' value='none'";
                        if (!acl_check_issue($focustype, '', 'write')) {
                            echo " disabled";
                        }

                          echo " /><b>" . xlt("None") . "</b></td></tr>";
                    }
                }

              // display issues
                while ($row = sqlFetchArray($pres)) {
                    $rowid = $row['id'];

                    $disptitle = trim($row['title']) ? $row['title'] : "[Missing Title]";

                    $ierow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
                    "list_id = ?", array($rowid));

                    // encount is used to toggle the color of the table-row output below
                    ++$encount;
                    $bgclass = (($encount & 1) ? "bg1" : "bg2");

                    $colorstyle = empty($row['enddate']) ? "style='color:red'" : "";

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

                            $codetext .= "<a href='javascript:educlick(" . attr_js($codetype) . "," . attr_js($code) . ")' $colorstyle>" .
                              text($diag . " (" . $codedesc . ")") . "</a>";
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

                    echo " <tr class='" . attr($bgclass) . " detail' $colorstyle>\n";
                    echo "  <td style='text-align:left' class='" . attr($click_class) . "' id='" . attr($rowid) . "'>" . text($disptitle) . "</td>\n";
                    echo "  <td>" . text($row['begdate']) . "&nbsp;</td>\n";
                    echo "  <td>" . text($row['enddate']) . "&nbsp;</td>\n";
                    // both codetext and statusCompute have already been escaped above with htmlspecialchars)
                    echo "  <td>" . $codetext . "</td>\n";
                    echo "  <td>" . $statusCompute . "&nbsp;</td>\n";
                    echo "  <td class='nowrap'>";
                    echo generate_display_field(array('data_type'=>'1','list_id'=>'occurrence'), $row['occurrence']);
                    echo "</td>\n";
                    if ($focustype == "allergy") {
                          echo "  <td>";
                            echo generate_display_field(array('data_type'=>'1','list_id'=>'reaction'), $row['reaction']);
                          echo "</td>\n";
                    }

                    echo "  <td>" . text($row['referredby']) . "</td>\n";
                    echo "  <td>" . text($row['modifydate']) . "</td>\n";
                    echo "  <td>" . text($row['comments']) . "</td>\n";
                    echo "  <td id='e_" . attr($rowid) . "' class='noclick center' title='" . xla('View related encounters') . "'>";
                    echo "  <input type='button' value='" . attr($ierow['count']) . "' class='editenc' id='" . attr($rowid) . "' />";
                    echo "  </td>";
                    echo " </tr>\n";
                }
            }

            echo "</table>";
            ?>

            </table>

            </form>
        </div> <!-- end patient_stats -->
    </div><!--end of container div -->
    <?php $oemr_ui->oeBelowContainerDiv();?>

</body>

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".statrow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".statrow").mouseout(function() { $(this).toggleClass("highlight"); });

    $(".statrow").click(function() { dopclick(this.id,0); });
    $(".editenc").click(function(event) { doeclick(this.id); });
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

var listId = '#' + <?php echo js_escape($list_id); ?>;
$(document).ready(function(){
    $(listId).addClass("active");
});
</script>


</html>
