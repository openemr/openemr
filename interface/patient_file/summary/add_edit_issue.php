<?php
/**
 * add or edit a medical problem.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/lists.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
require_once($GLOBALS['srcdir'].'/csv_like_join.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// TBD - Resolve functional issues if opener is included in Header
?>
<script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js?v=<?php echo $v_js_includes; ?>"></script>
<?php

if ($_POST['form_save']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    // Following hidden field received in the form will be used to ensure integrity of form values
    // 'issue', 'thispid', 'thisenc'
    $issue = $_POST['issue'];
    $thispid = $_POST['thispid'];
    $thisenc = $_POST['thisenc'];
} else {
    $issue = $_REQUEST['issue'];
    $thispid = 0 + (empty($_REQUEST['thispid']) ? $pid : $_REQUEST['thispid']);
    // A nonempty thisenc means we are to link the issue to the encounter.
    $thisenc = 0 + (empty($_REQUEST['thisenc']) ? 0 : $_REQUEST['thisenc']);
}

if (isset($ISSUE_TYPES['ippf_gcac'])) {
    if ($ISSUE_TYPES['ippf_gcac']) {
        // Similarly for IPPF issues.
        require_once($GLOBALS['srcdir'].'/ippf_issues.inc.php');
    }
}

$info_msg = "";

// A nonempty thistype is an issue type to be forced for a new issue.
$thistype = empty($_REQUEST['thistype']) ? '' : $_REQUEST['thistype'];

if ($thistype && !$issue && !acl_check_issue($thistype, '', array('write', 'addonly'))) {
    die(xlt("Add is not authorized!"));
}

$tmp = getPatientData($thispid, "squad");
if ($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) {
    die(xlt("Not authorized for this squad!"));
}

function QuotedOrNull($fld)
{
    if ($fld) {
        return "'".add_escape_custom($fld)."'";
    }

    return "NULL";
}

function rbinput($name, $value, $desc, $colname)
{
    global $irow;
    $ret  = "<input type='radio' name='".attr($name)."' value='".attr($value)."'";
    if ($irow[$colname] == $value) {
        $ret .= " checked";
    }

    $ret .= " />".text($desc);
    return $ret;
}

// Given an issue type as a string, compute its index.
function issueTypeIndex($tstr)
{
    global $ISSUE_TYPES;
    $i = 0;
    foreach ($ISSUE_TYPES as $key => $value) {
        if ($key == $tstr) {
            break;
        }

        ++$i;
    }

    return $i;
}

function ActiveIssueCodeRecycleFn($thispid2, $ISSUE_TYPES2)
{
///////////////////////////////////////////////////////////////////////
// Active Issue Code Recycle Function authored by epsdky (2014-2015) //
///////////////////////////////////////////////////////////////////////

    $modeIssueTypes = array();
    $issueTypeIdx2 = array();
    $idx2 = 0;

    foreach ($ISSUE_TYPES2 as $issueTypeX => $isJunk) {
        $modeIssueTypes[$idx2] = $issueTypeX;
        $issueTypeIdx2[$issueTypeX] = $idx2;
        ++$idx2;
    }

    $pe2 = array($thispid2);
    $qs2 = str_repeat('?, ', count($modeIssueTypes) - 1) . '?';
    $sqlParameters2 = array_merge($pe2, $modeIssueTypes);

    $codeList2 = array();

    $issueCodes2 = sqlStatement("SELECT diagnosis FROM lists WHERE pid = ? AND enddate is NULL AND type IN ($qs2)", $sqlParameters2);

    while ($issueCodesRow2 = sqlFetchArray($issueCodes2)) {
        if ($issueCodesRow2['diagnosis'] != "") {
            $someCodes2 = explode(";", $issueCodesRow2['diagnosis']);
            $codeList2 = array_merge($codeList2, $someCodes2);
        }
    }

    if ($codeList2) {
        $codeList2 = array_unique($codeList2);
        sort($codeList2);
    }

    $memberCodes = array();
    $memberCodes[0] = array();
    $memberCodes[1] = array();
    $memberCodes[2] = array();

    $allowedCodes2 = array();
    $allowedCodes2[0] = collect_codetypes("medical_problem");
    $allowedCodes2[1] = collect_codetypes("diagnosis");
    $allowedCodes2[2] = collect_codetypes("drug");

  // Test membership of codes to each code type set
    foreach ($allowedCodes2 as $akey1 => $allowCodes2) {
        foreach ($codeList2 as $listCode2) {
            list($codeTyX,) = explode(":", $listCode2);

            if (in_array($codeTyX, $allowCodes2)) {
                array_push($memberCodes[$akey1], $listCode2);
            }
        }
    }

  // output sets of display options
    $displayCodeSets[0] = $memberCodes[0]; // medical_problem
    $displayCodeSets[1] = array_merge($memberCodes[1], $memberCodes[2]);  // allergy
    $displayCodeSets[2] = array_merge($memberCodes[2], $memberCodes[1]);  // medication
    $displayCodeSets[3] = $memberCodes[1];  // default

    echo "var listBoxOptionSets = new Array();\n\n";

    foreach ($displayCodeSets as $akey => $displayCodeSet) {
        echo "listBoxOptionSets[" . attr($akey) . "] = new Array();\n";

        if ($displayCodeSet) {
            foreach ($displayCodeSet as $dispCode2) {
                $codeDesc2 = lookup_code_descriptions($dispCode2);
                echo "listBoxOptionSets[" . attr($akey) . "][listBoxOptionSets[" . attr($akey) . "].length] = new Option(" . js_escape($dispCode2." (".trim($codeDesc2).") ") . ", " . js_escape($dispCode2) . ", false, false);\n";
            }
        }
    }

  // map issues to a set of display options
    $modeIndexMapping = array();

    foreach ($modeIssueTypes as $akey2 => $isJunk) {
        $modeIndexMapping[$akey2] = 3;
    }

    if (array_key_exists("medical_problem", $issueTypeIdx2)) {
        $modeIndexMapping[$issueTypeIdx2['medical_problem']] = 0;
    }

    if (array_key_exists("allergy", $issueTypeIdx2)) {
        $modeIndexMapping[$issueTypeIdx2['allergy']] = 1;
    }

    if (array_key_exists("medication", $issueTypeIdx2)) {
        $modeIndexMapping[$issueTypeIdx2['medication']] = 2;
    }

    echo "\nvar listBoxOptions2 = new Array();\n\n";

    foreach ($modeIssueTypes as $akey2 => $isJunk) {
        echo "listBoxOptions2[" . attr($akey2) . "] = listBoxOptionSets[" . attr($modeIndexMapping[$akey2]) . "];\n";
    }

///////////////////////////////////////////////////////////////////////
// End of Active Issue Code Recycle Function main code block         //
///////////////////////////////////////////////////////////////////////
}

// If we are saving, then save and close the window.
//
if ($_POST['form_save']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $i = 0;
    $text_type = "unknown";
    foreach ($ISSUE_TYPES as $key => $value) {
        if ($i++ == $_POST['form_type']) {
            $text_type = $key;
        }
    }

    $form_begin = fixDate($_POST['form_begin'], '');
    $form_end   = fixDate($_POST['form_end'], '');

    $form_injury_part = $_POST['form_medical_system'];
    $form_injury_type = $_POST['form_medical_type'];

    if ($issue) {
        $query = "UPDATE lists SET " .
        "type = '"        . add_escape_custom($text_type)                  . "', " .
        "title = '"       . add_escape_custom($_POST['form_title'])        . "', " .
        "comments = '"    . add_escape_custom($_POST['form_comments'])     . "', " .
        "begdate = "      . QuotedOrNull($form_begin)   . ", "  .
        "enddate = "      . QuotedOrNull($form_end)     . ", "  .
        "returndate = "   . QuotedOrNull($form_return)  . ", "  .
        "diagnosis = '"   . add_escape_custom($_POST['form_diagnosis'])    . "', " .
        "occurrence = '"  . add_escape_custom($_POST['form_occur'])        . "', " .
        "classification = '" . add_escape_custom($_POST['form_classification']) . "', " .
        "reinjury_id = '" . add_escape_custom($_POST['form_reinjury_id'])  . "', " .
        "referredby = '"  . add_escape_custom($_POST['form_referredby'])   . "', " .
        "injury_grade = '" . add_escape_custom($_POST['form_injury_grade']) . "', " .
        "injury_part = '" . add_escape_custom($form_injury_part)           . "', " .
        "injury_type = '" . add_escape_custom($form_injury_type)           . "', " .
        "outcome = '"     . add_escape_custom($_POST['form_outcome'])      . "', " .
        "destination = '" . add_escape_custom($_POST['form_destination'])   . "', " .
        "reaction ='"     . add_escape_custom($_POST['form_reaction'])     . "', " .
        "severity_al ='"     . add_escape_custom($_POST['form_severity_id'])     . "', " .
        "list_option_id ='"     . add_escape_custom($_POST['form_title_id'])     . "', " .
        "erx_uploaded = '0', " .
        "modifydate = NOW() " .
        "WHERE id = '" . add_escape_custom($issue) . "'";
        sqlStatement($query);
        if ($text_type == "medication" && enddate != '') {
            sqlStatement('UPDATE prescriptions SET '
            . 'medication = 0 where patient_id = ? '
            . " and upper(trim(drug)) = ? "
            . ' and medication = 1', array($thispid,strtoupper($_POST['form_title'])));
        }
    } else {
        $issue = sqlInsert("INSERT INTO lists ( " .
        "date, pid, type, title, activity, comments, begdate, enddate, returndate, " .
        "diagnosis, occurrence, classification, referredby, user, groupname, " .
        "outcome, destination, reinjury_id, injury_grade, injury_part, injury_type, " .
        "reaction, severity_al, list_option_id " .
        ") VALUES ( " .
        "NOW(), " .
        "'" . add_escape_custom($thispid) . "', " .
        "'" . add_escape_custom($text_type)                 . "', " .
        "'" . add_escape_custom($_POST['form_title'])       . "', " .
        "1, "                            .
        "'" . add_escape_custom($_POST['form_comments'])    . "', " .
        QuotedOrNull($form_begin)        . ", "  .
        QuotedOrNull($form_end)        . ", "  .
        QuotedOrNull($form_return)       . ", "  .
        "'" . add_escape_custom($_POST['form_diagnosis'])   . "', " .
        "'" . add_escape_custom($_POST['form_occur'])       . "', " .
        "'" . add_escape_custom($_POST['form_classification']) . "', " .
        "'" . add_escape_custom($_POST['form_referredby'])  . "', " .
        "'" . add_escape_custom($$_SESSION['authUser'])     . "', " .
        "'" . add_escape_custom($$_SESSION['authProvider']) . "', " .
        "'" . add_escape_custom($_POST['form_outcome'])     . "', " .
        "'" . add_escape_custom($_POST['form_destination']) . "', " .
        "'" . add_escape_custom($_POST['form_reinjury_id']) . "', " .
        "'" . add_escape_custom($_POST['form_injury_grade']) . "', " .
        "'" . add_escape_custom($form_injury_part)          . "', " .
        "'" . add_escape_custom($form_injury_type)          . "', " .
        "'" . add_escape_custom($_POST['form_reaction'])         . "', " .
        "'" . add_escape_custom($_POST['form_severity_id'])         . "', " .
        "'" . add_escape_custom($_POST['form_title_id'])         . "' " .
        ")");
    }

  // For record/reporting purposes, place entry in lists_touch table.
    setListTouch($thispid, $text_type);

    if ($text_type == 'ippf_gcac') {
        issue_ippf_gcac_save($issue);
    }

    if ($text_type == 'contraceptive') {
        issue_ippf_con_save($issue);
    }

  // If requested, link the issue to a specified encounter.
    if ($thisenc) {
        $query = "INSERT INTO issue_encounter ( " .
        "pid, list_id, encounter " .
        ") VALUES ( ?,?,? )";
        sqlStatement($query, array($thispid,$issue,$thisenc));
    }

    $tmp_title = $ISSUE_TYPES[$text_type][2] . ": $form_begin " .
    substr($_POST['form_title'], 0, 40);

  // Close this window and redisplay the updated list of issues.
  //
    echo "<html><body><script language='JavaScript'>\n";
    if ($info_msg) {
        echo " alert(" . js_escape($info_msg) . ");\n";
    }

    echo " var myboss = opener ? opener : parent;\n";
    echo " if (myboss.refreshIssue) myboss.refreshIssue(" . js_escape($issue) . "," . js_escape($tmp_title) . ");\n";
    echo " else if (myboss.reloadIssues) myboss.reloadIssues();\n";
    echo " else myboss.location.reload();\n";
    echo " dlgclose();\n";

    echo "</script></body></html>\n";
    exit();
}

$irow = array();
if ($issue) {
    $irow = sqlQuery("SELECT * FROM lists WHERE id = ?", array($issue));
    if (!acl_check_issue($irow['type'], '', 'write')) {
        die(xlt("Edit is not authorized!"));
    }
} elseif ($thistype) {
    $irow['type'] = $thistype;
}

$type_index = 0;

if (!empty($irow['type'])) {
    foreach ($ISSUE_TYPES as $key => $value) {
        if ($key == $irow['type']) {
            break;
        }

        ++$type_index;
    }
}
?>
<html>
<head>
<?php Header::setupHeader(['common', 'jquery-ui', 'datetime-picker', 'select2']); ?>
<title><?php echo ($issue) ? xlt('Edit Issue') : xlt('Add New Issue'); ?></title>

<style>

td, input, select, textarea {
 font-family: Arial, Helvetica, sans-serif;
 font-size: 10pt;
}

div.section {
 border: solid;
 border-width: 1px;
 border-color: #0000ff;
 margin: 0 0 0 10pt;
 padding: 5pt;
}

/* Override theme's selected tab top color so it matches tab contents. */
ul.tabNav li.current a { background:#ffffff; }

</style>

<script language="JavaScript">
 var aitypes = new Array(); // issue type attributes
 var aopts   = new Array(); // Option objects
<?php
 $i = 0;
foreach ($ISSUE_TYPES as $key => $value) {
    echo " aitypes[" . attr($i) . "] = " . js_escape($value[3]) . ";\n";
    echo " aopts[" . attr($i) . "] = new Array();\n";
    $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = ? AND activity = 1", array($key."_issue_list"));
    while ($res = sqlFetchArray($qry)) {
        echo " aopts[" . attr($i) . "][aopts[" . attr($i) . "].length] = new Option(" . js_escape(xl_list_label(trim($res['title']))) . ", " . js_escape(trim($res['option_id'])) . ", false, false);\n";
        if ($res['codes']) {
            echo " aopts[" . attr($i) . "][aopts[" . attr($i) . "].length-1].setAttribute('data-code'," . js_escape(trim($res['codes'])) . ");\n";
        }
    }

    ++$i;
}

///////////
ActiveIssueCodeRecycleFn($thispid, $ISSUE_TYPES);
///////////
?>

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 ///////////////////////////
 function codeBoxFunction2() {
  var f = document.forms[0];
  var x2 = f.form_codeSelect2.options[f.form_codeSelect2.selectedIndex].value;
  f.form_codeSelect2.selectedIndex = -1;
  var x6 = f.form_diagnosis.value;
  if (x6.length > 0) x6 += ";";
  x6 += x2;
  f.form_diagnosis.value = x6;
 }
 ///////////////////////////
 //
 // React to selection of an issue type.  This loads the associated
 // shortcuts into the selection list of titles, and determines which
 // rows are displayed or hidden.
 function newtype(index) {
  var f = document.forms[0];
  var theopts = f.form_titles.options;
  theopts.length = 0;
  var i = 0;
  for (i = 0; i < aopts[index].length; ++i) {
   theopts[i] = aopts[index][i];
  }
  document.getElementById('row_titles').style.display = i ? '' : 'none';
  //
  ///////////////////////
  var listBoxOpts2 = f.form_codeSelect2.options;
  listBoxOpts2.length = 0;
  var ix = 0;
  for (ix = 0; ix < listBoxOptions2[index].length; ++ix) {
   listBoxOpts2[ix] = listBoxOptions2[index][ix];
   listBoxOpts2[ix].title = listBoxOptions2[index][ix].text;
  }
  document.getElementById('row_codeSelect2').style.display = ix ? '' : 'none';

  //////////////////////
  //
  // Show or hide various rows depending on issue type, except do not
  // hide the comments or referred-by fields if they have data.

 $(document).ready(function(){
 var comdisp = (aitypes[index] == 1) ? 'none' : '';
  var revdisp = (aitypes[index] == 1) ? '' : 'none';
  var injdisp = (aitypes[index] == 2) ? '' : 'none';
  var nordisp = (aitypes[index] == 0) ? '' : 'none';
  // reaction row should be displayed only for medication allergy.
  var alldisp =  (index == <?php echo issueTypeIndex('allergy'); ?>) ? '' : 'none';
  document.getElementById('row_enddate'       ).style.display = comdisp;
  // Note that by default all the issues will not show the active row
  //  (which is desired functionality, since then use the end date
  //   to inactivate the item.)
  document.getElementById('row_active'        ).style.display = revdisp;
  document.getElementById('row_diagnosis'     ).style.display = comdisp;
  document.getElementById('row_occurrence'    ).style.display = comdisp;
  document.getElementById('row_classification').style.display = injdisp;
  document.getElementById('row_reinjury_id'   ).style.display = injdisp;
  document.getElementById('row_severity'      ).style.display = alldisp;
  document.getElementById('row_reaction'      ).style.display = alldisp;
  document.getElementById('row_referredby'    ).style.display = (f.form_referredby.value) ? '' : comdisp;
  //document.getElementById('row_comments'      ).style.display = (f.form_comments.value) ? '' : revdisp;
  document.getElementById('row_referredby'    ).style.display = (f.form_referredby.value) ? '' : comdisp;
 });
<?php
if ($ISSUE_TYPES['ippf_gcac'] && !$_POST['form_save']) {
    // Generate more of these for gcac and contraceptive fields.
    if (empty($issue) || $irow['type'] == 'ippf_gcac') {
        issue_ippf_gcac_newtype();
    }

    if (empty($issue) || $irow['type'] == 'contraceptive') {
        issue_ippf_con_newtype();
    }
}
?>
 }

 // If a clickoption title is selected, copy it to the title field.
 // If it has a code, add that too.
 function set_text() {
  var f = document.forms[0];
  f.form_title.value = f.form_titles.options[f.form_titles.selectedIndex].text;
  f.form_title_id.value = f.form_titles.options[f.form_titles.selectedIndex].value;
  f.form_diagnosis.value = f.form_titles.options[f.form_titles.selectedIndex].getAttribute('data-code');
  f.form_titles.selectedIndex = -1;
 }

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?issue=' + <?php echo js_url($issue); ?> + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 450);
  return false;
 }

 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
  closeme();
 }

 function closeme() {
    dlgclose();
 }

 // Called when the Active checkbox is clicked.  For consistency we
 // use the existence of an end date to indicate inactivity, even
 // though the simple verion of the form does not show an end date.
 function activeClicked(cb) {
  var f = document.forms[0];
  if (cb.checked) {
   f.form_end.value = '';
  } else {
   var today = new Date();
   f.form_end.value = '' + (today.getYear() + 1900) + '-' +
    (today.getMonth() + 1) + '-' + today.getDate();
  }
 }

 // Called when resolved outcome is chosen and the end date is entered.
 function outcomeClicked(cb) {
  var f = document.forms[0];
  if (cb.value == '1'){
   var today = new Date();
   f.form_end.value = '' + (today.getYear() + 1900) + '-' +
    ("0" + (today.getMonth() + 1)).slice(-2) + '-' + ("0" + today.getDate()).slice(-2);
   f.form_end.focus();
  }
 }

