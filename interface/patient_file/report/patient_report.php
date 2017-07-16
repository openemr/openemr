<?php

require_once("../../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/patient.inc");

// get various authorization levels
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_coding_a = acl_check('encounters', 'coding_a');
$auth_coding   = acl_check('encounters', 'coding');
$auth_relaxed  = acl_check('encounters', 'relaxed');
$auth_med      = acl_check('patients'  , 'med');
$auth_demo     = acl_check('patients'  , 'demo');

$cmsportal = false;
if ($GLOBALS['gbl_portal_cms_enable']) {
  $ptdata = getPatientData($pid, 'cmsportal_login');
  $cmsportal = $ptdata['cmsportal_login'] !== '';
}
$rpt_opt = array(
	'gen' => array (
		'demographics' => array('disp' => 'Demographics', 'auth' => true, 'attr' => 'checked'),
		'history' => array('disp' => 'History', 'auth' => acl_check('patients', 'med'), 'attr' => ''),
//		'employer' => array('disp' => 'Employer', 'auth' => true, 'attr' => ''),
		'insurance' => array('disp' => 'Insurance', 'auth' => true, 'attr' => ''),
		'billing' => array('disp' => 'Billing', 'auth' => true, 'attr' => ($GLOBALS['simplified_demographics'] ? '' : 'checked')),
		'immunizations' => array('disp' => 'Immunizations', 'auth' => true, 'attr' => ''),
//		'allergies' => array('disp' => 'Allergies', 'auth' => true, 'attr' => ''),
//		'medications' => array('disp' => 'Medications', 'auth' => true, 'attr' => ''),
//		'medical_problems' => array('disp' => 'Medical Problems', 'auth' => true, 'attr' => ''),
		'notes' => array('disp' => 'Patient Notes', 'auth' => true, 'attr' => ''),
		'transactions' => array('disp' => 'Transactions', 'auth' => true, 'attr' => ''),
		'batchcom' => array('disp' => 'Communications', 'auth' => true, 'attr' => ''),
		'recurring_days' => array('disp' => 'Recurrent Appointments', 'auth' => true, 'attr' => ''),
	),
	'submits' => array(
		'1' => array('disp' => 'Generate Report', 'auth' => true, 'class' => 'genreport'),
		'2' => array('disp' => 'Download PDF', 'auth' => true, 'class' => 'genpdfrep'),
		'3' => array('disp' => 'Send to Portal', 'auth' => $cmsportal, 'class' => 'genreport'),		
	),
);
$html_gen = '';
foreach($rpt_opt['gen'] as $opt_key => $opt) {
	if ($opt['auth']) {
		$html_gen .= sprintf('<div class="col-sm-2"><input type="checkbox" name="include_%s" id="include_%s" value="%s" %s>
			<label for="include_%s">%s</label></div>',
			$opt_key, $opt_key, $opt_key, $opt['attr'], $opt_key, xl($opt['disp']));
	}
}
$html_gen = sprintf('<div class="row">%s</div>', $html_gen);
$html_sub = '';
foreach($rpt_opt['submits'] as $opt_key => $opt) {
	if ($opt['auth']) {
		$html_gen .= sprintf('<div class="col-sm-2"><input type="button" class="btn btn-xs %s" value="%s" /></div>',
			$opt['class'], xl($opt['disp']));
	}
}
$html_sub = sprintf('<div class="row">%s</div>', $html_sub);
// Documents list
$html_docs = '';
$sql = "SELECT d.id, d.docdate, d.url, c.name, c.aco_spec 
		FROM documents AS d LEFT JOIN categories_to_documents AS ctd ON d.id=ctd.document_id 
		LEFT JOIN categories AS c ON c.id = ctd.category_id 
		WHERE d.foreign_id = ?
		ORDER BY d.docdate DESC";
$rs = sqlStatement($sql, array($pid));
while ($rs_doc = sqlFetchArray($rs)) {
	if ((!$rs_doc['aco_spec']) && (!acl_check_aco_spec($rs_doc['aco_spec']))) { continue; }
	// Two col display
	$html_docs .= sprintf('<div class="col-md-6">
		<div class="col-md-1"><input type="checkbox" name="documents[]" value="%s"></div>
		<div class="col-md-3">%s</div>
		<div class="col-md-3">%s</div>
		<div class="col-md-5">%s</div>
		</div>',
		$rs_doc['id'], $rs_doc['docdate'], xl_document_category($rs_doc['name']), basename($rs_doc['url']));
}
$html_docs = sprintf('<div class="row">%s</div>', $html_docs);
?>
<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>

<body class="body_top">

