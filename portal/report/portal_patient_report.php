<?php

/**
 * portal_patient_report.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady@sparmy.com>
 * @copyright Copyright (c) 2016-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

//landing page definition -- where to go if something goes wrong
$landingpage = "../index.php?site=" . urlencode($_SESSION['site_id']);
//

// kick out if patient not authenticated
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $pid = $_SESSION['pid'];
    $user = $_SESSION['sessionUser'];
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit;
}

$ignoreAuth_onsite_portal = true;
global $ignoreAuth_onsite_portal;

require_once('../../interface/globals.php');
require_once("$srcdir/lists.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/patient.inc");

use OpenEMR\Core\Header;

// get various authorization levels
$auth_notes_a  = true; //AclMain::aclCheckCore('encounters', 'notes_a');
$auth_notes    = true; //AclMain::aclCheckCore('encounters', 'notes');
$auth_coding_a = true; //AclMain::aclCheckCore('encounters', 'coding_a');
$auth_coding   = true; //AclMain::aclCheckCore('encounters', 'coding');
$auth_relaxed  = true; //AclMain::aclCheckCore('encounters', 'relaxed');
$auth_med      = true; //AclMain::aclCheckCore('patients'  , 'med');
$auth_demo     = true; //AclMain::aclCheckCore('patients'  , 'demo');

$ignoreAuth_onsite_portal = true;
?>

<?php Header::setupAssets('textformat'); ?>

<style>
    input[type="checkbox"],
    input[type="radio"] {
        margin: 0 5px 5px;
        line-height: normal;
    }

    /*=============================================================
 * Patient Reports
 * seen in the patient reports screens
 *============================================================*/
    #patient_reports {
        width: 100%;
    }

    #patient_reports .issues {
        padding-right: 30px;
    }

    #patient_reports .issues table {
        margin: 10px 0 10px 0;
    }

    #patient_reports .issues td {
        padding: 2px;
    }

    #patient_reports .encounters td {
        padding: 2px;
    }

    #patient_reports .encounter_forms {
        margin: 5px 15px 5px 15px;
    }

    #patient_reports td {
        vertical-align: top;
    }

    #patient_reports ul {
        list-style: none;
    }

    /*=============================================================
 * Report - Custom
 * seen as the patient report (portal_custom_report.php)
 *============================================================*/
    #report_custom {
        width: 100%;
    }

    #report_custom hr {
        border: 2px dotted #000;
    }

    #report_custom .billing {
        margin: 5px;
        padding: 5px;
    }

    #report_custom h1 {
        font-size: 120%;
        margin: 0 0 5px 0;
        padding: 0;
    }

    #report_custom .immunizations {
        margin: 5px;
        padding: 5px;
    }

    #report_custom .notes {
        margin: 5px;
        padding: 5px;
    }

    #report_custom .transactions {
        margin: 5px;
        padding: 5px;
    }

    #report_custom .communications {
        margin: 5px;
        padding: 5px;
    }

    #report_custom .documents {
        margin: 5px;
        padding: 5px;
    }

    #report_custom .demographics {
        margin: 5px;
        padding: 5px;
    }

    #report_custom .insurance {
        margin: 5px;
        padding: 5px;
    }

    #report_custom .history {
        margin: 5px;
        padding: 5px;
    }

    #report_custom .issue {
        margin-left: 20px;
    }

    #report_custom .issue_type {
        font-weight: bold;
        padding: 5px 0 5px 0;
    }

    #report_custom .issue_diag {
        margin: 0 20px 0 20px;
    }

    #report_custom .encounter {
        width: 100%;
        border-top: 2px dotted #000;
        padding: 10px 5px 10px 5px;
        margin-top: 10px;
    }

    #report_custom .encounter h1 {
        font-size: 140%;
        margin: 0;
        padding: 0;
    }

    #report_custom .encounter_form {
        margin: 10px;
        padding: 10px;
        border-top: 1px solid #808080;
    }

    #addressbook_list tr.evenrow {
        background-color: #ddddff;
    }

    #addressbook_list tr.oddrow {
        background-color: #ffffff;
    }

    tr.odd,
    td.even {
        background-color: #ffffff !important;
    }
