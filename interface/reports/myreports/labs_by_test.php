<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows past encounters with filtering and sorting.

require_once("../../globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/patient.inc");
include_once("$srcdir/wmt-v2/wmtstandard.inc");
include_once("$srcdir/wmt-v2/wmt.forms.php");

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

function PrintCodeSummary($thisCode, $grp1=0, $grp2=0, $thisDesc='No Description Provided') {
	echo "<tr>\n";
	echo "	<td class='label'>$thisCode</td>\n";
	echo "	<td class='text'>$thisDesc</td>\n";
	echo "	<td class='text'>Tests for Patients Age&nbsp;&lt;&nbsp;65:&nbsp;&nbsp;$grp1</td>\n";
	echo "	<td class='text'>Tests for Patients Age&nbsp;&gt;=&nbsp;65:&nbsp;&nbsp;$grp2</td>\n";
	echo "	<td class='text'>Total Tests:&nbsp;&nbsp;",($grp1 + $grp2),"</td>\n";
	echo "</tr>\n";
}

function PrintTotal($thisTitle, $grp1=0, $grp2=0) {
	echo "<tr>\n";
	echo "	<td class='bold' colspan='2'>$thisTitle</td>\n";
	echo "	<td class='bold'>Tests for Patients Age&nbsp;&lt;&nbsp;65:&nbsp;&nbsp;$grp1</td>\n";
	echo "	<td class='bold'>Tests for Patients Age&nbsp;&gt;=&nbsp;65:&nbsp;&nbsp;$grp2</td>\n";
	echo "	<td class='bold'>Total Tests:&nbsp;&nbsp;",($grp1 + $grp2),"</td>\n";
	echo "</tr>\n";
}

$last_year= mktime(0,0,0,date('m'),date('d'),date('Y')-1);
$last_month= mktime(0,0,0,date('m')-1,date('d'),date('Y'));
if(!isset($_POST['form_from_date'])) { $_POST['form_from_date'] = '' ; }
if(!isset($_POST['form_to_date'])) { $_POST['form_to_date'] = '' ; }
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d', $last_year));
$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_provider  = '';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
$selected_codes = '';
if(isset($_POST['tmp_test_codes'])) { $selected_codes = $_POST['tmp_test_codes']; }
// echo "Selected codes set to: $selected<br>\n";
if($selected_codes == '~ALL~') {
 	$rlist= sqlStatement("SELECT DISTINCT test_cd, test_text FROM ".
				"labcorp_codes WHERE UPPER(active)='Y' ORDER BY test_cd");
	$selected_codes = '';
	while($rrow = sqlFetchArray($rlist)) {
		if($selected_codes != '') { $selected_codes .= '+'; }	
		$selected_codes .= $rrow{'test_cd'};
	}
}
$selected_tests = '';
$tests = explode('+', $selected_codes);
unset($test_grp1);
unset($test_grp2);
unset($tot_grp1);
unset($tot_grp2);
unset($test_descriptions);
$test_grp1= array();
$test_grp2= array();
$tot_grp1= array();
$tot_grp1= array();
$test_descriptions= array();
foreach($tests as $chosen) {
	// echo "Searching for description for [$chosen] - <br>\n";
	$fres = sqlStatement("SELECT DISTINCT test_text, test_cd FROM labcorp_codes ".
			"WHERE test_cd=?", array($chosen));
	$frow = sqlFetchArray($fres);
	$test_grp1[ltrim($frow{'test_cd'},"0")] = 0;
	$test_grp2[ltrim($frow{'test_cd'},"0")] = 0;
	$tot_grp1[ltrim($frow{'test_cd'},"0")] = 0;
	$tot_grp1[ltrim($frow{'test_cd'},"0")] = 0;
	// echo "Found: (",$frow{'test_text'},") For Chosen: $chosen Code [",$frow{'test_cd'},"]<br>\n";
	$chosen = ltrim($chosen, "0");
	$test_descriptions[$chosen] = $frow{'test_text'};
	$selected_tests = AppendItem($selected_tests, $frow{'test_text'});
}
if(isset($_POST['tmp_test_codes'])) { 
	if($_POST['tmp_test_codes'] == '~ALL~') { $selected_tests = 'ALL'; }
}
// print_r($test_descriptions);
// echo "<br>\n";
// $query = "SELECT dr, pid, ord_dt, test_cd FROM ";

$query = "SELECT " .
	"procedure_result.procedure_type_id AS type, ".
	"procedure_order.provider_id AS dr, ".
	"procedure_order.patient_id AS pid, ".
	"procedure_order.date_ordered AS ord_dt, ".
	"labcorp_codes.test_cd AS test_cd, ".
	"labcorp_codes.result_cd AS result_cd ".
  "FROM procedure_result " .
  "LEFT JOIN procedure_report USING (procedure_report_id) ".
  "LEFT JOIN procedure_order USING (procedure_order_id)  ".
	// "(procedure_report.procedure_order_id=procedure_order.procedure_order_id) " .
  "RIGHT JOIN labcorp_codes ON ".
	"(labcorp_codes.result_cd=procedure_result.procedure_type_id) ".
	// "(procedure_result.procedure_type_id=labcorp_codes.result_cd) " .
  "WHERE ";
