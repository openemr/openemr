<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows the results of patient screens with filtering

require_once("../../globals.php");
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/formatting.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

$ORDERHASH = array(
  'doctor'  => 'lower(users.lname), lower(users.fname), form_encounter.date',
  'patient' => 'lower(patient_data.lname), lower(patient_data.fname), form_encounter.date',
  'pubpid'  => 'lower(patient_data.pubpid), form_encounter.date',
  'time'    => 'form_encounter.date, lower(users.lname), lower(users.fname)',
);
$pop_forms = getFormsByType(array('pop_form'));
$screen_forms = getFormsByType(array('screen_form'));
$pop_used = checkSettingMode('wmt::form_popup');

$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$form_from_date= date('Y-m-d', $last_month);
if(isset($_POST['form_from_date'])) {
	$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
}
$form_to_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(isset($_POST['form_to_date'])) {
	$form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
}
$form_provider  = '';
$form_facility  = '';
$form_complete  = '';
$form_status    = '';
$form_referral  = 0;
$form_name      = array();
$form_no_referral  = 0;
$create         = '';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_facility'])) $form_facility = $_POST['form_facility'];
if(isset($_POST['form_status'])) $form_complete = $_POST['form_status'];
if(isset($_POST['form_name'])) $form_name = $_POST['form_name'];
if(isset($_POST['form_referral'])) $form_referral = $_POST['form_referral'];
if(isset($_POST['form_no_referral'])) $form_no_referral = $_POST['form_no_referral'];
if(isset($_GET['create'])) $create = strip_tags($_GET['create']);
$form_details   = '1';
if($form_name[0] == '') $form_name = array();

$form_orderby = 'doctor';
if(isset($_REQUEST['form_orderby'])) $form_orderby = $_REQUEST['form_orderby'];
$orderby = $ORDERHASH[$form_orderby];

$include_rcmd = FALSE;
$query = 'SHOW TABLES LIKE "%form_smoke_screen2"';
$res = sqlQuery($query);
if($res) $include_rcmd = TRUE;

if(count($form_name)) {
	if(!in_array('smoke_screen2', $form_name)) $include_rcmd = FALSE;
}

$columns = array("Provider", "Date", "Patient Name", "Patient ID", "Referral",
	"Type of Screen");
if($include_rcmd) $columns[] = 'Recommended';

$query = "SELECT " .
  "forms.formdir, forms.form_name, forms.deleted, forms.form_id, forms.user, " .
  "form_encounter.encounter, form_encounter.date, form_encounter.reason, " .
  "patient_data.fname, patient_data.mname, patient_data.lname, " .
  "patient_data.pubpid, patient_data.pid, " .
  "users.lname AS ulname, users.fname AS ufname, users.mname AS umname, " .
  "users.username FROM forms " .
  "LEFT JOIN form_encounter USING (encounter) " .
  "LEFT JOIN users ON form_encounter.provider_id = users.id " .
  "LEFT JOIN patient_data ON forms.pid = patient_data.pid " .
  "WHERE " .
  "forms.deleted != '1' ";
if(count($form_name)) {
	$query .= 'AND (';
	$tmp = 1;
	foreach($form_name as $dir) {
		if($tmp > 1) $query .= ' OR ';
		$query .= 'forms.formdir = "' . $dir . '"';
		$tmp++;
	}
	$query .= ') ';
} else if(count($screen_forms)) {
	$query .= 'AND (';
	$tmp = 1;
	foreach($screen_forms as $dir) {
		if($tmp > 1) $query .= ' OR ';
		$query .= 'forms.formdir = "' . $dir['form_name'] . '"';
		$tmp++;
	}
	$query .= ') ';
}
if ($form_to_date) {
  $query .= "AND form_encounter.date >= '$form_from_date 00:00:00' AND form_encounter.date <= '$form_to_date 23:59:59' ";
} else {
  $query .= "AND form_encounter.date >= '$form_from_date 00:00:00' AND form_encounter.date <= '$form_from_date 23:59:59' ";
}
if ($form_provider !== '') {
  $query .= "AND form_encounter.provider_id = '$form_provider' ";
}
if ($form_facility) {
  $query .= "AND form_encounter.facility_id = '$form_facility' ";
}