</style>

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
var mypcc = '<?php echo $GLOBALS['phone_country_code']; ?>';
</script>

<body class="body_top">
<div id="patient_reports"> <!-- large outer DIV -->

<?php if ($GLOBALS['activate_ccr_ccd_report']) { // show CCR/CCD reporting options ?>
<div id="ccr_report">

<form name='ccr_form' id='ccr_form' method='post' action="../ccr/createCCR.php?portal_auth=1">
<span class='title'><?php echo xlt('Continuity of Care Record (CCR)'); ?></span>&nbsp;&nbsp;
<br/>
<span class='text'>(<?php echo xlt('Pop ups need to be enabled to see these reports'); ?>)</span>
<br/>
<br/>
<input type='hidden' name='ccrAction'>
<input type='hidden' name='raw'>
<input type="checkbox" name="show_date" id="show_date" onchange="show_date_fun();" ><span class='text'><?php echo xlt('Use Date Range'); ?></span>
<br />
<div id="date_div" style="display:none" >
  <br />
  <table border="0" cellpadding="0" cellspacing="0" >
    <tr>
      <td>
        <span class='bold'><?php echo xlt('Start Date');?>: </span>
      </td>
      <td>
        <input type='text' size='10' name='Start' id='Start' class='datepicker' title='<?php echo xla('yyyy-mm-dd'); ?>' />
      </td>
      <td>
        &nbsp;
        <span class='bold'><?php echo xlt('End Date');?>: </span>
      </td>
      <td>
        <input type='text' size='10' name='End' id='End' class='datepicker' title='<?php echo xla('yyyy-mm-dd'); ?>' />
      </td>
    </tr>
  </table>
</div>
<br />

<!-- <button data-target="#reportdialog" data-toggle="modal" class="btn btn-secondary">
    <?php //echo xla('Generate Report'); ?></button> -->
<input type="button" class="generateCCR" value="<?php echo xla('Generate Report'); ?>" />
<!-- <input type="button" class="generateCCR_download_h" value="<?php echo xl('Download') . " (Hybrid)"; ?>" /> -->
<input type="button" class="generateCCR_download_p" value="<?php echo xla('Download'); ?>" />
    <?php if ($GLOBALS['phimail_enable'] == true && $GLOBALS['phimail_ccr_enable'] == true) { ?>
<input type="button" class="viewCCR_send_dialog" value="<?php echo xla('Transmit'); ?>" />
             <br />
             <div id="ccr_send_dialog" style="display:none" >
              <br />
              <table border="0" cellpadding="0" cellspacing="0" >
               <tr>
                <td>
                 <span class='bold'><?php echo xlt('Enter Recipient\'s Direct Address');?>: </span>
                <input type="text" size="64" name="ccr_send_to" id="ccr_send_to" value="">
                <input type="hidden" name="ccr_sent_by" id="ccr_sent_by" value="user">
                <input type="button" class="viewCCR_transmit" value="<?php echo xla('Send'); ?>" />
                <div id="ccr_send_result" style="display:none" >
                 <span class="text" id="ccr_send_message"></span>
                </div>
                </td>
              </tr>
              </table>
             </div>
<?php } ?>
<hr/>
<span class='title'><?php echo xlt('Continuity of Care Document (CCD)'); ?></span>&nbsp;&nbsp;
<br/>
<span class='text'>(<?php echo xlt('Pop ups need to be enabled to see these reports'); ?>)</span>
<br/>
<br/>
<input type="button" class="viewCCD" value="<?php echo xla('Generate Report'); ?>" />
<input type="button" class="viewCCD_download" value="<?php echo xla('Download'); ?>" />
    <?php if ($GLOBALS['phimail_enable'] == true && $GLOBALS['phimail_ccd_enable'] == true) { ?>
<input type="button" class="viewCCD_send_dialog" value="<?php echo xla('Transmit'); ?>" />
             <br />
             <div id="ccd_send_dialog" style="display:none" >
              <br />
              <table border="0" cellpadding="0" cellspacing="0" >
               <tr>
                <td>
                 <span class='bold'><?php echo xlt('Enter Recipient\'s Direct Address');?>: </span>
                <input type="text" size="64" name="ccd_send_to" id="ccd_send_to" value="">
        <input type="hidden" name="ccd_sent_by" id="ccd_sent_by" value="user">
                <input type="button" class="viewCCD_transmit" value="<?php echo xla('Send'); ?>" />
                <div id="ccd_send_result" style="display:none" >
                 <span class="text" id="ccd_send_message"></span>
                </div>
                </td>
              </tr>
              </table>
             </div>
<?php } ?>

</form>

<hr/>

</div>
<?php } // end CCR/CCD reporting options ?>

