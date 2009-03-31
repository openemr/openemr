<?php
include_once("../../globals.php");
include_once("$srcdir/lists.inc");
include_once("$srcdir/acl.inc");
include_once("$srcdir/forms.inc");

// get various authorization levels
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_coding_a = acl_check('encounters', 'coding_a');
$auth_coding   = acl_check('encounters', 'coding');
$auth_relaxed  = acl_check('encounters', 'relaxed');
$auth_med      = acl_check('patients'  , 'med');
$auth_demo     = acl_check('patients'  , 'demo');

?>
<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- include jQuery support -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>

<script language='JavaScript'>

function checkAll(check) {
 var f = document.forms[0];
 for (var i = 0; i < f.elements.length; ++i) {
  if (f.elements[i].type == 'checkbox') f.elements[i].checked = check;
 }
 return false;
}

</script>

</head>

<body class="body_top">
<div id="patient_reports"> <!-- large outer DIV -->

<span class='title'><?php xl('Patient Report','e'); ?></span>&nbsp;&nbsp;

<!--
<a class="link_submit" href="full_report.php" onclick="top.restoreSession()">
[<?php xl('View Comprehensive Patient Report','e'); ?>]</a>
-->
<a class="link_submit" href="#" onclick="return checkAll(true)">[<?php xl('Check All','e'); ?>]</a>
&nbsp;
<a class="link_submit" href="#" onclick="return checkAll(false)">[<?php xl('Clear All','e'); ?>]</a>
<p>

<form name='report_form' id="report_form" method='post' action='custom_report.php'>

<table class="includes">
 <tr>
  <td class='text'>
   <input type='checkbox' name='include_demographics' id='include_demographics' value="demographics" checked><?php xl('Demographics','e'); ?><br>
   <?php if (acl_check('patients', 'med')): ?>
   <input type='checkbox' name='include_history' id='include_history' value="history"><?php xl(' History','e'); ?><br>
   <?php endif; ?>
   <!--
   <input type='checkbox' name='include_employer' id='include_employer' value="employer"><?php xl('Employer','e'); ?><br>
   -->
   <input type='checkbox' name='include_insurance' id='include_insurance' value="insurance"><?php xl('Insurance','e'); ?><br>
   <input type='checkbox' name='include_billing' id='include_billing' value="billing"
    <?php if (!$GLOBALS['simplified_demographics']) echo 'checked'; ?>><?php xl('Billing','e'); ?><br>
  </td>
  <td class='text'>
   <!--
   <input type='checkbox' name='include_allergies' id='include_allergies' value="allergies">Allergies<br>
   <input type='checkbox' name='include_medications' id='include_medications' value="medications">Medications<br>
   -->
   <input type='checkbox' name='include_immunizations' id='include_immunizations' value="immunizations"><?php xl('Immunizations','e'); ?><br>
   <!--
   <input type='checkbox' name='include_medical_problems' id='include_medical_problems' value="medical_problems">Medical Problems<br>
   -->
   <input type='checkbox' name='include_notes' id='include_notes' value="notes"><?php xl('Patient Notes','e'); ?><br>
   <input type='checkbox' name='include_transactions' id='include_transactions' value="transactions"><?php xl('Transactions','e'); ?><br>
   <input type='checkbox' name='include_batchcom' id='include_batchcom' value="batchcom"><?php xl('Communications','e'); ?><br>
  </td>
 </tr>
</table>

<br>
<input type="button" class="genreport" value="<?php xl('Generate Report','e'); ?>" />
<hr/>

<table class="issues_encounters_forms">
 <tr>

  <!-- Issues -->
  <td class='text'>
  <div class="issues">
  <span class='bold'><?php xl('Issues','e'); ?>:</span>
   <br>
   <br>

<?php if (! acl_check('patients', 'med')): ?>
<br>(Issues not authorized)

<?php else: ?>
   <table>