$query .= "ORDER BY $orderby";

$res=array();
if(isset($_GET['mode'])) { 
	set_time_limit(0);
	$res = sqlStatement($query);
	$cnt = sqlNumRows($res);
}

echo "Query: $query<br>\n";
while ($row = sqlFetchArray($res)) {
		echo "Row: ";
		print_r($row);
		echo "<br>\n";
}
if($create == 'csv' && $res) {
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=screens.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, $columns);
 	while ($row = sqlFetchArray($res)) {

    $docname = '';
    if (!empty($row['ulname']) || !empty($row['ufname'])) {
      $docname = $row['ulname'];
      if (!empty($row['ufname']) || !empty($row['umname']))
        $docname .= ', ' . $row['ufname'] . ' ' . $row['umname'];
    }
		$sql = "SELECT * FROM form_".$row{'formdir'}." WHERE id=".$row{'form_id'};
		$dres = sqlQuery($sql);
		if($form_complete) {
			if($dres{'form_complete'} != $form_complete) continue;
		}
		if ($form_referral) {
			if($dres{'referral'} != 1 && 
				strtolower(substr($dres{'referral'},0,1)) != 'y') continue;
		}
		if ($form_no_referral) {
			if($dres{'referral'} &&
				strtolower(substr($dres{'referral'},0,1)) != 'n') continue;
		}

		$referral = 'No';
		if($dres['referral'] == 1 || 
			strtolower(substr($dres{'referral'},0,1)) == 'y') $referral = 'Yes';
		$rcmd = '';
		if($include_rcmd) {
			if($row['formdir'] == 'smoke_screen2') $rcmd = 'No';
			if($dres['referral_rcmd'] == 1 || 
				strtolower(substr($dres{'referral_rcmd'},0,1)) == 'y') $rcmd = 'Yes';
		}
		if($row['formdir'] == 'alcohol_screen' && $referral == 'Yes') $referral = 'SBIRT';
		$fdate = oeFormatShortDate(substr($row['date'], 0, 10));
		$patname = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
		unset($data);
		$data = array($docname, $fdate, $patname, $row{'pubpid'}, $referral,
   		$row{'form_name'});
		if($include_rcmd) $data[] = $rcmd;
		fputcsv($output, $data);
	}
	fclose($output);
} else {

?>
<html>
<head>
<title><?php xl('Patient Screening Report','e'); ?></title>

<link rel=stylesheet href="<?php echo $GLOBALS['css_header'];?>" type="text/css">
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

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>'/library/wmt-v2/report_tools.js"></script>

<?php } ?>

<script type="text/javascript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function dosort(orderby) {
  var f = document.forms[0];
  f.form_orderby.value = orderby;
  f.submit();
  return false;
 }

 function refreshme() {
  document.forms[0].submit();
 }

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Screening Forms','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='screen_rpt.php?mode=search'>

