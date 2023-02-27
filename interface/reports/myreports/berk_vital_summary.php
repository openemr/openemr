<?php
// Copyright (C) 2015 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows specific lab results with particulars as needed for
// accrediting..

require_once("../../globals.php");
require_once("$srcdir/sql.inc");
require_once("$srcdir/patient.inc");
include_once("$srcdir/wmt-v2/wmtstandard.inc");

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

class DoctorTotal {
	public $A1c_lt_7;
	public $A1c_lt_8;
	public $A1c_lt_9;
	public $A1c_gt_9;
	public $A1c_tests;
	public $A1c_pats;
	public $A1c_pat_total;
	public $A1c_pat_tests;

	public $HDL_lt_40;
	public $HDL_lt_50;
	public $HDL_lt_60;
	public $HDL_gt_60;
	public $HDL_tests;
	public $HDL_pats;
	public $HDL_pat_total;
	public $HDL_pat_tests;

	public $LDL_lt_100;
	public $LDL_lt_130;
	public $LDL_gt_130;
	public $LDL_tests;
	public $LDL_pats;
	public $LDL_pat_total;
	public $LDL_pat_tests;

	public $Tri_lt_250;
	public $Tri_pat_lt_250;
	public $Tri_tests;
	public $Tri_pats;
	public $Tri_pat_used;

	public $D_tests;
	public $D_pats;
	public $D_pat_used;

	public $Bp_lt_130;
	public $Bp_gt_140;
	public $Bp_tests;
	public $Bp_pats;

	public $Tobacco_use;

	public $Urine_tests;
	public $Urine_pats;
	public $Urine_pat_used;

	public $BMI_follow_up;

	public $Foot_exam;

	public $Total_pats;
	public $Total_pats_lt_18;
	public $Total_pats_gt_17;
	public $Total_pats_gt_75;

	// Construct - define the doctor totals
	public function __construct() {
		$this->A1c_lt_7 = 0;
		$this->A1c_lt_8 = 0;
		$this->A1c_lt_9 = 0;
		$this->A1c_gt_9 = 0;
		$this->A1c_tests = 0;
		$this->A1c_pats = 0;
		$this->A1c_pat_total = 0;
		$this->A1c_pat_tests = 0;

		$this->HDL_lt_40 = 0;
		$this->HDL_lt_50 = 0;
		$this->HDL_lt_60 = 0;
		$this->HDL_gt_60 = 0;
		$this->HDL_tests = 0;
		$this->HDL_pats = 0;
		$this->HDL_pat_total = 0;
		$this->HDL_pat_tests = 0;

		$this->LDL_lt_100 = 0;
		$this->LDL_lt_130 = 0;
		$this->LDL_gt_130 = 0;
		$this->LDL_tests = 0;
		$this->LDL_pats = 0;
		$this->LDL_pat_total = 0;
		$this->LDL_pat_tests = 0;

		$this->Tri_lt_250 = 0;
		$this->Tri_pat_lt_250 = 0;
		$this->Tri_tests = 0;
		$this->Tri_pats = 0;
		$this->Tri_pat_used = 0;

		$this->D_tests = 0;
		$this->D_pats = 0;
		$this->D_pat_used = 0;

		$this->Bp_lt_130 = 0;
		$this->Bp_gt_140 = 0;
		$this->Bp_tests = 0;
		$this->Bp_pats = 0;

		$this->Tobacco_use = 0;

		$this->Urine_tests = 0;
		$this->Urine_pats = 0;
		$this->Urine_pat_used = 0;

		$this->BMI_follow_up = 0;

		$this->Foot_exam = 0;

		$this->Total_pats = 0;
		$this->Total_pats_lt_18 = 0;
		$this->Total_pats_gt_17 = 0;
		$this->Total_pats_gt_75 = 0;
	}