$cnt = 0;
foreach($tests as $chosen) {
	$cres = sqlStatement("SELECT test_cd, test_text, result_cd, result_text ".
		"FROM labcorp_codes WHERE test_cd=?",array($chosen));
	while($crow = sqlFetchArray($cres)) {
		if($cnt === 0) { $query .= '('; }
		if($cnt > 0) { $query .= ' OR '; }
		$query .= "procedure_result.procedure_type_id = '".
			ltrim($crow{'result_cd'}, "0")."'";
		$cnt++;
	}
}
if($cnt > 0) {
	$query .= ") ";
} else {
	$query .= "(procedure_result.procedure_type_id = '' OR procedure_result.procedure_type_id IS NULL) ";
}
$query .= "AND labcorp_codes.test_cd != '' ";
$query .= "AND labcorp_codes.test_cd IS NOT NULL ";
$cnt = 0;
foreach($tests as $chosen) {
	if($cnt === 0) { $query .= 'AND ('; }
	if($cnt > 0) { $query .= ' OR '; }
	$query .= "labcorp_codes.test_cd='$chosen' ";
	$cnt++;
}
if($cnt > 0) {
	$query .= ") ";
}

if ($form_to_date) {
  $query .= "AND date_ordered >= '$form_from_date' AND date_ordered <= '$form_to_date' ";
} else {
  $query .= "AND date_ordered >= '$form_from_date' AND date_ordered <= '$form_from_date' ";
}
if ($form_provider !== '') {
  $query .= "AND provider_id  = '$form_provider' ";
}
$query .= "AND patient_id > 0 ";
$query .= "GROUP BY dr, pid, ord_dt, test_cd";
// echo "Query: ",$query,"\n";

$lres=array();
if(isset($_GET['mode'])) { 
	set_time_limit(0);
	$lres = sqlStatement($query);
	$cnt = sqlNumRows($lres);
	// echo "<br/></br>Row Count: $cnt<br/><br/>\n";
}
?>
<html>
<head>
<title><?php xl('Lab Result Summary','e'); ?></title>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

</style>

<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>

<script LANGUAGE="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function refreshme() {
  document.forms[0].submit();
 }

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Lab Tests by Physician','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='labs_by_test.php?mode=search'>

<div id="report_parameters">
<table>
<tr>
  <td width='800px'>
    <div style='float:left'>
      <table class='text'>
        <tr>
          <td class='label'><?php xl('Provider','e'); ?>: </td>
          <td><?php
               // Build a drop-down list of providers.
              $query = "SELECT id, username, lname, fname FROM users WHERE " .
								"authorized=1 AND active='1' AND username!='' AND " .
								"(UPPER(specialty) LIKE '%PROVIDER%' OR UPPER(specialty) ".
								"LIKE '%SUPERVISOR%') ".
								"ORDER BY lname, fname";
              $ures = sqlStatement($query);

              echo "   <select name='form_provider'>\n";
              echo "    <option value=''>-- " . xl('All') . " --\n";

              while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='$provid'";
                if ($provid == $form_provider) echo " selected";
                echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
              }
              echo "   </select>\n";
              ?></td>
           <td class='label'><?php xl('From','e'); ?>: </td>
           <td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='vertical-align: bottom; cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
           <td class='label'><?php xl('To','e'); ?>: </td>
           <td>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='vertical-align: bottom; cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
        </tr>
				<tr>
					<td class="label"><?php xl('Choose Tests','e'); ?>:</td>
					<td><select name="tmp_sel_tests" id="tmp_sel_tests" onchange="UpdateSelDescription('tmp_sel_tests','tmp_test_codes','tmp_test_list');">
					<?php
  				$rlist= sqlStatement("SELECT DISTINCT test_cd, test_text FROM ".
						"labcorp_codes WHERE UPPER(active)='Y' ORDER BY test_cd");
  				echo "<option value='' selected='selected'>",xl('Choose Another','e'),"</option>";
					echo "<option value='~ra~'>Remove All</option>\n";
					echo "<option value='~ALL~'>Select All</option>\n";
  				while ($rrow= sqlFetchArray($rlist)) {
    				echo "<option value='" . $rrow['test_cd'] . "'";
						// Possibly check the hidden field for the value, bold selected ones
    				// if(is_array($thisArray)) {
      				// if(in_array($rrow['option_id'],$thisArray)) echo " selected='selected'";
    				// }
    				echo ">" .$rrow['test_cd'].' - '.$rrow['test_text'];
    				echo "</option>";
  				}
					?>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="5"><span class='label' id='tmp_test_list'><?php echo $selected_tests; ?></span></td>
				</tr>
      </table>

    </div>
  </td>
  <td style="vertical-align: middle; text-align: left; height: 100%;">
    <table style='border-left:1px solid; width:100%; height:100%' >
      <tr>
        <td>
          <div style='margin-left:15px'>
            <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
					<span><?php xl('Submit','e'); ?></span></a>

            <?php if(isset($_GET['mode']) ) { ?>
            <a href='#' class='css_button' onclick='window.print()'>
						<span><?php xl('Print','e'); ?></span></a>
            <?php } ?>
          </div>
        </td>
      </tr>
     </table>
  </td>
 </tr>