// This is for callback by the find-code popup.
// Appends to or erases the current list of diagnoses.
function set_related(codetype, code, selector, codedesc) {
 var f = document.forms[0];
 var s = f.form_diagnosis.value;
 var title = f.form_title.value;
 if (code) {
     //disabled duplicate codes
     if (s.indexOf(codetype + ':' + code) == -1){
         if (s.length > 0) s += ';';
         s += codetype + ':' + code;
     }
 } else {
  s = '';
 }
 f.form_diagnosis.value = s;
 if(title == '') f.form_title.value = codedesc;
}

// This is for callback by the find-code popup.
// Returns the array of currently selected codes with each element in codetype:code format.
function get_related() {
    return document.forms[0].form_diagnosis.value.split(';');
}

// This is for callback by the find-code popup.
// Deletes the specified codetype:code from the currently selected list.
function del_related(s) {
    my_del_related(s, document.forms[0].form_diagnosis, false);
}

// This invokes the find-code popup.
function sel_diagnosis() {
<?php
$url = '../encounter/find_code_dynamic.php?codetype=';
if ($irow['type'] == 'medical_problem') {
    $url .= urlencode(collect_codetypes("medical_problem", "csv"));
} else {
    $url .= urlencode(collect_codetypes("diagnosis", "csv"));
    $tmp  = urlencode(collect_codetypes("drug", "csv"));
    if ($irow['type'] == 'allergy') {
        if ($tmp) {
            $url .= ",$tmp";
        }
    } elseif ($irow['type'] == 'medication') {
        if ($tmp) {
            $url .= ",$tmp&default=$tmp";
        }
    }
}
?>
dlgopen(<?php echo js_escape($url); ?>, '_blank', 985, 800, '', <?php echo xlj("Select Codes"); ?>);
}