<div id="report_parameters">
<table>
 <tr>
  <td width='85%'>
    <div style='float:left'>

      <table class='text'>
        <tr>
          <td class='label' style='text-align: left;'><?php xl('Facility','e'); ?>: </td>
          <td>
	    <?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', true); ?></td>
          <td class='label'><?php xl('Form Status','e'); ?>: </td>
          <td><?php
               // Build a drop-down list of form statuses.
              $query = "SELECT option_id, title FROM list_options WHERE ".
                "list_id = 'Form_Status' ORDER BY seq";
              $ures = sqlStatement($query);

              echo "   <select name='form_status'>\n";
              echo "    <option value=''>-- " . xl('All') . " --\n";

              while ($urow = sqlFetchArray($ures)) {
                $statid = $urow['option_id'];
                echo "    <option value='$statid'";
                if ($statid == $form_status) echo " selected";
                echo ">" . $urow['title'] . "\n";
              }
              echo "   </select>\n";
              ?></td>
          <td class='label'><?php xl('Form Type','e'); ?>: </td>
          <td rowspan="3"><select name="form_name[]" multiple size="4">
							<option value="" <?php echo count($form_name) < 1 ? 'selected' : ''; ?> > -- ALL -- </option>
							<?php
							foreach($screen_forms as $form) {
								echo "		<option value='".$form['form_name']."'";
								if(in_array($form['form_name'], $form_name)) echo " selected";
								echo ">";
								echo ($form['nickname'] != '') ? $form['nickname'] : $form['name'];
								echo "</options>\n";
							}
							?>
          </select></td>
         </tr>
         <tr>
          <td class='label' style='text-align: left;'><?php xl('Provider','e'); ?>: </td>
          <td><?php
               // Build a drop-down list of providers.
              $query = "SELECT id, username, lname, fname FROM users WHERE " .
								"authorized=1 AND active='1' AND username!='' " .
								"AND ( specialty LIKE ".
								"'%Provider%' OR specialty LIKE '%Supervisor%' ) ".
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
					<td colspan="2" class="label" style="text-align: left;"><input name="form_referral" id="form_referral" type="checkbox" value="1" <?php echo (($form_referral)?'checked':''); ?> onclick="TogglePair('form_referral','form_no_referral');" /><label for="form_referral">&nbsp;&nbsp;Only Report Forms With a Referral</label></td>
        </tr>
				<tr>
           <td colspan="2"><span class='label'><?php xl('From','e'); ?>: </span>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'>&nbsp;&nbsp;
           <span class='label'><?php xl('To','e'); ?>: </span>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
					<td colspan="2" class="label" style="text-align: left;"><input name="form_no_referral" id="form_no_referral" type="checkbox" value="1" <?php echo (($form_no_referral)?'checked':''); ?> onchange="TogglePair('form_no_referral','form_referral');" /><label for="form_no_referral">&nbsp;&nbsp;Only Report Forms With No Referral</label></td>
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
          </div>
				</td>
			</tr>
			<tr>
				<td>
            <?php if (isset($_POST['form_refresh']) || isset($_POST['form_orderby'])) { ?>
          <div style='margin-left:15px'>
            <a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php xl('Print','e'); ?>
						</span>
					</a>
					</div>
            <?php } ?>
        </td>
      </tr>
			<tr>
				<td>
            <?php if(isset($_GET['mode']) ) { ?>
						<div style="margin-left: 15px; ">
            <a href='javascript:;' class='css_button' onclick="formCreateCSV(); ">
						<span><?php xl('Create as CSV','e'); ?></span></a></div>
            <?php } ?>
				</td>
			</tr>
     </table>
  </td>
 </tr>
</table>

</div> <!-- end report_parameters -->