<?php
// get issues
$pres = sqlStatement("SELECT * FROM lists WHERE pid = $pid " .
                    "ORDER BY type, begdate");
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
        echo "  <td colspan='4' class='bold'><b>$disptype</b></td>\n";
        echo " </tr>\n";
    }
    $rowid = $prow['id'];
    $disptitle = trim($prow['title']) ? $prow['title'] : "[Missing Title]";

    $ieres = sqlStatement("SELECT encounter FROM issue_encounter WHERE " .
                        "pid = '$pid' AND list_id = '$rowid'");

    echo "    <tr class='text'>\n";
    echo "     <td>&nbsp;</td>\n";
    echo "     <td>";
    echo "<input type='checkbox' name='issue_$rowid' id='issue_$rowid' class='issuecheckbox' value='/";
    while ($ierow = sqlFetchArray($ieres)) {
        echo $ierow['encounter'] . "/";
    }
    echo "' />$disptitle</td>\n";
    echo "     <td>" . $prow['begdate'];

    if ($prow['enddate']) { echo " - " . $prow['enddate']; }
    else { echo " Active"; }

    echo "</td>\n";
    echo "</tr>\n";
}
?>
   </table>

<?php endif; // end of Issues output ?>

   </div> <!-- end issues DIV -->
  </td>

<!-- Encounters and Forms -->

<td class='text'>
<div class='encounters'>
<span class='bold'><?php xl('Encounters &amp; Forms','e'); ?>:</span>
<br><br>

<?php if (!($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)): ?>
(Encounters not authorized)
<?php else: ?>

<?php

$isfirst = 1;
$res = sqlStatement("SELECT forms.encounter, forms.form_id, forms.form_name, " .
                    "forms.formdir, forms.date AS fdate, form_encounter.date " .
                    ",form_encounter.reason ".
                    "FROM forms, form_encounter WHERE " .
                    "forms.pid = '$pid' AND form_encounter.pid = '$pid' AND " .
                    "form_encounter.encounter = forms.encounter " .
                    " AND forms.deleted=0 ". // --JRM--
                    "ORDER BY form_encounter.date DESC, fdate ASC");
$res2 = sqlStatement("SELECT name FROM registry ORDER BY priority");
$html_strings = array();
$registry_form_name = array();
while($result2 = sqlFetchArray($res2)) {
    array_push($registry_form_name,trim($result2['name']));
}
while($result = sqlFetchArray($res)) {
    if ($result{"form_name"} == "New Patient Encounter") {
        if ($isfirst == 0) {
            foreach($registry_form_name as $var) {
                if ($toprint = $html_strings[$var]) { 
                    foreach($toprint as $var) {print $var;}
                }
            }
            $html_strings = array();
            echo "</div>\n"; // end DIV encounter_forms
            echo "</div>\n\n";  //end DIV encounter_data 
            echo "<br>";
        }
        $isfirst = 0;
        echo "<div class='encounter_data'>\n";
        echo "<input type=checkbox ".
                " name='" . $result{"formdir"} . "_" .  $result{"form_id"} . "'".
                " id='" . $result{"formdir"} . "_" .  $result{"form_id"} . "'".
                " value='" . $result{"encounter"} . "'" .
                " class='encounter'".
                " >";

        // show encounter reason, not just 'New Encounter'
        // trim to a reasonable length for display purposes --cfapress
        $maxReasonLength = 20;
        if (strlen($result["reason"]) > $maxReasonLength) {
            $result['reason'] = substr($result['reason'], 0, $maxReasonLength) . " ... ";
        }

        echo $result{"reason"}. 
                " (" . date("Y-m-d",strtotime($result{"date"})) .
                ")\n";
        echo "<div class='encounter_forms'>\n";
    } 
    else {
        $form_name = trim($result{"form_name"});
        //if form name is not in registry, look for the closest match by
        // finding a registry name which is  at the start of the form name.
        //this is to allow for forms to put additional helpful information
        //in the database in the same string as their form name after the name
        $form_name_found_flag = 0;
        foreach($registry_form_name as $var) {if ($var == $form_name) {$form_name_found_flag = 1;}}
        // if the form does not match precisely with any names in the registry, now see if any front partial matches
        // and change $form_name appropriately so it will print above in $toprint = $html_strings[$var]
        if (!$form_name_found_flag) { foreach($registry_form_name as $var) {if (strpos($form_name,$var) == 0) {$form_name = $var;}}}
     
        if (!is_array($html_strings[$form_name])) {$html_strings[$form_name] = array();}
        array_push($html_strings[$form_name], "<input type='checkbox' ".
                                                " name='" . $result{"formdir"} . "_" . $result{"form_id"} . "'".
                                                " id='" . $result{"formdir"} . "_" . $result{"form_id"} . "'".
                                                " value='" . $result{"encounter"} . "'" .
                                                " class='encounter_form' ".
                                                ">" . $result{"form_name"} . "<br>\n");
    }
}
foreach($registry_form_name as $var) {
    if ($toprint = $html_strings[$var]) { 
        foreach($toprint as $var) {print $var;}
    }
}
?>