	public function ChangePat() {
		$_avg = 0;
		if($this->A1c_pat_total && $this->A1c_pat_tests) {
			$this->A1c_pats++;
			$_avg = ($this->A1c_pat_total / $this->A1c_pat_tests);
			if($_avg < 7) {
				$this->A1c_lt_7++;
			} else if($_avg >= 7 && $_avg < 8) {
				$this->A1c_lt_8++;
			} else if($_avg >= 8 && $_avg < 9) {
				$this->A1c_lt_9++;
			} else {
				$this->A1c_gt_9++;
			}
		} 
		$this->A1c_pat_total = 0;
		$this->A1c_pat_tests = 0;

		$_avg = 0;
		if($this->HDL_pat_total && $this->HDL_pat_tests) {
			$this->HDL_pats++;
			$_avg = ($this->HDL_pat_total / $this->HDL_pat_tests);
			if($_avg <= 40) {
				$this->HDL_lt_40++;
			} else if($_avg > 40 && $_avg <= 50) {
				$this->HDL_lt_50++;
			} else if($_avg > 50 && $_avg <= 60) {
				$this->HDL_lt_60++;
			} else if($_avg > 60) {
				$this->HDL_gt_60++;
			}
		}
		$this->HDL_pat_total = 0;
		$this->HDL_pat_tests = 0;

		$_avg = 0;
		if($this->LDL_pat_total && $this->LDL_pat_tests) {
			$this->LDL_pats++;
			$_avg = ($this->LDL_pat_total / $this->LDL_pat_tests);
			if($_avg <= 100) {
				$this->LDL_lt_100++;
			} else if($_avg > 100 && $_avg < 130) {
				$this->LDL_lt_130++;
			} else if($_avg >= 130) {
				$this->LDL_gt_130++;
			}
		}
		$this->LDL_pat_total= 0;
		$this->LDL_pat_tests= 0;

		$this->Tri_pat_used= 0;
		$this->Tri_pat_lt_250 = 0;

		$this->D_pat_used= 0;
		$this->Urine_pat_used= 0;
	}

