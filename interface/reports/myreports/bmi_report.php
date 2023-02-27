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

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later
$last_year= mktime(0,0,0,date('m'),date('d'),date('Y')-1);
$last_month= mktime(0,0,0,date('m')-1,date('d'),date('Y'));
if(!isset($_POST['form_from_date'])) { $_POST['form_form_date'] = '' ; }
if(!isset($_POST['form_to_date'])) { $_POST['form_to_date'] = '' ; }
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d', $last_year));
$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_provider  = '';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];

$query = "SELECT " .
	"form_encounter.date, form_encounter.pid, form_encounter.provider_id, ". 
	"patient_data.DOB, patient_data.lname, patient_data.fname, ".
	"forms.deleted ".
  "FROM form_encounter ".
  "LEFT JOIN patient_data ON (form_encounter.pid = patient_data.pid) " .
	"LEFT JOIN forms on (form_encounter.pid = forms.pid AND ".
	"form_encounter.id=forms.form_id AND ".
	"forms.formdir='newpatient') ".
	"WHERE (DOB <= '1995-09-17') ".
	"AND forms.deleted=0 ";
if ($form_to_date) {
  $query .= "AND form_encounter.date >= '$form_from_date 00:00:00 ' AND form_encounter.date <= '$form_to_date 23:59:59' ";
} else {
  $query .= "AND form_encounter.date >= '$form_from_date 00:00:00' AND form_encounter.date <= '$form_from_date 23:59:59' ";
}
if ($form_provider) {
  $query .= "AND form_encounter.provider_id = '$form_provider' ";
}
$query .= "GROUP BY form_encounter.pid";
$query .= " ORDER BY form_encounter.pid";
echo "Query: ",$query,"\n";

$lres=array();
// if(isset($_GET['mode'])) { 
	$lres = sqlStatement($query);
	$cnt = sqlNumRows($lres);
	echo "<br/></br>Row Count: $cnt<br/><br/>\n";
// }
?>
<html>
<head>
<title><?php xl('Abnormal BMI Summary','e'); ?></title>
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

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Summary of Abnormal BMI','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='bmi_report.php?mode=search'>

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
function GetLastBMI($thisPid, $thisDOB) {
	$tst = 'none';
	echo "<tr><td>Getting Vitals For $thisPid</td></tr>\n";
	$sql = "SELECT form_vitals.id, form_vitals.BMI, form_vitals.date, ".
		"forms.deleted ".
		"FROM form_vitals ".
		"LEFT JOIN forms ON (form_vitals.id = forms.form_id AND ".
		"form_vitals.pid = forms.pid) ".
		"WHERE form_vitals.pid=? AND forms.deleted='0' ".
 	  "AND (form_vitals.BMI !=0 AND form_vitals.BMI != '0.0' AND ".
		"form_vitals.BMI IS NOT NULL AND form_vitals.BMI != '') ".
		"ORDER BY form_vitals.date DESC LIMIT 1";
	$vres= sqlStatement($sql, array($thisPid));
	$brow= sqlFetchArray($vres);
	if($brow{'id'}) {
		// echo "<tr><td>Vitals Located! ID [",$brow{'id'},"]  BMI: (",$brow{'BMI'},")</td></tr>\n";
		// echo "<tr><td>This Patients DOB is $thisDOB</td></tr>\n";
		if($thisDOB < '1248-09-17') {
			if($brow{'BMI'} < 23 || $brow{'BMI'} >= 30) { 
				$tst='abnormal';
			} else $tst='normal';
		} else {
			if($brow{'BMI'} <= 18.5 || $brow{'BMI'} >= 25) { 
				$tst='abnormal';
			} else $tst='normal';
		}
		
	} else {
		// echo "<tr><td>No Vitals Located</td></tr>\n";
	}
	// echo "<tr><td>Returning: $tst</td></tr>\n";
	return $tst;
	
}
// Build an array of result totals here
if ($lres) {
	// Initialize Variables
	$normal=0;
	$abnormal=0;
  while ($row = sqlFetchArray($lres)) {
		// echo "<tr><td>";
		// print_r($row);
		// echo "</td></tr>\n";
		$check=GetLastBMI($row['pid'], $row['DOB']);
		if($check == 'normal') { $normal++; }
		if($check == 'abnormal') { $abnormal++; }
	}	
}
echo "<tr><td>Normal BMI: $normal</td></tr>\n";
echo "<tr><td>Abnormal BMI: $abnormal</td></tr>\n";
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