// Check for errors when the form is submitted.
function validate() {
 var f = document.forms[0];
 if(f.form_begin.value > f.form_end.value && (f.form_end.value)) {
  alert(<?php echo xlj('Please Enter End Date greater than Begin Date!'); ?>);
  return false;
 }
 if (! f.form_title.value) {
  alert(<?php echo xlj('Please enter a title!'); ?>);
  return false;
 }
 top.restoreSession();
 return true;
}

// Supports customizable forms (currently just for IPPF).
function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

$(function() {
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});
$('div').hide();
</script>

</head>

<body class="body_top" style="padding-right:0.5em">
<div class="container">
<ul class="tabNav">
 <li class='current'><a href='#'><?php echo xlt('Issue'); ?></a></li>
<?php
// Build html tab data for each visit form linked to this issue.
$tabcontents = '';
if ($issue) {
    $vres = sqlStatement(
        "SELECT f.id, f.encounter, f.form_name, f.form_id, f.formdir, fe.date " .
        "FROM forms AS f, form_encounter AS fe WHERE " .
        "f.pid = ? AND f.issue_id = ? AND f.deleted = 0 AND " .
        "fe.pid = f.pid and fe.encounter = f.encounter " .
        "ORDER BY fe.date DESC, f.id DESC",
        array($thispid, $issue)
    );
    while ($vrow = sqlFetchArray($vres)) {
        $formdir = $vrow['formdir'];
        $formid  = $vrow['form_id'];
        $visitid = $vrow['encounter'];
        echo " <li><a href='#'>" . text(oeFormatShortDate(substr($vrow['date'], 0, 10))) . ' ' .
            text($vrow['form_name']) . "</a></li>\n";
        $tabcontents .= "<div class='tab' style='height:90%;width:98%;'>\n";
        $tabcontents .= "<iframe frameborder='0' style='height:100%;width:100%;' " .
            "src='../../forms/LBF/new.php?formname=" . attr_url($formdir) . "&id=" . attr_url($formid) . "&visitid=" . attr_url($visitid) . "&from_issue_form=1'" .
            ">Oops</iframe>\n";
        $tabcontents .= "</div>\n";
    }
}
?>
</ul>