	public function PrintSummary($thisDoc='') {
		echo "<tr><td colspan='3'><b>Doctor Totals For $thisDoc</b></td></tr>\n";
		echo "<tr><td><b>Hemoglobin A1c</b></td><td>Patients w/Average Result Less Than 7% :</td><td style='text-align: right'>$this->A1c_lt_7</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Average Result of 7.0% to 7.99% :</td><td style='text-align: right'>$this->A1c_lt_8</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Average Result of 8.0% to 8.99% :</td><td style='text-align: right'>$this->A1c_lt_9</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Average Result Greater Than 9% :</td><td style='text-align: right'>$this->A1c_gt_9</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Total Patients Tested :</td><td style='text-align: right'>$this->A1c_pats</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Total Tests w/Results :</td><td style='text-align: right'>$this->A1c_tests</td></tr>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
		echo "<tr><td><b>HDL Cholesterol</b></td><td>Patients w/Average Result Less Than/Equal to 40 :</td><td style='text-align: right'>$this->HDL_lt_40</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Average Result of 41 to 50 :</td><td style='text-align: right'>$this->HDL_lt_50</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Average Result of 51 to 60 :</td><td style='text-align: right'>$this->HDL_lt_60</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Average Result of 60 or Greater :</td><td style='text-align: right'>$this->HDL_gt_60</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Total Patients Tested :</td><td style='text-align: right'>$this->HDL_pats</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Total Tests w/Results :</td><td style='text-align: right'>$this->HDL_tests</td></td>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
		echo "<tr><td><b>LDL Cholesterol</b></td><td>Patients w/Result Less Than/Equal to 100 :</td><td style='text-align: right'>$this->LDL_lt_100</td></tr>\n";
		echo "<tr><td><b></b></td><td>Patients w/Result From 101 to 129 :</td><td style='text-align: right'>$this->LDL_lt_130</td></tr>\n";
		echo "<tr><td><b></b></td><td>Patients w/Result Greater Than/Equal to 130 :</td><td style='text-align: right'>$this->LDL_gt_130</td></tr>\n";
		echo "<tr><td><b>&nbsp;</b></td><td>Total Patients Tested :</td><td style='text-align: right'>$this->LDL_pats</td></tr>\n";
		echo "<tr><td><b>&nbsp;</b></td><td>Total Tests w/Results :</td><td style='text-align: right'>$this->LDL_tests</td></tr>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
		echo "<tr><td><b>Triglyceride</b></td><td>Patients w/Result Less Than 250 :</td><td style='text-align: right'>$this->Tri_lt_250</td></tr>\n";
		echo "<tr><td><b>&nbsp;</b></td><td>Total Patients Tested :</td><td style='text-align: right'>$this->Tri_pats</td></tr>\n";
		echo "<tr><td><b>&nbsp;</b></td><td>Total Tests w/Results :</td><td style='text-align: right'>$this->Tri_tests</td></tr>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
		echo "<tr><td><b>Vitamin D</b></td><td>Patients Tested :</td><td style='text-align: right'>$this->D_pats</td></tr>\n";
		echo "<tr><td><b>&nbsp;</b></td><td>Tests :</td><td style='text-align: right'>$this->D_tests</td></tr>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
		echo "<tr><td><b>Urine Protein Screen</b></td><td>Patients Tested :</td><td style='text-align: right'>$this->Urine_pats</td></tr>\n";
		echo "<tr><td><b>&nbsp;</b></td><td>Tests :</td><td style='text-align: right'>$this->Urine_tests</td></tr>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
		echo "<tr><td><b>Blood Pressure</b></td><td>Patients w/Average Result Less Than 130/80 :</td><td style='text-align: right'>$this->Bp_lt_130</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Patients w/Average Result Greater Than 140/90 :</td><td style='text-align: right'>$this->Bp_gt_140</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Total Patients Tested :</td><td style='text-align: right'>$this->Bp_pats</td></tr>\n";
		echo "<tr><td>&nbsp;</td><td>Total Tests :</td><td style='text-align: right'>$this->Bp_tests</td></tr>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
		echo "<tr><td><b>Tobacco Use</b></td><td>Screening and cessation intervention:</td><td style='text-align: right'>$this->Tobacco_use</td></tr>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
		echo "<tr><td><b>BMI Follow Up</b></td><td>Patients w/BMI Outside Normal AND a Follow Up Plan :</td><td style='text-align: right'>$this->BMI_follow_up</td></tr>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
		echo "<tr><td><b>Foot Exam</b></td><td>Number of Patients w/Diabetes who had a foot exam:</td><td style='text-align: right'>$this->Foot_exam</td></tr>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
		echo "<tr><td><b>TOTAL PATENTS</b></td><td>Number of Patients Aged 18 - 75:</td><td style='text-align: right'>$this->Total_pats_gt_17</td></tr>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
		echo "<tr><td colspan='3'>&nbsp;</td></tr>\n";
	}
}

$last_year = mktime(0,0,0,date('m'),date('d'),date('Y')-1);
$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
if(!isset($_POST['form_from_date'])) $_POST['form_from_date'] = '' ;
if(!isset($_POST['form_to_date'])) $_POST['form_to_date'] = '' ;
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d', $last_year));
$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_provider  = '';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];

$query = "SELECT form_vitals.*, ".
		"patient_data.DOB, patient_data.providerID, ".
		"forms.encounter, forms.deleted,".
		"form_encounter.date, form_encounter.provider_id ".
		"FROM form_vitals LEFT JOIN forms ON ".
		"(form_vitals.id = forms.form_id AND forms.formdir = 'vitals') ".
		"LEFT JOIN form_encounter ON ".
		"(forms.encounter = form_encounter.encounter) LEFT JOIN ".
		"patient_data ON (form_vitals.pid = patient_data.pid) WHERE ".
		"forms.deleted='0' ";
if ($form_to_date) {
  $query .= "AND form_encounter.date >= '$form_from_date 00:00:00' AND form_encounter.date <= '$form_to_date 23:59:59' ";
} else {
  $query .= "AND form_encounter.date >= '$form_from_date 00:00:00' AND form_encounter.date <= '$form_from_date 23:59:59' ";
}
if ($form_provider) {
  $query .= "AND patient_data.providerID = '$form_provider' ";
}
$query .= "ORDER BY patient_data.providerID, forms.pid";
// echo "Query: ",$query,"\n";