</table>

</div> <!-- end report_parameters -->

<?php
 if (isset($_GET['mode'])) {
?>
<div id="report_results">
<table>

<?php
if ($lres) {
  $lastdoc = '';
	$lastcode = '';
	$lastcode;
	$doc_tot1 = $doc_tot2 = 0;
	$rpt_tot1 = $tpr_tot2 = 0;
  while ($row = sqlFetchArray($lres)) {
		  echo "<tr><td colspan='4'>";
		  print_r($row);
		  echo "</td></tr>\n";
		 // echo "<tr><td colspan='4'>";
		 // echo "Last Doc ($lastdoc)  Last Code [$lastcode] -> This code <",$row{'procedure_type_id'},">";
		 // echo "</td></tr>\n";

		if(($row{'dr'} !== $lastdoc)) {
			if($lastcode != '') {
				// PrintCodeSummary($lastcode, $test_grp1[$lastcode], $test_grp2[$lastcode], $test_descriptions[$lastcode]);	
				$test_grp1[$lastcode] = 0;
				$test_grp2[$lastcode] = 0;
			}
			if($lastdoc !== '') {
				// Possibly print a total test row here, or a visual break div
				// PrintTotal('TOTAL TESTS FOR DOCTOR '.$lastdoc, $doc_tot1, $doc_tot2);
				$doc_tot1 = $doc_tot2 = 0;
				$docname=UserNameFromID($lastdoc);
				if($docname == '') { $docname = '** No Assigned Provider **'; }
				// echo "<tr><td colspan='5'>&nbsp;</td></tr>\n";
			}
			$docname = UserNameFromID($row{'dr'});
			if($docname == '') { $docname = 'No Assigned Provider'; }
			// echo "<tr><td class='label' colspan='5'>$docname</td></td>\n";

			$lastcode = '';
		}
		$lastdoc= $row{'dr'};


		if(ltrim($row{'test_cd'}, "0") != $lastcode) {
			if($lastcode!= '') {
				// PrintCodeSummary($lastcode, $test_grp1[$lastcode], $test_grp2[$lastcode], $test_descriptions[$lastcode]);	
				$test_grp1[$lastcode] = 0;
				$test_grp2[$lastcode] = 0;
			}
		}
		$lastcode = ltrim($row{'test_cd'}, "0");
		$_age = 1;
		$pres = sqlStatement("SELECT DOB FROM patient_data WHERE pid=?",
			array($row{'pid'}));
		$prow = sqlFetchArray($pres);
		if($prow{'DOB'}) { $_age = getPatientAge($prow{'DOB'}); }
		if($_age >= 65) {
			$test_grp2[$lastcode]++;
			$tot_grp2[$lastcode]++;
			$doc_tot2++;
			$rpt_tot2++;
		} else {
			$test_grp1[$lastcode]++;
			$tot_grp1[$lastcode]++;
			$doc_tot1++;
			$rpt_tot1++;
		}	

	}
	if($lastdoc != '') {
		if($lastcode != '') {
			// PrintCodeSummary($lastcode, $test_grp1[$lastcode], $test_grp2[$lastcode], $test_descriptions[$lastcode]);	
		}
		// PrintTotal('TOTAL TESTS FOR DOCTOR '.$lastdoc, $doc_tot1, $doc_tot2);
	}	
	// Print a summary for all selected doctors
	echo "<tr><td colspan='5'>&nbsp;</td></tr>\n";
	echo "<tr><td class='bold' colspan='5'>Test Totals for ALL Selected Doctors</td></tr>\n";
	$rpt_tot1 = $rpt_tot2 = 0;
	foreach($tests as $chosen) {
		$chosen = ltrim($chosen, "0");
		PrintCodeSummary($chosen, $tot_grp1[$chosen], $tot_grp2[$chosen], $test_descriptions[$chosen]);	
		$rpt_tot1 = $rpt_tot1 + $tot_grp1[$chosen];
		$rpt_tot2 = $rpt_tot2 + $tot_grp2[$chosen];
	}
	PrintTotal('TOTAL TESTS FOR REPORT', $rpt_tot1, $rpt_tot2);
}
?>
</table>
</div>  <!-- end encresults -->
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='tmp_test_codes' id='tmp_test_codes' value="<?php echo $selected_codes; ?>"/>

</form>
</body>

<script type="text/javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>
</script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmt.forms.js"></script>

</html>