<form name='report_form' id="report_form" method='post' action='./report/portal_custom_report.php'>

<span class='card-heading'><?php echo xlt('Patient Report'); ?></span>&nbsp;&nbsp;
<a class="link_submit" href="#" onclick="return checkAll(true)"><?php echo xlt('Check All'); ?></a>
|
<a class="link_submit" href="#" onclick="return checkAll(false)"><?php echo xlt('Clear All'); ?></a>
<p>

<table class="table includes">
 <tr>
  <td class='text'>
   <input type='checkbox' name='include_demographics' id='include_demographics' value="demographics" checked><?php echo xlt('Demographics'); ?><br />
   <input type='checkbox' name='include_history' id='include_history' value="history"><?php echo xlt('History'); ?><br />
   <input type='checkbox' name='include_insurance' id='include_insurance' value="insurance"><?php echo xlt('Insurance'); ?><br />
   <input type='checkbox' name='include_billing' id='include_billing' value="billing"
    <?php if (!$GLOBALS['simplified_demographics']) {
        echo 'checked';
    } ?>><?php echo xlt('Billing'); ?><br />
  </td>
  <td class='text'>
   <!--
   <input type='checkbox' name='include_allergies' id='include_allergies' value="allergies">Allergies<br />
   <input type='checkbox' name='include_medications' id='include_medications' value="medications">Medications<br />
   -->
   <input type='checkbox' name='include_immunizations' id='include_immunizations' value="immunizations"><?php echo xlt('Immunizations'); ?><br />
   <!--
   <input type='checkbox' name='include_medical_problems' id='include_medical_problems' value="medical_problems">Medical Problems<br />
   -->
   <input type='checkbox' name='include_notes' id='include_notes' value="notes"><?php echo xlt('Patient Notes'); ?><br />
   <input type='checkbox' name='include_transactions' id='include_transactions' value="transactions"><?php echo xlt('Transactions'); ?><br />
   <input type='checkbox' name='include_batchcom' id='include_batchcom' value="batchcom"><?php echo xlt('Communications'); ?><br />
  </td>
 </tr>
</table>

<input type='hidden' name='pdf' value='0'>
<br />

<!-- old ccr button position -->

<table class="issues_encounters_forms table">
 <tr>

  <!-- Issues -->
  <td class='text'>
  <div class="issues">
  <span class='bold'><?php echo xlt('Issues'); ?>:</span>
   <br />
   <br />

   <table>