<?php endif; ?>

  </div> <!-- end encounters DIV -->
  </td>
 </tr>
</table>
<input type="button" class="genreport" value="<?php xl('Generate Report','e'); ?>" />

<hr/>

<span class="bold"><?php xl('Documents','e'); ?></span>:<br>
<ul>
<?php
// show available documents
$db = $GLOBALS['adodb']['db'];
$sql = "SELECT d.id, d.url, c.name FROM documents AS d " .
        "LEFT JOIN categories_to_documents AS ctd ON d.id=ctd.document_id " .
        "LEFT JOIN categories AS c ON c.id = ctd.category_id WHERE " .
        "d.foreign_id = " . $db->qstr($pid);
$result = $db->Execute($sql);
if ($db->ErrorMsg()) echo $db->ErrorMsg();
while ($result && !$result->EOF) {
    echo "<li class='bold'>";
    echo '<input type="checkbox" name="documents[]" value="' .
        $result->fields['id'] . '">';
    echo '&nbsp;&nbsp;<i>' . $result->fields['name'] . "</i>";
    echo '&nbsp;&nbsp;Name: <i>' . basename($result->fields['url']) . "</i>";
    echo '</li>';
    $result->MoveNext();	
}
?>
</ul>
</form>

<input type="button" class="genreport" value="<?php xl('Generate Report','e'); ?>" />

</div>  <!-- close patient_reports DIV -->
</body>

<script language="javascript">

// jQuery stuff to make the page a little easier to use
$(document).ready(function(){
    $(".genreport").click(function() { top.restoreSession(); $("#report_form").submit(); });
    $("#genfullreport").click(function() { location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>'; });
    //$("#printform").click(function() { PrintForm(); });
    $(".issuecheckbox").click(function() { issueClick(this); });

    // check/uncheck all Forms of an encounter
    $(".encounter").click(function() { SelectForms($(this)); });
});


// select/deselect the Forms related to the selected Encounter
// (it ain't pretty code folks)
var SelectForms = function (selectedEncounter) {
    if ($(selectedEncounter).attr("checked")) {
        $(selectedEncounter).parent().children().each(function(i, obj) {
            $(this).children().each(function(i, obj) {
                $(this).attr("checked", "checked");
            });
        });
    }
    else {
        $(selectedEncounter).parent().children().each(function(i, obj) {
            $(this).children().each(function(i, obj) {
                $(this).removeAttr("checked");
            });
        });
    }
}

// When an issue is checked, auto-check all the related encounters and forms
function issueClick(issue) {
    // do nothing when unchecked
    if (! $(issue).attr("checked")) return;

    $("#report_form :checkbox").each(function(i, obj) {
        if ($(issue).val().indexOf('/' + $(this).val() + '/') >= 0) {
            $(this).attr("checked", "checked");
        }
            
    });
}

</script>

</html>
