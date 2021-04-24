<?php

/**
 * Encounter list.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/lists.inc");
require_once("../../../custom/code_types.inc.php");
if ($GLOBALS['enable_group_therapy']) {
    require_once("$srcdir/group.inc");
}

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Billing\InvoiceSummary;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$is_group = ($attendant_type == 'gid') ? true : false;

// "issue" parameter exists if we are being invoked by clicking an issue title
// in the left_nav menu.  Currently that is just for athletic teams.  In this
// case we only display encounters that are linked to the specified issue.
$issue = empty($_GET['issue']) ? 0 : 0 + $_GET['issue'];

 //maximum number of encounter entries to display on this page:
 // $N = 12;

 //Get the default encounter from Globals
 $default_encounter = $GLOBALS['default_encounter_view']; //'0'=clinical, '1' = billing

// Get relevant ACL info.
$auth_notes_a = AclMain::aclCheckCore('encounters', 'notes_a');
$auth_notes = AclMain::aclCheckCore('encounters', 'notes');
$auth_coding_a = AclMain::aclCheckCore('encounters', 'coding_a');
$auth_coding = AclMain::aclCheckCore('encounters', 'coding');
$auth_relaxed = AclMain::aclCheckCore('encounters', 'relaxed');
$auth_med = AclMain::aclCheckCore('patients', 'med');
$auth_demo = AclMain::aclCheckCore('patients', 'demo');
$glog_view_write = AclMain::aclCheckCore("groups", "glog", false, array('view', 'write'));

$tmp = getPatientData($pid, "squad");
if ($tmp['squad'] && ! AclMain::aclCheckCore('squads', $tmp['squad'])) {
    $auth_notes_a = $auth_notes = $auth_coding_a = $auth_coding = $auth_med = $auth_demo = $auth_relaxed = 0;
}

// Perhaps the view choice should be saved as a session variable.
//
$tmp = sqlQuery("select authorized from users " .
  "where id = ?", array($_SESSION['authUserID']));
$billing_view = ($tmp['authorized']) ? 0 : 1;
if (isset($_GET['billing'])) {
    $billing_view = empty($_GET['billing']) ? 0 : 1;
} else {
    $billing_view = ($default_encounter == 0) ? 0 : 1;
}

//Get Document List by Encounter ID
function getDocListByEncID($encounter, $raw_encounter_date, $pid)
{
    global $ISSUE_TYPES, $auth_med;

    $documents = getDocumentsByEncounter($pid, $encounter);
    if (!empty($documents) && count($documents) > 0) {
        foreach ($documents as $documentrow) {
            if ($auth_med) {
                $irow = sqlQuery("SELECT type, title, begdate FROM lists WHERE id = ? LIMIT 1", array($documentrow['list_id']));
                if ($irow) {
                    $tcode = $irow['type'];
                    if ($ISSUE_TYPES[$tcode]) {
                        $tcode = $ISSUE_TYPES[$tcode][2];
                    }
                    echo text("$tcode: " . $irow['title']);
                }
            } else {
                echo "(" . xlt('No access') . ")";
            }

            // Get the notes for this document and display as title for the link.
            $queryString = "SELECT date,note FROM notes WHERE foreign_id = ? ORDER BY date";
            $noteResultSet = sqlStatement($queryString, array($documentrow['id']));
            $note = '';
            while ($row = sqlFetchArray($noteResultSet)) {
                $note .= oeFormatShortDate(date('Y-m-d', strtotime($row['date']))) . " : " . $row['note'] . "\n";
            }
            $docTitle = ( $note ) ? $note : xl("View document");

            $docHref = $GLOBALS['webroot'] . "/controller.php?document&view&patient_id=" . attr_url($pid) . "&doc_id=" . attr_url($documentrow['id']);
            echo "<div class='text docrow' id='" . attr($documentrow['id']) . "'data-toggle='tooltip' data-placement='top' title='" . attr($docTitle) . "'>\n";
            echo "<a href='$docHref' onclick='top.restoreSession()' >" . xlt('Document') . ": " . text($documentrow['document_name'])  . '-' . $documentrow['id'] . ' (' . text(xl_document_category($documentrow['name'])) . ')' . "</a>";
            echo "</div>";
        }
    }
}

// This is called to generate a line of output for a patient document.
//
function showDocument(&$drow)
{
    global $ISSUE_TYPES, $auth_med;

    $docdate = $drow['docdate'];

    // if doc is already tagged by encounter it already has its own row so return
    $doc_tagged_enc = $drow['encounter_id'];
    if ($doc_tagged_enc) {
        return;
    }

    echo "<tr class='text docrow' id='" . attr($drow['id']) . "'data-toggle='tooltip' data-placement='top' title='" . xla('View document') . "'>\n";

  // show date
    echo "<td>" . text(oeFormatShortDate($docdate)) . "</td>\n";

  // show associated issue, if any
    echo "<td>";
    if ($auth_med) {
        $irow = sqlQuery("SELECT type, title, begdate " .
        "FROM lists WHERE " .
        "id = ? " .
        "LIMIT 1", array($drow['list_id']));
        if ($irow) {
              $tcode = $irow['type'];
            if ($ISSUE_TYPES[$tcode]) {
                $tcode = $ISSUE_TYPES[$tcode][2];
            }
              echo text("$tcode: " . $irow['title']);
        }
    } else {
        echo "(" . xlt('No access') . ")";
    }
    echo "</td>\n";

  // show document name and category
    echo "<td colspan='3'>" .
    text(xl('Document') . ": " . $drow['document_name'] . '-' . $drow['id'] . ' (' . xl_document_category($drow['name']) . ')') .
    "</td>\n";
    echo "<td colspan='5'>&nbsp;</td>\n";
    echo "</tr>\n";
}

function generatePageElement($start, $pagesize, $billing, $issue, $text)
{
    if ($start < 0) {
        $start = 0;
    }
    $url = "encounters.php?pagestart=" . attr_url($start) . "&pagesize=" . attr_url($pagesize);
    $url .= "&billing=" . attr_url($billing);
    $url .= "&issue=" . attr_url($issue);

    echo "<a href='" . $url . "' onclick='top.restoreSession()'>" . $text . "</a>";
}

?>
<html>
<head>
<!-- Main style sheet comes after the page-specific stylesheet to facilitate overrides. -->
<?php if ($_SESSION['language_direction'] == "rtl") { ?>
  <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/rtl_encounters.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
<?php } else { ?>
  <link rel="stylesheet" href="<?php echo $GLOBALS['themes_static_relative']; ?>/misc/encounters.css?v=<?php echo $GLOBALS['v_js_includes']; ?>" />
<?php } ?>
<!-- Not sure why we don't want this ui to be B.S responsive. -->
<?php Header::setupHeader(['no_textformat']); ?>

<script src="<?php echo $GLOBALS['webroot'] ?>/library/js/ajtooltip.js"></script>

<script>
// open dialog to edit an invoice w/o opening encounter.
function editInvoice(e, id) {
    e.stopPropagation();
    const url = './../../billing/sl_eob_invoice.php?id=' + encodeURIComponent(id);
    dlgopen(url, '', 'modal-lg', 750, false, '', {
        onClosed: 'reload'
    });
}

//function toencounter(enc, datestr) {
function toencounter(rawdata) {
    var parts = rawdata.split("~");
    var enc = parts[0];
    var datestr = parts[1];

    top.restoreSession();
    parent.left_nav.setEncounter(datestr, enc, window.name);
    parent.left_nav.loadFrame('enc2', window.name, 'patient_file/encounter/encounter_top.php?set_encounter=' + encodeURIComponent(enc));
}

function todocument(docid) {
  h = '<?php echo $GLOBALS['webroot'] ?>/controller.php?document&view&patient_id=<?php echo attr_url($pid); ?>&doc_id=' + encodeURIComponent(docid);
  top.restoreSession();
  location.href = h;
}

 // Helper function to set the contents of a div.
function setDivContent(id, content) {
    $("#"+id).html(content);
}

function changePageSize() {
    billing = $(this).attr("billing");
    pagestart = $(this).attr("pagestart");
    issue = $(this).attr("issue");
    pagesize = $(this).val();
    top.restoreSession();
    window.location.href = "encounters.php?billing=" + encodeURIComponent(billing) + "&issue=" + encodeURIComponent(issue) + "&pagestart=" + encodeURIComponent(pagestart) + "&pagesize=" + encodeURIComponent(pagesize);
}

window.onload = function() {
    $("#selPagesize").on("change", changePageSize);
}

// Mouseover handler for encounter form names. Brings up a custom tooltip
// to display the form's contents.
function efmouseover(elem, ptid, encid, formname, formid) {
 ttMouseOver(elem, "encounters_ajax.php?ptid=" + encodeURIComponent(ptid) + "&encid=" + encodeURIComponent(encid) +
  "&formname=" + encodeURIComponent(formname) + "&formid=" + encodeURIComponent(formid) + "&csrf_token_form=" + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>);
}

</script>

</head>

<body>
<div class="container mt-3" id="encounters"> <!-- large outer DIV -->

    <span class='title'>
        <?php
        if ($issue) {
            echo xlt('Past Encounters for') . ' ';
            $tmp = sqlQuery("SELECT title FROM lists WHERE id = ?", array($issue));
            echo text($tmp['title']);
        } else {
            //There isn't documents for therapy group yet
            echo $attendant_type == 'pid' ? xlt('Past Encounters and Documents') : xlt('Past Therapy Group Encounters');
        }
        ?>
    </span>
    <?php
    // Setup the GET string to append when switching between billing and clinical views.


    if (!($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed) || ($is_group && !$glog_view_write)) {
        echo "<body>\n<html>\n";
        echo "<p>(" . xlt('Encounters not authorized') . ")</p>\n";
        echo "</body>\n</html>\n";
        exit();
    }

    $pagestart = 0;
    if (isset($_GET['pagesize'])) {
        $pagesize = $_GET['pagesize'];
    } else {
        if (array_key_exists('encounter_page_size', $GLOBALS)) {
            $pagesize = $GLOBALS['encounter_page_size'];
        } else {
            $pagesize = 0;
        }
    }
    if (isset($_GET['pagestart'])) {
        $pagestart = $_GET['pagestart'];
    } else {
        $pagestart = 0;
    }
    $getStringForPage = "&pagesize=" . attr_url($pagesize) . "&pagestart=" . attr_url($pagestart);

    ?>

    <?php if ($billing_view) { ?>
        <a href='encounters.php?billing=0&issue=<?php echo $issue . $getStringForPage; ?>' onclick='top.restoreSession()' style='font-size: 11px'>(<?php echo xlt('To Clinical View'); ?>)</a>
    <?php } else { ?>
        <a href='encounters.php?billing=1&issue=<?php echo $issue . $getStringForPage; ?>' onclick='top.restoreSession()' style='font-size: 11px'>(<?php echo xlt('To Billing View'); ?>)</a>
    <?php } ?>

    <span class="float-right">
        <?php echo xlt('Results per page'); ?>:
        <select class="form-control" id="selPagesize" billing="<?php echo attr($billing_view); ?>" issue="<?php echo attr($issue); ?>" pagestart="<?php echo attr($pagestart); ?>" >
            <?php
            $pagesizes = array(5, 10, 15, 20, 25, 50, 0);
            for ($idx = 0; $idx < count($pagesizes); $idx++) {
                echo "<option value='" . attr($pagesizes[$idx]) . "'";
                if ($pagesize == $pagesizes[$idx]) {
                    echo " selected='true'>";
                } else {
                    echo ">";
                }
                if ($pagesizes[$idx] == 0) {
                    echo xlt('ALL');
                } else {
                    echo text($pagesizes[$idx]);
                }
                echo "</option>";
            }
            ?>
        </select>
    </span>

    <br />

    <div class="table-responsive">
        <table class="table table-hover jumbotron py-4 mt-3">
            <thead>
                <tr class='text'>
                    <th scope="col"><?php echo xlt('Date'); ?></th>

                    <?php if ($billing_view) { ?>
                        <th class='billing_note' scope="col"><?php echo xlt('Billing Note'); ?></th>
                    <?php } else { ?>
                        <?php if ($attendant_type == 'pid' && !$issue) { // only for patient encounter and if listing for multiple issues?>
                            <th scope="col"><?php echo xlt('Issue'); ?></th>
                        <?php } ?>
                            <th scope="col"><?php echo xlt('Reason/Form'); ?></th>
                        <?php if ($attendant_type == 'pid') { ?>
                            <th scope="col"><?php echo xlt('Provider');    ?></th>
                        <?php } else { ?>
                            <th scope="col"><?php echo xlt('Counselors');    ?></th>
                        <?php } ?>
                    <?php } ?>

                    <?php if ($billing_view) { ?>
                    <th scope="col"><?php echo xlt('Code'); ?></th>
                    <th class='text-right' scope="col"><?php echo xlt('Chg'); ?></th>
                    <th class='text-right' scope="col"><?php echo xlt('Paid'); ?></th>
                    <th class='text-right' scope="col"><?php echo xlt('Adj'); ?></th>
                    <th class='text-right' scope="col"><?php echo xlt('Bal'); ?></th>
                    <?php } elseif ($attendant_type == 'pid') { ?>
                    <th colspan='5' scope="col"><?php echo ($GLOBALS['phone_country_code'] == '1') ? xlt('Billing') : xlt('Coding'); ?></th>
                    <?php } ?>

                    <?php if ($attendant_type == 'pid' && !$GLOBALS['ippf_specific']) { ?>
                    <th scope="col">&nbsp;<?php echo ($GLOBALS['weight_loss_clinic']) ? xlt('Payment') : xlt('Insurance'); ?></th>
                    <?php } ?>

                    <?php if ($GLOBALS['enable_group_therapy'] && !$billing_view && $therapy_group == 0) { ?>
                        <th scope="col"><?php echo xlt('Encounter type'); ?></th>
                    <?php }?>

                    <?php if ($GLOBALS['enable_follow_up_encounters']) { ?>
                        <th scope="col"></th>
                    <?php }?>

                    <?php if ($GLOBALS['enable_group_therapy'] && !$billing_view && $therapy_group == 0) { ?>
                        <th scope="col"><?php echo xlt('Group name'); ?></th>
                    <?php }?>

                    <?php if ($GLOBALS['enable_follow_up_encounters']) { ?>
                        <th scope="col"></th>
                    <?php }?>
                </tr>
            </thead>

            <?php
            $drow = false;
            if (!$billing_view) {
            // Query the documents for this patient.  If this list is issue-specific
            // then also limit the query to documents that are linked to the issue.
                $queryarr = array($pid);
                $query = "SELECT d.id, d.type, d.url, d.name as document_name, d.docdate, d.list_id, d.encounter_id, c.name " .
                "FROM documents AS d, categories_to_documents AS cd, categories AS c WHERE " .
                "d.foreign_id = ? AND cd.document_id = d.id AND c.id = cd.category_id ";
                if ($issue) {
                    $query .= "AND d.list_id = ? ";
                    $queryarr[] = $issue;
                }
                $query .= "ORDER BY d.docdate DESC, d.id DESC";
                $dres = sqlStatement($query, $queryarr);
                $drow = sqlFetchArray($dres);
            }

            // $count = 0;

            $sqlBindArray = array();
            if ($attendant_type == 'pid') {
                $from = "FROM form_encounter AS fe " .
                    "JOIN forms AS f ON f.pid = fe.pid AND f.encounter = fe.encounter AND " .
                    "f.formdir = 'newpatient' AND f.deleted = 0 ";
            } else {
                $from = "FROM form_groups_encounter AS fe " .
                    "JOIN forms AS f ON f.therapy_group_id = fe.group_id AND f.encounter = fe.encounter AND " .
                    "f.formdir = 'newGroupEncounter' AND f.deleted = 0 ";
            }

            if ($issue) {
                $from .= "JOIN issue_encounter AS ie ON ie.pid = ? AND " .
                "ie.list_id = ? AND ie.encounter = fe.encounter ";
                array_push($sqlBindArray, $pid, $issue);
            }
            if ($attendant_type == 'pid') {
                $from .= "LEFT JOIN users AS u ON u.id = fe.provider_id WHERE fe.pid = ? ";
                $sqlBindArray[] = $pid;
            } else {
                $from .= "LEFT JOIN users AS u ON u.id = fe.provider_id WHERE fe.group_id = ? ";
                $sqlBindArray[] = $_SESSION['therapy_group'];
            }

            $query = "SELECT fe.*, f.user, u.fname, u.mname, u.lname " . $from .
                    "ORDER BY fe.date DESC, fe.id DESC";

            $countQuery = "SELECT COUNT(*) as c " . $from;

            $countRes = sqlStatement($countQuery, $sqlBindArray);
            $count = sqlFetchArray($countRes);
            $numRes = $count['c'];


            if ($pagesize > 0) {
                $query .= " LIMIT " . escape_limit($pagestart) . "," . escape_limit($pagesize);
            }
            $upper  = $pagestart + $pagesize;
            if (($upper > $numRes) || ($pagesize == 0)) {
                $upper = $numRes;
            }


            if (($pagesize > 0) && ($pagestart > 0)) {
                generatePageElement($pagestart - $pagesize, $pagesize, $billing_view, $issue, "&lArr;" . htmlspecialchars(xl("Prev"), ENT_NOQUOTES) . " ");
            }
            echo ($pagestart + 1) . "-" . $upper . " " . htmlspecialchars(xl('of'), ENT_NOQUOTES) . " " . $numRes;
            if (($pagesize > 0) && ($pagestart + $pagesize <= $numRes)) {
                generatePageElement($pagestart + $pagesize, $pagesize, $billing_view, $issue, " " . htmlspecialchars(xl("Next"), ENT_NOQUOTES) . "&rArr;");
            }


            $res4 = sqlStatement($query, $sqlBindArray);


            while ($result4 = sqlFetchArray($res4)) {
                    // $href = "javascript:window.toencounter(" . $result4['encounter'] . ")";
                    $reason_string = "";
                    $auth_sensitivity = true;

                    $raw_encounter_date = '';

                    $raw_encounter_date = date("Y-m-d", strtotime($result4["date"]));
                    $encounter_date = date("D F jS", strtotime($result4["date"]));

                    //fetch acl for given pc_catid
                    $postCalendarCategoryACO = AclMain::fetchPostCalendarCategoryACO($result4['pc_catid']);
                if ($postCalendarCategoryACO) {
                    $postCalendarCategoryACO = explode('|', $postCalendarCategoryACO);
                    $authPostCalendarCategory = AclMain::aclCheckCore($postCalendarCategoryACO[0], $postCalendarCategoryACO[1]);
                } else { // if no aco is set for category
                    $authPostCalendarCategory = true;
                }

                if (!empty($result4["reason"])) {
                    $reason_string .= text($result4["reason"]) . "<br />\n";
                }

                    // else
                    //   $reason_string = "(No access)";

                if ($result4['sensitivity']) {
                    $auth_sensitivity = AclMain::aclCheckCore('sensitivities', $result4['sensitivity']);
                    if (!$auth_sensitivity || !$authPostCalendarCategory) {
                        $reason_string = "(" . xlt("No access") . ")";
                    }
                }

                    // This generates document lines as appropriate for the date order.
                while ($drow && $raw_encounter_date && $drow['docdate'] > $raw_encounter_date) {
                    showDocument($drow);
                    $drow = sqlFetchArray($dres);
                }

                    // Fetch all forms for this encounter, if the user is authorized to see
                    // this encounter's notes and this is the clinical view.
                    $encarr = array();
                    $encounter_rows = 1;
                if (
                    !$billing_view && $auth_sensitivity && $authPostCalendarCategory &&
                        ($auth_notes_a || ($auth_notes && $result4['user'] == $_SESSION['authUser']))
                ) {
                    $attendant_id = $attendant_type == 'pid' ? $pid : $therapy_group;
                    $encarr = getFormByEncounter($attendant_id, $result4['encounter'], "formdir, user, form_name, form_id, deleted");
                    $encounter_rows = count($encarr);
                }

                    $rawdata = $result4['encounter'] . "~" . oeFormatShortDate($raw_encounter_date);
                    echo "<tr class='encrow text' id='" . attr($rawdata) .
                    "'>\n";

                    // show encounter date
                    echo "<td class='align-top' data-toggle='tooltip' data-placement='top' title='" . attr(xl('View encounter') . ' ' . $pid . "." . $result4['encounter']) . "'>" .
                        text(oeFormatShortDate($raw_encounter_date)) . "</td>\n";

                if ($billing_view) {
                    // Show billing note that you can click on to edit.
                    $feid = $result4['id'] ? $result4['id'] : 0; // form_encounter id
                    echo "<td class='align-top'>";
                    echo "<div id='note_" . attr($feid) . "'>";
                    echo "<div id='" . attr($feid) . "'data-toggle='tooltip' data-placement='top' title='" . xla('Click to edit') . "' class='text billing_note_text border-0'>";
                    echo $result4['billing_note'] ? nl2br(text($result4['billing_note'])) : '<button type="button" class="btn btn-primary btn-add btn-sm">' . xlt('Add') . '</button>';
                    echo "</div>";
                    echo "</div>";
                    echo "</td>\n";

                    //  *************** end billing view *********************
                } else {
                    if ($attendant_type == 'pid' && !$issue) { // only for patient encounter and if listing for multiple issues
                        // show issues for this encounter
                        echo "<td>";
                        if ($auth_med && $auth_sensitivity && $authPostCalendarCategory) {
                            $ires = sqlStatement("SELECT lists.type, lists.title, lists.begdate " .
                                                "FROM issue_encounter, lists WHERE " .
                                                "issue_encounter.pid = ? AND " .
                                                "issue_encounter.encounter = ? AND " .
                                                "lists.id = issue_encounter.list_id " .
                                                "ORDER BY lists.type, lists.begdate", array($pid,$result4['encounter']));
                            for ($i = 0; $irow = sqlFetchArray($ires); ++$i) {
                                if ($i > 0) {
                                    echo "<br />";
                                }
                                $tcode = $irow['type'];
                                if ($ISSUE_TYPES[$tcode]) {
                                    $tcode = $ISSUE_TYPES[$tcode][2];
                                }
                                    echo text("$tcode: " . $irow['title']);
                            }
                        } else {
                            echo "(" . xlt('No access') . ")";
                        }
                        echo "</td>\n";
                    } // end if (!$issue)

                    // show encounter reason/title
                    echo "<td>" . $reason_string;

                    //Display the documents tagged to this encounter
                    getDocListByEncID($result4['encounter'], $raw_encounter_date, $pid);

                    echo "<div class='pl-2'>";

                    // Now show a line for each encounter form, if the user is authorized to
                    // see this encounter's notes.

                    foreach ($encarr as $enc) {
                        if ($enc['formdir'] == 'newpatient' || $enc['formdir'] == 'newGroupEncounter') {
                            continue;
                        }

                        // skip forms whose 'deleted' flag is set to 1 --JRM--
                        if ($enc['deleted'] == 1) {
                            continue;
                        }

                        // Skip forms that we are not authorized to see. --JRM--
                        // pardon the wonky logic
                        $formdir = $enc['formdir'];
                        if (
                            ($auth_notes_a) ||
                            ($auth_notes && $enc['user'] == $_SESSION['authUser']) ||
                            ($auth_relaxed && ($formdir == 'sports_fitness' || $formdir == 'podiatry'))
                        ) {
                        } else {
                            continue;
                        }

                        // Show the form name.  In addition, for the specific-issue case show
                        // the data collected by the form (this used to be a huge tooltip
                        // but we did away with that).
                        //
                        $formdir = $enc['formdir'];
                        if ($issue) {
                            echo text(xl_form_title($enc['form_name']));
                            echo "<br />";
                            echo "<div class='encreport pl-2'>";
                    // Use the form's report.php for display.  Forms with names starting with LBF
                    // are list-based forms sharing a single collection of code.
                            if (substr($formdir, 0, 3) == 'LBF') {
                                include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
                                call_user_func("lbf_report", $pid, $result4['encounter'], 2, $enc['form_id'], $formdir);
                            } else {
                                include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
                                call_user_func($formdir . "_report", $pid, $result4['encounter'], 2, $enc['form_id']);
                            }
                            echo "</div>";
                        } else {
                            $formDiv = "<div ";
                            if (hasFormPermission($enc['formdir'])) {
                                $formDiv .= "onmouseover='efmouseover(this," . attr_js($pid) . ","
                                . attr_js($result4['encounter']) .
                                "," . attr_js($formdir) . "," . attr_js($enc['form_id'])
                                . ")' " .
                                "onmouseout='ttMouseOut()'";
                            }
                            $formDiv .= ">";
                            $formDiv .= text(xl_form_title($enc['form_name']));
                            $formDiv .= "</div>";
                            echo $formDiv;
                        }
                    } // end encounter Forms loop

                    echo "</div>";
                    echo "</td>\n";

                    if ($attendant_type == 'pid') {
                        // show user (Provider) for the encounter
                        $provname = 'Unknown';
                        if (!empty($result4['lname']) || !empty($result4['fname'])) {
                            $provname = $result4['lname'];
                            if (!empty($result4['fname']) || !empty($result4['mname'])) {
                                $provname .= ', ' . $result4['fname'] . ' ' . $result4['mname'];
                            }
                        }
                        echo "<td>" . text($provname) . "</td>\n";

                        // for therapy group view
                    } else {
                        $counselors = '';
                        foreach (explode(',', $result4['counselors']) as $userId) {
                            $counselors .= getUserNameById($userId) . ', ';
                        }
                        $counselors = rtrim($counselors, ", ");
                        echo "<td>" . text($counselors) . "</td>\n";
                    }
                } // end not billing view

                    //this is where we print out the text of the billing that occurred on this encounter
                    $thisauth = $auth_coding_a;
                if (!$thisauth && $auth_coding) {
                    if ($result4['user'] == $_SESSION['authUser']) {
                        $thisauth = $auth_coding;
                    }
                }
                    $coded = "";
                    $arid = 0;
                if ($thisauth && $auth_sensitivity && $authPostCalendarCategory) {
                    $binfo = array('', '', '', '', '');
                    if ($subresult2 = BillingUtilities::getBillingByEncounter($pid, $result4['encounter'], "code_type, code, modifier, code_text, fee")) {
                        // Get A/R info, if available, for this encounter.
                        $arinvoice = array();
                        $arlinkbeg = "";
                        $arlinkend = "";
                        if ($billing_view) {
                                $tmp = sqlQuery("SELECT id FROM form_encounter WHERE " .
                                            "pid = ? AND encounter = ?", array($pid,$result4['encounter']));
                                $arid = 0 + $tmp['id'];
                            if ($arid) {
                                $arinvoice = InvoiceSummary::arGetInvoiceSummary($pid, $result4['encounter'], true);
                            }
                            if ($arid) {
                                $arlinkbeg = "<a onclick='editInvoice(event, " . attr_js($arid) . ")" . "'" . " class='text' style='color:#00cc00'>";
                                $arlinkend = "</a>";
                            }
                        }

                        // Throw in product sales.
                        $query = "SELECT s.drug_id, s.fee, d.name " .
                        "FROM drug_sales AS s " .
                        "LEFT JOIN drugs AS d ON d.drug_id = s.drug_id " .
                        "WHERE s.pid = ? AND s.encounter = ? " .
                        "ORDER BY s.sale_id";
                        $sres = sqlStatement($query, array($pid,$result4['encounter']));
                        while ($srow = sqlFetchArray($sres)) {
                            $subresult2[] = array('code_type' => 'PROD',
                            'code' => 'PROD:' . $srow['drug_id'], 'modifier' => '',
                            'code_text' => $srow['name'], 'fee' => $srow['fee']);
                        }

                        // This creates 5 columns of billing information:
                        // billing code, charges, payments, adjustments, balance.
                        foreach ($subresult2 as $iter2) {
                            // Next 2 lines were to skip diagnoses, but that seems unpopular.
                            // if ($iter2['code_type'] != 'COPAY' &&
                            //   !$code_types[$iter2['code_type']]['fee']) continue;
                            $title = $iter2['code_text'];
                            $codekey = $iter2['code'];
                            $codekeydisp = $iter2['code_type'] . " - " . $iter2['code'];
                            if ($iter2['code_type'] == 'COPAY') {
                                $codekey = 'CO-PAY';
                                $codekeydisp = xl('CO-PAY');
                            }
                            if ($iter2['modifier']) {
                                $codekey .= ':' . $iter2['modifier'];
                                $codekeydisp .= ':' . $iter2['modifier'];
                            }

                            $codekeydisp = $codekeydisp;

                            if ($binfo[0]) {
                                $binfo[0] .= '<br />';
                            }
                            if ($issue && !$billing_view) {
                            // Single issue clinical view: show code description after the code.
                                $binfo[0] .= $arlinkbeg . text($codekeydisp) . " " . text($title) . $arlinkend;
                            } else {
                            // Otherwise offer the description as a tooltip.
                                $binfo[0] .= "<span data-toggle='tooltip' data-placement='top' title='" . attr($title) . "'>" . $arlinkbeg . text($codekeydisp) . $arlinkend . "</span>";
                            }
                            if ($billing_view) {
                                if ($binfo[1]) {
                                    for ($i = 1; $i < 5; ++$i) {
                                        $binfo[$i] .= '<br />';
                                    }
                                }
                                if (empty($arinvoice[$codekey])) {
                                    // If no invoice, show the fee.
                                    if ($arlinkbeg) {
                                        $binfo[1] .= '&nbsp;';
                                    } else {
                                        $binfo[1] .= text(oeFormatMoney($iter2['fee']));
                                    }

                                    for ($i = 2; $i < 5; ++$i) {
                                        $binfo[$i] .= '&nbsp;';
                                    }
                                } else {
                                    $binfo[1] .= text(oeFormatMoney($arinvoice[$codekey]['chg'] + ($arinvoice[$codekey]['adj'] ?? null)));
                                    $binfo[2] .= text(oeFormatMoney($arinvoice[$codekey]['chg'] - $arinvoice[$codekey]['bal']));
                                    $binfo[3] .= text(oeFormatMoney($arinvoice[$codekey]['adj'] ?? null));
                                    $binfo[4] .= text(oeFormatMoney($arinvoice[$codekey]['bal']));
                                    unset($arinvoice[$codekey]);
                                }
                            }
                        } // end foreach

                        // Pick up any remaining unmatched invoice items from the accounting
                        // system.  Display them in red, as they should be unusual.
                        // Except copays aren't unusual but displaying them in red
                        // helps billers spot them quickly :)
                        if (!empty($arinvoice)) {
                            foreach ($arinvoice as $codekey => $val) {
                                if ($binfo[0]) {
                                    for ($i = 0; $i < 5; ++$i) {
                                        $binfo[$i] .= '<br />';
                                    }
                                }
                                for ($i = 0; $i < 5; ++$i) {
                                    $binfo[$i] .= "<p class='text-danger'>";
                                }
                                $binfo[0] .= text($codekey);
                                $binfo[1] .= text(oeFormatMoney($val['chg'] + $val['adj']));
                                $binfo[2] .= text(oeFormatMoney($val['chg'] - $val['bal']));
                                $binfo[3] .= text(oeFormatMoney($val['adj']));
                                $binfo[4] .= text(oeFormatMoney($val['bal']));
                                for ($i = 0; $i < 5; ++$i) {
                                    $binfo[$i] .= "</font>";
                                }
                            }
                        }
                    } // end if there is billing

                    echo "<td class='text'>" . $binfo[0] . "</td>\n";
                    for ($i = 1; $i < 5; ++$i) {
                        echo "<td class='text-right'>" . $binfo[$i] . "</td>\n";
                    }
                } /* end if authorized */ else {
                    echo "<td class='text align-top' colspan='5' rowspan='" . attr($encounter_rows) . "'>(" . xlt("No access") . ")</td>\n";
                }

                    // show insurance
                if ($attendant_type == 'pid' && !$GLOBALS['ippf_specific']) {
                    $insured = oeFormatShortDate($raw_encounter_date);
                    if ($auth_demo) {
                        $responsible = -1;
                        if ($arid) {
                                $responsible = InvoiceSummary::arResponsibleParty($pid, $result4['encounter']);
                        }
                        $subresult5 = getInsuranceDataByDate($pid, $raw_encounter_date, "primary");
                        if ($subresult5 && $subresult5["provider_name"]) {
                            $style = $responsible == 1 ? " style='color: var(--danger)'" : "";
                            $insured = "<span class='text'$style>&nbsp;" . xlt('Primary') . ": " .
                            text($subresult5["provider_name"]) . "</span><br />\n";
                        }
                        $subresult6 = getInsuranceDataByDate($pid, $raw_encounter_date, "secondary");
                        if ($subresult6 && $subresult6["provider_name"]) {
                            $style = $responsible == 2 ? " style='color: var(--danger)'" : "";
                            $insured .= "<span class='text'$style>&nbsp;" . xlt('Secondary') . ": " .
                            text($subresult6["provider_name"]) . "</span><br />\n";
                        }
                        $subresult7 = getInsuranceDataByDate($pid, $raw_encounter_date, "tertiary");
                        if ($subresult6 && $subresult7["provider_name"]) {
                            $style = $responsible == 3 ? " style='color: var(--danger)'" : "";
                            $insured .= "<span class='text'$style>&nbsp;" . xlt('Tertiary') . ": " .
                            text($subresult7["provider_name"]) . "</span><br />\n";
                        }
                        if ($responsible == 0) {
                            $insured .= "<span class='text' style='color: var(--danger)'>&nbsp;" . xlt('Patient') .
                                        "</span><br />\n";
                        }
                    } else {
                        $insured = " (" . xlt("No access") . ")";
                    }

                    echo "<td>" . $insured . "</td>\n";
                }

                if ($GLOBALS['enable_group_therapy'] && !$billing_view && $therapy_group == 0) {
                    $encounter_type = sqlQuery("SELECT pc_catname, pc_cattype FROM openemr_postcalendar_categories where pc_catid = ?", array($result4['pc_catid']));
                    echo "<td>" . xlt($encounter_type['pc_catname']) . "</td>\n";
                }

                if ($GLOBALS['enable_follow_up_encounters']) {
                    $symbol = ( !empty($result4['parent_encounter_id']) ) ? '<span class="fa fa-fw fa-undo p-1"></span>' : null;

                    echo "<td> " . $symbol . " </td>\n";
                }

                if ($GLOBALS['enable_group_therapy'] && !$billing_view && $therapy_group == 0) {
                    $group_name = ($encounter_type['pc_cattype'] == 3 && is_numeric($result4['external_id'])) ? getGroup($result4['external_id'])['group_name']  : "";
                    echo "<td>" . text($group_name) . "</td>\n";
                }


                if ($GLOBALS['enable_follow_up_encounters']) {
                    $encounterId = ( !empty($result4['parent_encounter_id']) ) ? $result4['parent_encounter_id'] : $result4['id'];
                    echo "<td> <div style='z-index: 9999'>  <a href='#' class='btn btn-primary' onclick='createFollowUpEncounter(event," . attr_js($encounterId) . ")'><span>" . xlt('Create follow-up encounter') . "</span></a> </div></td>\n";
                }

                    echo "</tr>\n";
            } // end while


            // Dump remaining document lines if count not exceeded.
            while ($drow /* && $count <= $N */) {
                showDocument($drow);
                $drow = sqlFetchArray($dres);
            }
            ?>

        </table>
    </div>