<?php
// get issues
$pres = sqlStatement("SELECT * FROM lists WHERE pid = ? " .
                    "ORDER BY type, begdate", [$pid]);
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
        echo "  <td colspan='4' class='bold'><b>" . text($disptype) . "</b></td>\n";
        echo " </tr>\n";
    }

    $rowid = $prow['id'];
    $disptitle = trim($prow['title']) ? $prow['title'] : "[Missing Title]";

    $ieres = sqlStatement("SELECT encounter FROM issue_encounter WHERE " .
                        "pid = ? AND list_id = ?", [$pid, $rowid]);

    echo "    <tr class='text'>\n";
    echo "     <td>&nbsp;</td>\n";
    echo "     <td>";
    echo "<input type='checkbox' name='issue_" . attr($rowid) . "' id='issue_" . attr($rowid) . "' class='issuecheckbox' value='/";
    while ($ierow = sqlFetchArray($ieres)) {
        echo text($ierow['encounter']) . "/";
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

<?php //endif; // end of Issues output ?>

   </div> <!-- end issues DIV -->
  </td>

<!-- Encounters and Forms -->

<td class='text'>
<div class='encounters'>
<span class='bold'><?php echo xlt('Encounters & Forms'); ?>:</span>
<br /><br />

<?php if (!($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) : ?>
(Encounters not authorized)
<?php else : ?>
    <?php

    $isfirst = 1;
    $res = sqlStatement("SELECT forms.encounter, forms.form_id, forms.form_name, " .
                    "forms.formdir, forms.date AS fdate, form_encounter.date " .
                    ",form_encounter.reason " .
                    "FROM forms, form_encounter WHERE " .
                    "forms.pid = ? AND form_encounter.pid = ? AND " .
                    "form_encounter.encounter = forms.encounter " .
                    " AND forms.deleted=0 " . // --JRM--
                    "ORDER BY form_encounter.date DESC, fdate ASC", [$pid, $pid]);
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
                $result['reason'] = substr($result['reason'], 0, $maxReasonLength) . " ... ";
            }

            echo attr($result["reason"]) .
                " (" . date("Y-m-d", strtotime($result["date"])) .
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

            if (!is_array($html_strings[$form_name] ?? null)) {
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
        if ($toprint = $html_strings[$var] ?? null) {
            foreach ($toprint as $var) {
                print $var;
            }
        }
    }
    ?>

<?php endif; ?>

  </div> <!-- end encounters DIV -->
  </td>
 </tr>
</table>

<!-- Procedure Orders -->
<hr/>
<table border="0" cellpadding="0" cellspacing="0" >
 <tr>
  <td class='bold'><?php echo xlt('Procedures'); ?>&nbsp;&nbsp;</td>
  <td class='text'><?php echo xlt('Order Date'); ?>&nbsp;&nbsp;</td>
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
    echo "  <td align='center' class='text'>" .
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

<hr/>
<span class="bold"><?php echo xlt('Documents'); ?></span>:<br />
<ul>
<?php
// show available documents
$db = $GLOBALS['adodb']['db'];
$sql = "SELECT d.id, d.url, d.name as document_name, c.name FROM documents AS d " .
        "LEFT JOIN categories_to_documents AS ctd ON d.id=ctd.document_id " .
        "LEFT JOIN categories AS c ON c.id = ctd.category_id WHERE " .
        "d.foreign_id = ? AND d.deleted = 0";
$result = $db->Execute($sql, [$pid]);
if ($db->ErrorMsg()) {
    echo $db->ErrorMsg();
}

while ($result && !$result->EOF) {
    $fname = basename($result->fields['url']);
    $extension = strtolower(substr($fname, strrpos($fname, ".")));
    if ($extension !== '.zip' && $extension !== '.dcm') {
        echo "<li class='bold'>";
        echo '<input type="checkbox" name="documents[]" value="' .
            $result->fields['id'] . '">';
        echo '&nbsp;&nbsp;<i>' . text(xl_document_category($result->fields['name'])) . "</i>";
        echo '&nbsp;&nbsp;' . xlt('Name') . ': <i>' . text(basename($result->fields['url'])) . "</i>";
        echo '</li>';
    }

    $result->MoveNext();
}
?>
</ul>
</form>

<input type="button" class="genreport" value="<?php echo xla('Generate Report'); ?>" />&nbsp;
<input type="button" class="genpdfrep" value="<?php echo xla('Download PDF'); ?>" />&nbsp;

</div>  <!-- close patient_reports DIV -->

<script>

// jQuery stuff to make the page a little easier to use
initReport = function(){
    $("body").on("click", ".genreport", function() {
          document.report_form.pdf.value = 0;
          showCustom();

          return false;
         });
    $(".genpdfrep").click(function() {  document.report_form.pdf.value = 1; $("#report_form").submit(); });
    $(".genportal").click(function() {  document.report_form.pdf.value = 2; $("#report_form").submit(); });
    $("#genfullreport").click(function() { location.href='<?php echo (!empty($returnurl)) ? "$rootdir/patient_file/encounter/$returnurl"  : '';?>'; });
    //$("#printform").click(function() { PrintForm(); });
    $(".issuecheckbox").click(function() { issueClick(this); });

    // check/uncheck all Forms of an encounter
    $(".encounter").click(function() { SelectForms(this); });

    function showCustom(){
        var formval = $( "#report_form" ).serializeArray();
        var title = <?php echo xlj("Custom Report") ?>;
        var params = {
            sizeHeight: 'full',
            size: 'modal-lg',
            title: title,
            type: "POST",
            url: './report/portal_custom_report.php',
            data: formval
        };

        // returns a promise after dialog inits. Just an empty fulfill for an example.
        // Could do an alert or confirm etc.
        return dialog.ajax(params)
        .then(function (dialog) {
            $('div.modal-body', dialog).addClass('overflow-auto');
        });
    }
    $(".generateCCR").click(
        function() {
            if(document.getElementById('show_date').checked == true){
                if(document.getElementById('Start').value == '' || document.getElementById('End').value == ''){
                   alert(<?php echo xlj('Please select a start date and end date'); ?>);
                        return false;
                }
            }
        var ccrAction = document.getElementsByName('ccrAction');
        ccrAction[0].value = 'generate';
        var raw = document.getElementsByName('raw');
        raw[0].value = 'no';

        ccr_form.setAttribute("target", "_blank");
        $("#ccr_form").submit();
        ccr_form.setAttribute("target", "");
    });
        $(".generateCCR_raw").click(
        function() {
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'generate';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'yes';

                ccr_form.setAttribute("target", "_blank");
                $("#ccr_form").submit();
                ccr_form.setAttribute("target", "");
        });
        $(".generateCCR_download_h").click(
        function() {
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'generate';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'hybrid';

                $("#ccr_form").submit();
        });
        $(".generateCCR_download_p").click(
        function() {
            if(document.getElementById('show_date').checked === true){
                if(document.getElementById('Start').value === '' || document.getElementById('End').value === ''){
                        alert(<?php echo xlj('Please select a start date and end date'); ?>);
                        return false;
                }
            }
            var ccrAction = document.getElementsByName('ccrAction');
            ccrAction[0].value = 'generate';
            var raw = document.getElementsByName('raw');
            raw[0].value = 'pure';

            $("#ccr_form").submit();
        });
    $(".viewCCD").click(
    function() {
        var ccrAction = document.getElementsByName('ccrAction');
        ccrAction[0].value = 'viewccd';
            var raw = document.getElementsByName('raw');
            raw[0].value = 'no';

            ccr_form.setAttribute("target", "_blank");
        $("#ccr_form").submit();
                ccr_form.setAttribute("target", "");
    });
        $(".viewCCD_raw").click(
        function() {
            var ccrAction = document.getElementsByName('ccrAction');
            ccrAction[0].value = 'viewccd';
            var raw = document.getElementsByName('raw');
            raw[0].value = 'yes';

            ccr_form.setAttribute("target", "_blank");
            $("#ccr_form").submit();
            ccr_form.setAttribute("target", "");
        });
        $(".viewCCD_download").click(
        function() {
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'viewccd';
                var raw = document.getElementsByName('raw');
                raw[0].value = 'pure';
                $("#ccr_form").submit();
        });
<?php if ($GLOBALS['phimail_enable'] == true && $GLOBALS['phimail_ccr_enable'] == true) { ?>
        $(".viewCCR_send_dialog").click(
        function() {
                $("#ccr_send_dialog").toggle();
        });
        $(".viewCCR_transmit").click(
        function() {
                $(".viewCCR_transmit").attr('disabled','disabled');
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'generate';
                var ccrRecipient = $("#ccr_send_to").val();
                var raw = document.getElementsByName('raw');
                raw[0].value = 'send '+ccrRecipient;
                if(ccrRecipient === "") {
                  $("#ccr_send_message").html("<?php
                    echo xla('Please enter a valid Direct Address above.'); ?>");
                  $("#ccr_send_result").show();
                } else {
                  $(".viewCCR_transmit").attr('disabled','disabled');
                  $("#ccr_send_message").html("<?php
                    echo xla('Working... this may take a minute.'); ?>");
                  $("#ccr_send_result").show();
                  var action=$("#ccr_form").attr('action');
                  $.post(action, {ccrAction:'generate',raw:'send '+ccrRecipient,requested_by:'user'},
                     function(data) {
                       if(data === "SUCCESS") {
                         $("#ccr_send_message").html("<?php
                            echo xla('Your message was submitted for delivery to');
                            ?> "+ccrRecipient);
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
        $(".viewCCD_send_dialog").click(
        function() {
                $("#ccd_send_dialog").toggle();
        });
        $(".viewCCD_transmit").click(
        function() {
                $(".viewCCD_transmit").attr('disabled','disabled');
                var ccrAction = document.getElementsByName('ccrAction');
                ccrAction[0].value = 'viewccd';
                var ccdRecipient = $("#ccd_send_to").val();
                var raw = document.getElementsByName('raw');
                raw[0].value = 'send '+ccdRecipient;
                if(ccdRecipient === "") {
                  $("#ccd_send_message").html("<?php
                    echo xla('Please enter a valid Direct Address above.'); ?>");
                  $("#ccd_send_result").show();
                } else {
                  $(".viewCCD_transmit").attr('disabled','disabled');
                  $("#ccd_send_message").html("<?php
                    echo xla('Working... this may take a minute.'); ?>");
                  $("#ccd_send_result").show();
                  var action=$("#ccr_form").attr('action');
                  $.post(action, {ccrAction:'viewccd',raw:'send '+ccdRecipient,requested_by:'user'},
                     function(data) {
                       if(data === "SUCCESS") {
                         $("#ccd_send_message").html("<?php
                            echo xla('Your message was submitted for delivery to');
                            ?> "+ccdRecipient);
                         $("#ccd_send_to").val("");
                       } else {
                         $("#ccd_send_message").html(data);
                       }
                       $(".viewCCD_transmit").removeAttr('disabled');
                  });
                }
        });
<?php } ?>
}; // end initReport

$(function () {

    initReport();

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    });
});

// select/deselect the Forms related to the selected Encounter
// (it ain't pretty code folks)
var SelectForms = function (selectedEncounter) {
    if ($(selectedEncounter).prop("checked")) {
        $(selectedEncounter).parent().children().each(function (i, obj) {
            $(this).children().each(function (i, obj) {
                $(this).prop("checked", true);
            });
        });
    } else {
        $(selectedEncounter).parent().children().each(function (i, obj) {
            $(this).children().each(function (i, obj) {
                $(this).prop("checked", false);
            });
        });
    }
}

// When an issue is checked, auto-check all the related encounters and forms
function issueClick(issue) {
    // do nothing when unchecked
    if (!$(issue).prop("checked")) return;

    $("#report_form :checkbox").each(function (i, obj) {
        if ($(issue).val().indexOf('/' + $(this).val() + '/') >= 0) {
            $(this).prop("checked", true);
        }

    });
}

</script>