<div class="tabContainer">
    <div class='tab current' style='height:auto;width:97%;'>
        <div class='col-sm-12'>
            <form class="form-horizontal" name='theform' method="post" onsubmit='return validate()'>
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <?php
                // action setting not required in html5.  By default form will submit to itself.
                // Provide key values previously passed as part of action string.
                foreach (array('issue'=>$issue, 'thispid'=>$thispid, 'thisenc'=>$thisenc) as $fldName => $fldVal) {
                    printf('<input name="%s" type="hidden" value="%s"/>%s', attr($fldName), attr($fldVal), PHP_EOL);
                }
                ?>
                <div class="form-group">
                    <label for="" class="control-label col-xs-2"><?php echo xlt('Type'); ?>:</label>
                    <div class="col-xs-10">
                        <?php
                         $index = 0;
                        foreach ($ISSUE_TYPES as $key => $value) {
                            if ($issue || $thistype) {
                                if ($index == $type_index) {
                                    echo text($value[1]);
                                    echo "<input type='hidden' name='form_type' value='" . attr($index) . "'>\n";
                                }
                            } else {
                                echo "   <input type='radio' name='form_type' value='" . attr($index) . "' onclick='newtype(" . attr_js($index) . ")'";
                                if ($index == $type_index) {
                                    echo " checked";
                                }

                                if (!acl_check_issue($key, '', array('write','addonly'))) {
                                    echo " disabled";
                                }

                                echo " />" . text($value[1]) . "&nbsp;\n";
                            }

                            ++$index;
                        }
                        ?>
                    </div>
                </div>
                <div class="form-group" id='row_titles'>
                    <label for="form_titles" class="control-label col-xs-2"> </label>
                    <div class="col-xs-10">
                        <select name='form_titles' id='form_titles'  class= "form-control" multiple size='4' onchange='set_text()'></select>
                        <p><?php echo xlt('(Select one of these, or type your own title)'); ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-xs-2" for="title_diagnosis"><?php echo xlt('Title'); ?>:</label>
                    <div class="col-xs-10">
                        <input type='text' class="form-control" name='form_title' id='form_title' value='<?php echo attr($irow['title']) ?>'>
                        <input type='hidden' name='form_title_id' value='<?php echo attr($irow['list_option_id']) ?>'>
                    </div>
                </div>
                <div class="form-group" id='row_codeSelect2'>
                    <label for="form_codeSelect2" class="control-label col-xs-2"><?php echo xlt('Active Issue Codes'); ?>:</label>
                    <div class="col-xs-10">
                        <select name='form_codeSelect2' id='form_codeSelect2' class= "form-control" multiple size='4' onchange="codeBoxFunction2()" style="width:100%;"></select>
                    </div>
                </div>
                <div class="form-group" id='row_diagnosis'>
                    <label class="control-label col-xs-2" for="form_diagnosis"><?php echo xlt('Coding'); ?>:</label>
                    <div class="col-xs-10">
                        <input type='text' class="form-control" name='form_diagnosis' id='form_diagnosis'
                        value='<?php echo attr($irow['diagnosis']) ?>' onclick='sel_diagnosis()'
                        title='<?php echo xla('Click to select or change coding'); ?>' readonly >
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-xs-2" for="form_begin"><?php echo xlt('Begin Date'); ?>:</label>
                    <div class="col-xs-10">
                        <input type='text' class='datepicker form-control' style="width:50%" name='form_begin' id='form_begin'
                        value='<?php echo attr($irow['begdate']) ?>'
                        title='<?php echo xla('yyyy-mm-dd date of onset, surgery or start of medication'); ?>'>
                    </div>
                </div>
                <div class="form-group" id='row_enddate'>
                    <label class="control-label col-xs-2" for="form_begin"><?php echo xlt('End Date'); ?>:</label>
                    <div class="col-xs-10">
                        <input type='text' class='datepicker form-control' style="width:50%" name='form_end' id='form_end'
                        value='<?php echo attr($irow['enddate']) ?>'
                        title='<?php echo xla('yyyy-mm-dd date of recovery or end of medication'); ?>' />
                        &nbsp;(<?php echo xlt('leave blank if still active'); ?>)
                    </div>
                </div>
                <div class="form-group" id='row_active'>
                    <label class="control-label col-xs-2" for="form_active"><?php echo xlt('Active'); ?>: </label>
                    <div class="col-xs-10">
                        <div class="checkbox">
                            <label><input type='checkbox' name='form_active' id=='form_active' value='1' <?php echo ($irow['enddate']) ? "" : "checked"; ?>
                            onclick='activeClicked(this);'
                            title='<?php echo xla('Indicates if this issue is currently active'); ?>'></label>
                        </div>
                    </div>
                </div>
                <div class="form-group " id='row_returndate'>
                    <input type='hidden'  name='form_return' id='form_return' />
                    <input type='hidden'  name='row_reinjury_id' id='row_reinjury_id' />
                    <img id='img_return'/>
                </div>
                <div class="form-group"id='row_occurrence'>
                    <label class="control-label col-xs-2" for="form_occur"><?php echo xlt('Occurrence'); ?>:</label>
                    <div class="col-xs-10">
                        <?php
                        // Modified 6/2009 by BM to incorporate the occurrence items into the list_options listings
                        generate_form_field(array('data_type'=>1,'field_id'=>'occur','list_id'=>'occurrence','empty_title'=>'SKIP'), $irow['occurrence']);
                        ?>
                    </div>
                </div>
                <div class="form-group" id='row_classification'>
                    <label class="control-label col-xs-2" for="form_classification"><?php echo xlt('Classification'); ?>:</label>
                    <div class="col-xs-10">
                       <select name='form_classification' id='form_classification' class='form-control'>
                            <?php
                            foreach ($ISSUE_CLASSIFICATIONS as $key => $value) {
                                echo "   <option value='" . attr($key) . "'";
                                if ($key == $irow['classification']) {
                                    echo " selected";
                                }
                                echo ">" . text($value) . "\n";
                            }
                            ?>
                       </select>
                    </div>
                </div>
                <!-- Reaction For Medication Allergy -->
                <div class="form-group" id='row_severity'>
                    <label class="control-label col-xs-2" for="form_severity_id"><?php echo xlt('Severity'); ?>:</label>
                    <div class="col-xs-10">
                        <?php
                            $severity=$irow['severity_al'];
                            generate_form_field(array('data_type'=>1,'field_id'=>'severity_id','list_id'=>'severity_ccda','empty_title'=>'SKIP'), $severity);
                        ?>
                    </div>
                </div>
                <div class="form-group" id='row_reaction'>
                    <label class="control-label col-xs-2" for="form_reaction"><?php echo xlt('Reaction'); ?>:</label>
                    <div class="col-xs-10">
                        <?php
                            echo generate_select_list('form_reaction', 'reaction', $irow['reaction'], '', '', '', '');
                        ?>
                    </div>
                </div>
                <!-- End of reaction -->
                <div class="form-group"id='row_referredby'>
                    <label class="control-label col-xs-2" for="form_referredby"><?php echo xlt('Referred by'); ?>:</label>
                    <div class="col-xs-10">
                        <input type='text' name='form_referredby' id='form_referredby' class='form-control' value='<?php echo attr($irow['referredby']) ?>'
                        title='<?php echo xla('Referring physician and practice'); ?>' />
                    </div>
                </div>
                <div class="form-group" id='row_comments'>
                    <label class="control-label col-xs-2" for="form_comments"><?php echo xlt('Comments'); ?>:</label>
                    <div class="col-xs-10">
                      <textarea class="form-control" name='form_comments' id='form_comments' rows="4" id='form_comments'><?php echo text($irow['comments']) ?></textarea>
                    </div>
                </div>
                <div class="form-group"
                <?php
                if ($GLOBALS['ippf_specific']) {
                        echo " style='display:none;'";
                } ?>>
                    <label class="control-label col-xs-2" for="form_outcome"><?php echo xlt('Outcome'); ?>:</label>
                    <div class="col-xs-10">
                        <?php
                            echo generate_select_list('form_outcome', 'outcome', $irow['outcome'], '', '', '', 'outcomeClicked(this);');
                        ?>
                    </div>
                </div>
                <div class="form-group"
                <?php
                if ($GLOBALS['ippf_specific']) {
                        echo " style='display:none;'";
                } ?>>
                    <label class="control-label col-xs-2" for="form_destination"><?php echo xlt('Destination'); ?>:</label>
                    <div class="col-xs-10">
                        <?php if (true) { ?>
                           <input type='text' class='form-control' name='form_destination' id='form_destination' value='<?php echo attr($irow['destination']) ?>'
                            style='width:100%' title='GP, Secondary care specialist, etc.' />
                        <?php } else { // leave this here for now, please -- Rod ?>
                            <?php echo rbinput('form_destination', '1', 'GP', 'destination') ?>&nbsp;
                            <?php echo rbinput('form_destination', '2', 'Secondary care spec', 'destination') ?>&nbsp;
                            <?php echo rbinput('form_destination', '3', 'GP via physio', 'destination') ?>&nbsp;
                            <?php echo rbinput('form_destination', '4', 'GP via podiatry', 'destination') ?>
                        <?php } ?>
                    </div>
                </div>
                <br>
                <?php //can change position of buttons by creating a class 'position-override' and adding rule text-alig:center or right as the case may be in individual stylesheets ?>
                    <div class="form-group clearfix" id="button-container">
                        <div class="col-sm-12 text-left position-override">
                            <div class="btn-group btn-group-pinch" role="group">
                                <button type='submit' name='form_save'  class="btn btn-default btn-save"  value='<?php echo xla('Save'); ?>'><?php echo xlt('Save'); ?></button>
                                <button type="button" class="btn btn-link btn-cancel btn-separate-left" onclick='closeme();'><?php echo xlt('Cancel');?></button>
                                <?php
                                if ($issue && acl_check('admin', 'super')) { ?>
                                    <button type='submit' name='form_delete'  class="btn btn-default btn-cancel btn-delete btn-separate-left" onclick='deleteme()' value='<?php echo xla('Delete'); ?>'><?php echo xlt('Delete'); ?></button>
                                    <?php
                                } ?>
                            </div>
                        </div>
                    </div>
                <?php
                if ($ISSUE_TYPES['ippf_gcac']) {
                    if (empty($issue) || $irow['type'] == 'ippf_gcac') {
                        issue_ippf_gcac_form($issue, $thispid);
                    }

                    if (empty($issue) || $irow['type'] == 'contraceptive') {
                        issue_ippf_con_form($issue, $thispid);
                    }
                }
                ?>
            </form>
        </div>
    </div>
    <?php echo $tabcontents; ?>
</div>
</div>

<script language='JavaScript'>
 newtype(<?php echo js_escape($type_index); ?>);
 // Set up the tabbed UI.
 tabbify();

$(function() {
    // Include bs3 / bs4 classes here.  Keep html tags functional.
    $('table').addClass('table table-sm');
});
</script>

<?php validateUsingPageRules($_SERVER['PHP_SELF']);?>


</body>
</html>