</div> <!-- end 'encounters' large outer DIV -->

<div class='position-absolute border' id='tooltipdiv' style='width: 533px; padding:2px; background-color: #ffffaa; visibility: hidden; z-index: 1000; font-size: 12px;'></div>

<script>
// jQuery stuff to make the page a little easier to use
function createFollowUpEncounter(event, encId){
    event.stopPropagation();
    var data = {
        encounterId: encId,
        mode: 'follow_up_encounter'
    };
    top.window.parent.newEncounter(data);
}

$(function () {
    $(".encrow").on("mouseover", function() { $(this).toggleClass("highlight"); });
    $(".encrow").on("mouseout", function() { $(this).toggleClass("highlight"); });
    $(".encrow").on("click", function() { toencounter(this.id); });

    $(".docrow").on("mouseover", function() { $(this).toggleClass("highlight"); });
    $(".docrow").on("mouseout", function() { $(this).toggleClass("highlight"); });
    $(".docrow").on("click", function() { todocument(this.id); });

    $(".billing_note_text").on("mouseover", function() { $(this).toggleClass("billing_note_text_highlight"); });
    $(".billing_note_text").on("mouseout", function() { $(this).toggleClass("billing_note_text_highlight"); });
    $(".billing_note_text").on("click", function(evt) {
        evt.stopPropagation();
        const url = 'edit_billnote.php?feid=' + encodeURIComponent(this.id);
        dlgopen(url, '', 'modal-sm', 350, false, '', {
            onClosed: 'reload',
        });
    });
});

$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

</script>
</body>
</html>