<?php
 if (isset($_POST['form_refresh']) || isset($_POST['form_orderby'])) {
?>
<div id="report_results">
<table>

 <thead>
<?php if ($form_details) { ?>
  <th>
   <a href="nojs.php" onclick="return dosort('doctor')"
   <?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php  xl('Provider','e'); ?> </a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('time')"
   <?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  xl('Date','e'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('patient')"
   <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  xl('Patient','e'); ?></a>
  </th>
  <th>
   <a href="nojs.php" onclick="return dosort('pubpid')"
   <?php if ($form_orderby == "pubpid") echo " style=\"color:#00cc00\"" ?>><?php  xl('ID','e'); ?></a>
  </th>
  <th>
   <?php  xl('Status','e'); ?>
  </th>
  <th>
   <?php  xl('Referral','e'); ?>
  </th>
<?php if($include_rcmd) { ?>
  <th>
   <?php  xl('Recommended','e'); ?>
  </th>
<?php } ?>
  <th>
   <?php  xl('Form','e'); ?>
  </th>
<?php } ?>
 </thead>
 <tbody>
<?php
if ($res) {
	$bgcolor = '#ffdddd';
  $lastdocname = "";
  $doc_encounters = 0;
	$doc_referrals = 0;
  while ($row = sqlFetchArray($res)) {

		echo "Row: ";
		print_r($row);
		echo "<br>\n";
    $docname = '';
    if (!empty($row['ulname']) || !empty($row['ufname'])) {
      $docname = $row['ulname'];
      if (!empty($row['ufname']) || !empty($row['umname']))
        $docname .= ', ' . $row['ufname'] . ' ' . $row['umname'];
    }
		$sql = "SELECT * FROM form_".$row{'formdir'}." WHERE id=".$row{'form_id'};
		$dres = sqlQuery($sql);
		if($form_complete) {
			if($dres{'form_complete'} != $form_complete) continue;
		}
		if ($form_referral) {
			if($dres{'referral'} != 1 && 
				strtolower(substr($dres{'referral'},0,1)) != 'y') continue;
		}
		if ($form_no_referral) {
			if($dres{'referral'} && 
				strtolower(substr($dres{'referral'},0,1)) != 'n') continue;
		}
		$rcmd = '';
		if($include_rcmd) {
			if($row['formdir'] == 'smoke_screen2') $rcmd = 'No';
			if($dres['referral_rcmd'] == 1 || 
				strtolower(substr($dres{'referral_rcmd'},0,1)) == 'y') $rcmd = 'Yes';
		}

    $errmsg  = "";
    $fstatus = ListLook($dres['form_complete'],'Form_Status');
    if(!$fstatus) $status = 'Unassigned';
		$referral = 'No';
		if($dres['referral'] == 1 || 
				strtolower(substr($dres{'referral'},0,1)) == 'y') $referral = 'Yes';
		$bgcolor = (($bgcolor == '#ffdddd')?'#ddddff':'#ffdddd');
?>
 <tr style='background-color: <?php echo $bgcolor ?>;'>
  <td>
   <?php echo $docname; ?>&nbsp;
  </td>
  <td>
   <?php echo oeFormatShortDate(substr($row['date'], 0, 10)) ?>&nbsp;
  </td>
  <td>
		<a href="javascript:;" onclick="goPid('<?php echo $row{'pid'}; ?>');" >
   <?php echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']; ?>&nbsp;
		</a>
  </td>
  <td>
   <?php echo $row['pubpid']; ?>&nbsp;
  </td>
  <td>
   <?php echo $fstatus; ?>&nbsp;
  </td>
  <td>
   <?php echo $referral; ?>&nbsp;
  </td>
<?php if($include_rcmd) { ?>
  <td>
   <?php echo $rcmd; ?>&nbsp;
  </td>
<?php } ?>
  <td>
	<?php if($pop_used) { ?>
		<a href="javascript:;" onclick="FormPop('<?php echo $row{'pid'}; ?>', '<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', '<?php echo $row{'formdir'}; ?>');">
	<?php } ?>
   <?php echo $row{'form_name'}; ?>&nbsp;
	<?php if($pop_used) { ?>
		</a>
	<?php } ?>
  </td>
 </tr>
<?php
    $lastdocname = $docname;
		$doc_encounters++;
		if($dres{'referral'}) { $doc_referrals++; }
  }

}
?>
</tbody>
</table>
</div>  <!-- end encresults -->
<br>
<?php if($doc_encounters || $doc_referrals) { ?>
<div class='text'>
 	<?php echo xl('Number of Forms Reported', 'e' ); ?>:&nbsp;<?php echo $doc_encounters; ?>
</div><br>
<div class='text'>
 	<?php echo xl('Number of Referrals', 'e' ); ?>:&nbsp;<?php echo $doc_referrals; ?>
</div>
<?php } ?>
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</body>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtstandard.popup.js"></script>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) echo " alert('$alertmsg');\n"; ?>
function formCreateCSV() {
	var my_action = "<?php echo $GLOBALS['webroot']; ?>/interface/reports/myreports/screen_rpt.php?form_from_date=<?php echo $form_from_date; ?>&form_to_date=<?php echo $form_to_date; ?>&create=csv&mode=search";
	var my_action = "<?php echo $GLOBALS['webroot']; ?>/interface/reports/myreports/screen_rpt.php?create=csv&mode=search";
	document.forms[0].action = my_action;
	document.forms[0].submit();
}

</script>

</html>

<!-- This is my temporary end condition -->
<?php } ?>