$lres=array();
if(isset($_GET['mode'])) { 
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

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Summary of Specified Tests','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='berk_vital_summary.php?mode=search'>

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
								"(specialty LIKE '%Provid%' OR specialty LIKE '%Super%') ".
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
         </tr>
         <tr>
           <td class='label'><?php xl('From','e'); ?>: </td>
           <td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
           <td class='label'><?php xl('To','e'); ?>: </td>
           <td>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
         </tr>
       </table>

    </div>
  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
      <tr>
        <td>
          <div style='margin-left:15px'>
            <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php xl('Submit','e'); ?>
					</span>
					</a>

            <?php if(isset($_GET['mode']) ) { ?>
            <a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php xl('Print','e'); ?>
						</span>
					</a>
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

function GetFootTotal($doc, $from_date, $to_date='') {
	if($to_date == '') $to_date = $from_date;
	$from_date .= ' 00:00:00';
	$to_date .= ' 23:59:59';
	$foot_pats = 0;
	$sql = "SELECT forms.*, form_encounter.provider_id, patient_data.DOB, ".
		"patient_data.providerID, form_encounter.date AS dos FROM forms ".
		"LEFT JOIN form_encounter USING (encounter) ".
		"LEFT JOIN patient_data ON (forms.pid = patient_data.pid) ".
		"WHERE forms.formdir = 'wu_foot_exam' AND forms.deleted = 0 ".
		"AND patient_data.providerID = ? ".
 	  "AND form_encounter.date >= ? AND form_encounter.date <= ? " .
		"ORDER BY forms.pid ";
	$vres= sqlStatement($sql, array($doc, $from_date, $to_date));
	if($vres) {
		$thispat = $thisDOB = $thisdate = '';
		while($enc = sqlFetchArray($vres)) {
			if($enc{'pid'} != $thispat && $thispat != '') {
				if($thisdate == '' || $thisdate == 0) $thisdate = date('Y-m-d');
				$pat_age = getPatientAge($thisDOB, $thisdate);
				if($pat_age > 17 && $pat_age < 76) $foot_pats++;
			}

			$thispat = $enc{'pid'};
			$thisdate = substr($enc{'dos'},0,10);
			$thisDOB = $enc{'DOB'};
		}
	}
	if($enc{'pid'} != $thispat && $thispat != '') {
		if($thisdate == '' || $thisdate == 0) $thisdate = date('Y-m-d');
		$pat_age = getPatientAge($thisDOB, $thisdate);
		if($pat_age > 17 && $pat_age < 76) $foot_pats++;
	}
	return $foot_pats;
}

function GetPatientTotal($doc, $from_date, $to_date='') {
	if($to_date == '') $to_date = $from_date;
	$from_date .= ' 00:00:00';
	$to_date .= ' 23:59:59';
	$lt_18 = $gt_17 = $gt_75 = $ttl = $smk_pats = 0;
	$smk = "SELECT tobacco, date FROM history_data WHERE pid = ? AND ".
 	  "history_data.date >= ? AND history_data.date <= ? AND ".
		"tobacco IS NOT NULL AND tobacco != '' AND tobacco != '|||'";

	// This pass is for gathering the total patients (from encounters)
	$sql = "SELECT form_encounter.*, patient_data.DOB, patient_data.providerID ".
	 	"FROM form_encounter LEFT JOIN patient_data USING (pid) WHERE ".
		// "(form_encounter.pid = patient_data.pid) ".
		"form_encounter.provider_id = ? AND ".
 	  "form_encounter.date >= ? AND form_encounter.date <= ? ".
		"ORDER BY form_encounter.pid";
	$vres= sqlStatement($sql, array($doc, $from_date, $to_date));
	if($vres) {
		$thispat = $thisDOB = $thisdate = '';
		while($enc = sqlFetchArray($vres)) {
			if($enc{'pid'} != $thispat && $thispat != '') {
				if($thisdate == '' || $thisdate == 0) $thisdate = date('Y-m-d');
				$pat_age = getPatientAge($thisDOB, $thisdate);
				$res = sqlQuery($smk, array($thispat, $from_date, $to_date));
				if(!isset($res{'tobacco'})) $res{'tobacco'} = '';
				if($res{'tobacco'} != '') $smk_pats++;
				if($pat_age < 18) {
					$lt_18 ++;
				} else if($pat_age > 17 && $pat_age < 76) {
					$gt_17++;
				} else if($pat_age > 75) {
					$gt_75++;
				}
				$ttl++;
			}

			$thispat = $enc{'pid'};
			$thisdate = substr($enc{'date'},0,10);
			$thisDOB = $enc{'DOB'};
		}
		if($enc{'pid'} != $thispat && $thispat != '') {
			if($thisdate == '' || $thisdate == 0) $thisdate = date('Y-m-d');
			$pat_age = getPatientAge($thisDOB, $thisdate);
			$res = sqlQuery($smk, array($thispat, $from_date, $to_date));
			if(!isset($res{'tobacco'})) $res{'tobacco'} = '';
			if($res{'tobacco'} != '') $smk_pats++;
			if($pat_age < 18) {
				$lt_18 ++;
			} else if($pat_age > 17 && $pat_age < 76) {
				$gt_17++;
			} else if($pat_age > 75) {
				$gt_75++;
			}
			$ttl++;
		}
	}
	return( array('lt_18' => $lt_18, 'gt_17' => $gt_17, 'gt_75' => $gt_75, 'ttl' => $ttl, 'smk' => $smk_pats));
}

function GetBPResults($doc, $from_date, $to_date='') {
	if($to_date == '') $to_date = $from_date;
	$from_date .= ' 00:00:00';
	$to_date .= ' 23:59:59';
	$over = $under = $pats = $bmi_pat_cnt = 0;
	// echo "<tr><td>Getting Vitals For $doc</td></tr>\n";
	$sql = "SELECT form_vitals.id, form_vitals.bps, form_vitals.bpd, ".
		"form_vitals.BMI, form_vitals.pid, patient_data.DOB, ".
		"patient_data.providerID, ".
		"forms.encounter, forms.deleted, form_encounter.date, ".
		"form_encounter.provider_id FROM form_vitals LEFT JOIN forms ON ".
		"(form_vitals.id = forms.form_id AND forms.formdir = 'vitals') ".
		"LEFT JOIN form_encounter ON ".
		"(forms.encounter = form_encounter.encounter) LEFT JOIN ".
		"patient_data ON (form_vitals.pid = patient_data.pid) WHERE ".
		"form_encounter.provider_id=? AND forms.deleted='0' ".
 	  "AND form_encounter.date >= ? AND form_encounter.date <= ? " .
		"ORDER BY form_vitals.pid ";
	$vres= sqlStatement($sql, array($doc, $from_date, $to_date));
	if($vres) {
		$plan_query = "SELECT begdate, enddate, diagnosis, comments FROM lists ".
			"LEFT JOIN issue_encounter ON list_id=id AND encounter=? WHERE ".
			"type='medical_problem' AND lists.pid=? AND diagnosis LIKE '%ICD%' ".
			"AND (comments != '' AND comments IS NOT NULL)";
		$thispat = $thisDOB = '';
		$vital_cnt = $bps_tot = $bpd_tot = 0;
		$over = $under = $tests = 0;
		$last_bmi = $thisdate = $thisenc = 0;
		while($vitals = sqlFetchArray($vres)) {
			if($vitals{'pid'} != $thispat) {
				// Average and increment our counts here
				$vital_bps_avg = $vital_bpd_avg = 0;
				if($thispat != '') {
					$pats++;
					if($bps_tot && $vital_cnt) {
						$vital_bps_avg = ($bps_tot / $vital_cnt);
					}
					if($bpd_tot && $vital_cnt) {
						$vital_bpd_avg = ($bpd_tot / $vital_cnt);
					}
					if($vital_bps_avg < 130 || $vital_bpd_avg < 80) $under++;
					if($vital_bps_avg > 140 || $vital_bpd_avg > 90) $over++;
				}
				if($thisdate == '' || $thisdate == 0) $thisdate = date('Y-m-d');
				$pat_age = getPatientAge($thisDOB, $thisdate);
				if($last_bmi) {
					$plan = sqlQuery($plan_query, array($thispat, $thisenc));
					if(!isset($plan{'comments'})) $plan{'comments'} = '';
					if($pat_age > 17 && $pat_age < 65 && $plan{'comments'} != '') {
						if($last_bmi < 23 || $last_bmi >= 30) $bmi_pat_cnt++;
					} else if($pat_age > 64) {
						if($last_bmi <= 18.5 || $last_bmi >= 25) $bmi_pat_cnt++;
					}
				}
				$vital_cnt= $bps_tot= $bpd_tot=0;
			}

			$thispat = $vitals{'pid'};
			$thisDOB = $vitals{'DOB'};
			if($vitals{'BMI'} != '' && $vitals{'BMI'} != 0.0 && $vitals{'BMI'} != 0) {
				$last_bmi = $vitals{'BMI'};
				$thisdate = substr($vitals{'date'},0,10);
				$thisenc = $vitals{'encounter'};
			}
			if($vitals{'bps'} && $vitals{'bpd'}) {
				$tests++;
				$bps_tot=$bps_tot+$vitals{'bps'};
				$bpd_tot=$bpd_tot+$vitals{'bpd'};
				$vital_cnt++;
			}
		}
		// Average and increment our counts here
		$vital_bps_avg = $vital_bpd_avg = 0;
		if($thispat != '') {
			$pats++;
			if($bps_tot && $vital_cnt) {
				$vital_bps_avg = ($bps_tot / $vital_cnt);
			}
			if($bpd_tot && $vital_cnt) {
				$vital_bpd_avg = ($bpd_tot / $vital_cnt);
			}
			if($vital_bps_avg < 130 || $vital_bpd_avg < 80) { $under++; }
			if($vital_bps_avg > 140 || $vital_bpd_avg > 90) { $over++; }

			if($thisdate == '' || $thisdate == 0) $thisdate = date('Y-m-d');
			$pat_age = getPatientAge($thisDOB, $thisdate);
			if($last_bmi) {
				$plan = sqlQuery($plan_query, array($thispat, $thisenc));
				if(!isset($plan{'comments'})) $plan{'comments'} = '';
				if($pat_age > 17 && $pat_age < 65 && $plan{'comments'} != '') {
					if($last_bmi < 23 || $last_bmi >= 30) $bmi_pat_cnt++;
				} else if($pat_age > 64) {
					if($last_bmi <= 18.5 || $last_bmi >= 25) $bmi_pat_cnt++;
				}
			}
		}
	}
	return( array('under' => $under, 'over' => $over, 'pats' => $pats, 'tests' => $tests, 'bmi_cnt' => $bmi_pat_cnt));
	
}

function GetVitaminDResults($doc, $from_date, $to_date='') {
	if($to_date == '') $to_date = $from_date;
	$from_date .= ' 00:00:00';
	$to_date .= ' 23:59:59';
	$tests = $pats = 0;
	$lastpat = '';
	$query = "SELECT " .
		"lri.test_code, lri.observation_value, lri.observation_status, ".
		"lr.specimen_datetime, lr.request_id, ".
		"lo.order_datetime, lo.pid, ".
		"patient_data.providerID, patient_data.DOB ".
  	"FROM form_labcorp_result_item AS lri " .
  	"LEFT JOIN form_labcorp_result AS lr ON (lri.parent_id = lr.id) " .
  	"LEFT JOIN form_labcorp_order AS lo ON (lo.id = lr.request_id) " .
		"LEFT JOIN patient_data ON (lo.pid = patient_data.pid) ".
  	"WHERE ".
		"( lri.test_code = '081950' ) ".
  	"AND order_datetime >= ? AND order_datetime <= ? AND providerID = ? ".
		"ORDER BY lo.pid";
	$fres = sqlStatement($query, array($from_date, $to_date, $doc));
	while($frow = sqlFetchArray($fres)) {
		if($frow{'pid'} != $lastpat) $pat_used = 0;
		$tests++;
		if(!$pat_used) $pats++;
		$lastpat = $frow{'pid'};
		$pat_used++;
	}
/****/
	$lastpat = '';
	$query = "SELECT " .
		"lri.test_code, lri.observation_value, lri.observation_status, ".
		"lr.specimen_datetime, lr.request_id, ".
		"lo.order_datetime, lo.pid, ".
		"patient_data.providerID, patient_data.DOB ".
  	"FROM form_quest_result_item AS lri " .
  	"LEFT JOIN form_quest_result AS lr ON (lri.parent_id = lr.id) " .
  	"LEFT JOIN form_quest_order AS lo ON (lo.id = lr.request_id) " .
		"LEFT JOIN patient_data ON (lo.pid = patient_data.pid) ".
  	"WHERE ".
		"( lri.test_code = '16558' OR lri.test_code = '16761' ) ".
  	"AND order_datetime >= ? AND order_datetime <= ? AND providerID = ? ".
		"ORDER BY lo.pid";
	$fres = sqlStatement($query, array($from_date, $to_date, $doc));
	while($frow = sqlFetchArray($fres)) {
		if($frow{'pid'} != $lastpat) $pat_used = 0;
		$tests++;
		if(!$pat_used) $pats++;
		$lastpat = $frow{'pid'};
		$pat_used++;
	}
/***/
	
	return( array( 'tests' => $tests, 'pats' => $pats) );
}

function GetUrineResults($doc, $from_date, $to_date='') {
	if($to_date == '') $to_date = $from_date;
	$from_date .= ' 00:00:00';
	$to_date .= ' 23:59:59';
	$tests = $pats = 0;
	$lastpat = '';
	$query = "SELECT " .
		"lri.test_code, lri.observation_value, lri.observation_status, ".
		"lr.specimen_datetime, lr.request_id, ".
		"lo.order_datetime, lo.pid, ".
		"patient_data.providerID, patient_data.DOB ".
  	"FROM form_labcorp_result_item AS lri " .
  	"LEFT JOIN form_labcorp_result AS lr ON (lri.parent_id = lr.id) " .
  	"LEFT JOIN form_labcorp_order AS lo ON (lo.id = lr.request_id) " .
		"LEFT JOIN patient_data ON (lo.pid = patient_data.pid) ".
  	"WHERE ".
		"( lri.test_code = '140285' ) ".
  	"AND order_datetime >= ? AND order_datetime <= ? AND providerID = ? ".
		"ORDER BY lo.pid";
	$fres = sqlStatement($query, array($from_date, $to_date, $doc));
	while($frow = sqlFetchArray($fres)) {
		if($frow{'pid'} != $lastpat) $pat_used = 0;
		$tests++;
		if(!$pat_used) $pats++;
		$lastpat = $frow{'pid'};
		$pat_used++;
	}
	
	return( array( 'tests' => $tests, 'pats' => $pats) );
}

// Process our array of result totals here
if ($lres) {
	// Initialize Variables
  $lastdoc = '';
	$lastpat = '';
	$DocAccumulator=new DoctorTotal();
  while ($row = sqlFetchArray($lres)) {
		// echo "<tr><td>";
		// print_r($row);
		// echo "</td></tr>\n";
		if(($row{'providerID'} != $lastdoc)) {
			if($lastpat != '') $DocAccumulator->ChangePat();
			if($lastdoc != '') {
				$ttl = GetPatientTotal($lastdoc,$form_from_date,$form_to_date);
				$bp = GetBPResults($lastdoc, $form_from_date, $form_to_date);
				$foot = GetFootTotal($lastdoc, $form_from_date, $form_to_date);
				$d = GetVitaminDResults($lastdoc, $form_from_date, $form_to_date);
				$u = GetUrineResults($lastdoc, $form_from_date, $form_to_date);
				$DocAccumulator->Total_pats_lt_18 = $ttl['lt_18'];
				$DocAccumulator->Total_pats_gt_17 = $ttl['gt_17'];
				$DocAccumulator->Total_pats_gt_75 = $ttl['gt_75'];
				$DocAccumulator->Total_pats = $ttl['ttl'];
				$DocAccumulator->Tobacco_use = $ttl['smk'];
				$DocAccumulator->Foot_exam = $foot;
				$DocAccumulator->Bp_lt_130 = $bp['under'];
				$DocAccumulator->Bp_gt_140 = $bp['over'];
				$DocAccumulator->Bp_pats = $bp['pats'];
				$DocAccumulator->Bp_tests = $bp['tests'];
				$DocAccumulator->BMI_follow_up = $bp['bmi_cnt'];
				$DocAccumulator->D_tests = $d['tests'];
				$DocAccumulator->D_pats = $d['pats'];
				$DocAccumulator->Urine_tests = $u['tests'];
				$DocAccumulator->Urine_pats = $u['pats'];
				$docname=UserDispNameFromID($lastdoc);
				if($docname == '') $docname = '** No User Assigned **';
				$DocAccumulator->PrintSummary($docname);
				$DocAccumulator=new DoctorTotal();
			}

			$lastpat = '';
		}
		$lastdoc = $row{'providerID'};
		// Add this patient to the correct test/result category
		if($row{'pid'} != $lastpat) $DocAccumulator->ChangePat();

		$lastpat = $row{'pid'};
		if($row{'HgbA1c'} != '') {
			$DocAccumulator->A1c_tests++;
			$DocAccumulator->A1c_pat_total = $DocAccumulator->A1c_pat_total + $row{'HgbA1c'};
			$DocAccumulator->A1c_pat_tests++;
		}
		if($row{'LDL'} != '') {
			$DocAccumulator->LDL_tests++;
			$DocAccumulator->LDL_pat_total = $DocAccumulator->LDL_pat_total + $row{'LDL'};
			$DocAccumulator->LDL_pat_tests++;
		}
		if($row{'HDL'} != '') {
			$DocAccumulator->HDL_tests++;
			$DocAccumulator->HDL_pat_total = $DocAccumulator->HDL_pat_total + $row{'HDL'};
			$DocAccumulator->HDL_pat_tests++;
		}
		if($row{'trig'} != '') {
			if($row{'trig'} < 250 && !$DocAccumulator->Tri_pat_lt_250) {
				$DocAccumulator->Tri_lt_250++;
				$DocAccumulator->Tri_pat_lt_250++;
			} 
			$DocAccumulator->Tri_tests++;
			if(!$DocAccumulator->Tri_pat_used) $DocAccumulator->Tri_pats++;
			$DocAccumulator->Tri_pat_used++;
		}
	}
	if($lastdoc != '') {
		$ttl = GetPatientTotal($lastdoc,$form_from_date,$form_to_date);
		$bp = GetBPResults($lastdoc, $form_from_date, $form_to_date);
		$foot = GetFootTotal($lastdoc, $form_from_date, $form_to_date);
		$d = GetVitaminDResults($lastdoc, $form_from_date, $form_to_date);
		$u = GetUrineResults($lastdoc, $form_from_date, $form_to_date);
		$DocAccumulator->Total_pats_lt_18 = $ttl['lt_18'];
		$DocAccumulator->Total_pats_gt_17 = $ttl['gt_17'];
		$DocAccumulator->Total_pats_gt_75 = $ttl['gt_75'];
		$DocAccumulator->Total_pats = $ttl['ttl'];
		$DocAccumulator->Tobacco_use = $ttl['smk'];
		$DocAccumulator->Foot_exam = $foot;
		$DocAccumulator->Bp_lt_130 = $bp['under'];
		$DocAccumulator->Bp_gt_140 = $bp['over'];
		$DocAccumulator->Bp_pats = $bp['pats'];
		$DocAccumulator->Bp_tests = $bp['tests'];
		$DocAccumulator->D_tests = $d['tests'];
		$DocAccumulator->D_pats = $d['pats'];
		$DocAccumulator->Urine_tests = $u['tests'];
		$DocAccumulator->Urine_pats = $u['pats'];
		if($lastpat != '') $DocAccumulator->ChangePat();
		$docname=UserDispNameFromID($lastdoc);
		if($docname == '') $docname = '** No User Assigned **';
		$DocAccumulator->PrintSummary($docname);
	}	
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

</form>
</body>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>

</script>

</html>