<div class="container">
  <h4 class="title"><?php echo xlt('Patient Reports'); ?></h4>
  <ul class="nav nav-tabs">
    <li class="active" data-target="#dne_report" data-toggle="tab">
		<a href="#"><?php printf('%s %s %s', xl("Demographics"), xl("and"), xl("Encounters")); ?></a>
	</li>
    <li data-target="#ccr_report" data-toggle="tab" <?php echo ($GLOBALS['activate_ccr_ccd_report'] ? "":'class="disabled"') ?> {>
		<a href="#"><?php echo xl("Continuity of Care"); ?></a>
	</li>
  </ul>

<div class="tab-content" id="patient_reports container"> <!-- large outer DIV -->

<div id="dne_report" class="tab-pane active">
<form name='report_form' id="report_form" method='post' action='custom_report.php'>
<input type='hidden' name='pdf' value='0'>
<div class="panel-group">
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo xlt('Common information'); ?>
<!--
<a class="link_submit" href="full_report.php" onclick="top.restoreSession()">
[<?php xl('View Comprehensive Patient Report','e'); ?>]</a>
-->
<a class="link_submit" href="#" onclick="return checkAll(true)"><?php xl('Check All','e'); ?></a>
|
<a class="link_submit" href="#" onclick="return checkAll(false)"><?php xl('Clear All','e'); ?></a>
	</div>
    <div class="panel-body"><?php echo $html_gen; ?></div>
	<div class="panel-footer"><?php echo $html_sub; ?></div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php xl('Issues','e'); ?></div>
    <div class="panel-body issues">
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
	</div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php xl('Encounters &amp; Forms','e'); ?></div>
    <div class="panel-body encounters">
<?php if (!($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)): ?>
(Encounters not authorized)
<?php else: ?>

<?php

$isfirst = 1;
$res = sqlStatement("SELECT fe.encounter, fm.form_id, fm.form_name, fm.formdir, fm.date AS fdate, fe.date, fe.reason 
                    FROM form_encounter fe INNER JOIN forms fm ON fe.encounter = fm.encounter AND fe.pid = fm.pid
					WHERE fe.pid=? AND fm.deleted=0 
                    ORDER BY fe.date DESC, fdate ASC", array($pid));
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
            // The default encoding for this mb_substr() call is set near top of globals.php
            $result['reason'] = mb_substr($result['reason'], 0, $maxReasonLength) . " ... ";
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
                                                ">" . xl_form_title($result{"form_name"}) . "<br>\n");
    }
}
foreach($registry_form_name as $var) {
    if ($toprint = $html_strings[$var]) {
        foreach($toprint as $var) {print $var;}
    }
}
?>

<?php endif; ?>
	</div>
	</div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo xl('Procedures'); ?></div>
    <div class="panel-body">
<table border="0" cellpadding="0" cellspacing="0" >
 <tr>
  <td class='bold'><?php echo xlt('Procedures'); ?>&nbsp;&nbsp;</td>
  <td class='text'><?php echo xlt('Order Date'); ?>&nbsp;&nbsp;</td>
  <td class='text'><?php echo xlt('Encounter Date'); ?>&nbsp;&nbsp;</td>
  <td class='text'><?php echo xlt('Order Descriptions'); ?></td>
 </tr>
<?php
$res = sqlStatement("SELECT po.procedure_order_id, po.date_ordered, fe.date " .
  "FROM procedure_order AS po " .
  "LEFT JOIN forms AS f ON f.pid = po.patient_id AND f.formdir = 'procedure_order' AND " .
  "f.form_id = po.procedure_order_id AND f.deleted = 0 " .
  "LEFT JOIN form_encounter AS fe ON fe.pid = f.pid AND fe.encounter = f.encounter " .
  "WHERE po.patient_id = ? " .
  "ORDER BY po.date_ordered DESC, po.procedure_order_id DESC",
  array($pid));
while($row = sqlFetchArray($res)) {
  $poid = $row['procedure_order_id'];
  echo " <tr>\n";
  echo "  <td align='center' class='text'>" .
       "<input type='checkbox' name='procedures[]' value='$poid' />&nbsp;&nbsp;</td>\n";
  echo "  <td class='text'>" . oeFormatShortDate($row['date_ordered']) . "&nbsp;&nbsp;</td>\n";
  echo "  <td class='text'>" . str_replace(" 00:00:00", "", oeFormatShortDate($row['date'])) . "&nbsp;&nbsp;</td>\n";
  echo "  <td class='text'>";
  $opres = sqlStatement("SELECT procedure_code, procedure_name FROM procedure_order_code " .
    "WHERE procedure_order_id = ? ORDER BY procedure_order_seq",
    array($poid));
  $pcodes = '';
  while($oprow = sqlFetchArray($opres)) {
    $pcodes .= '<code>'.(empty($oprow['procedure_name']) ? $oprow['procedure_code'] : $oprow['procedure_name']).'</code>,&nbsp;';
  }
  echo "$pcodes</td>\n";
  echo " </tr>\n";
}
?>
</table>
	</div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading"><?php echo xl('Documents','e'); ?></div>
    <div class="panel-body"><?php echo $html_docs ?></div>
  </div>
<?php /* Template for future additons
  <div class="panel panel-default">
    <div class="panel-heading">Panel Heading</div>
    <div class="panel-body">Panel Content</div>
  </div>
*/ ?>
</div>
</div>
</div>

<?php if ( $GLOBALS['activate_ccr_ccd_report'] ) { // show CCR/CCD reporting options ?>
<div id="ccr_report" class="tab-pane"></div>
<?php } // end CCR/CCD reporting options ?>

</form>

</div>

</body>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<!-- include jQuery support -->
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-min-3-1-1/index.js"></script>

<script language="javascript">
function checkAll(check) {
 var f = document.forms['report_form'];
 for (var i = 0; i < f.elements.length; ++i) {
  if (f.elements[i].type == 'checkbox') f.elements[i].checked = check;
 }
 return false;
}

// jQuery stuff to make the page a little easier to use
$(document).ready(function(){
    $(".genreport").click(function() { top.restoreSession(); document.report_form.pdf.value = 0; $("#report_form").submit(); });
    $(".genpdfrep").click(function() { top.restoreSession(); document.report_form.pdf.value = 1; $("#report_form").submit(); });
    $(".genportal").click(function() { top.restoreSession(); document.report_form.pdf.value = 2; $("#report_form").submit(); });
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
$("#ccr_report").load("patient_cc.php");
$('#sel_rep').click(function() {
    if ($(this).find('.btn-primary').size()>0) {
    	$(this).find('.btn').toggleClass('btn-primary');
    }
    $(this).find('.btn').toggleClass('btn-default');
});

</script>

</html>
