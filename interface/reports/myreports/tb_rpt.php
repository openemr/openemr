<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report helps locate/sort tb and lead screen forms that need testing.

require_once("../../globals.php");
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/formatting.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/formhunt.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'doctor'  => 'lower(users.lname), lower(users.fname), form_encounter.date',
  'patient' => 'lower(patient_data.lname), lower(patient_data.fname), form_encounter.date',
  'pubpid'  => 'lower(patient_data.pubpid), form_encounter.date',
  'time'    => 'form_encounter.date, lower(users.lname), lower(users.fname)',
);
$pop_forms= getFormsByType(array('pop_form'));
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
$form_do_test  = 0;
$form_name      = '';
$form_no_test  = 0;
$create         = '';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_facility'])) $form_facility = $_POST['form_facility'];
if(isset($_POST['form_status'])) $form_complete = $_POST['form_status'];
if(isset($_POST['form_name'])) $form_name = $_POST['form_name'];
if(isset($_POST['form_do_test'])) $form_do_test = $_POST['form_do_test'];
if(isset($_POST['form_no_test'])) $form_no_test = $_POST['form_no_test'];
if(isset($_GET['create'])) $create = $_GET['create'];
$form_details   = "1";

$form_orderby = 'doctor';
if(isset($_REQUEST['form_orderby'])) $form_orderby = $_REQUEST['form_orderby'];
$orderby = $ORDERHASH[$form_orderby];

$columns = array("Provider", "Date", "Patient Name", "Patient ID", 
		"Testing Indicated", "Type of Screen");

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
	if($form_name) {
		$query .= "AND forms.formdir = '$form_name' ";
	} else {
		$query .= "AND (forms.formdir = 'lead_screen' ";
		$query .= "OR forms.formdir = 'tb_screen') ";
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

// echo "Query: $query<br>\n";
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
			if($dres{'form_complete'} != $form_complete) { continue; }
		}
		if ($form_do_test) {
			if($dres{'test_needed'} != 1) { continue; }
		}
		if ($form_no_test) {
			if($dres{'test_needed'}) { continue; }
		}

		$do_test = 'No';
		if($dres['test_needed'] == 1) { $do_test = 'Yes'; }
		$fdate = oeFormatShortDate(substr($row['date'], 0, 10));
		$patname = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
		unset($data);
		$data = array($docname, $fdate, $patname, $row{'pubpid'}, $do_test,
   		$row{'form_name'});
		fputcsv($output, $data);
	}
	fclose($output);
} else {

?>
<html>
<head>
<title><?php xl('Lead/TB Screens','e'); ?></title>
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

<script LANGUAGE="JavaScript">

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

function ScreenPop(pid, id, enc, form)
{
	var warn_msg = '';
	if(pid == '' || pid == 0) warn_msg = 'Patient ID is NOT set - ';
	if(id == '' || id == 0) warn_msg = 'Form ID is NOT set - ';
	if(enc == '' || enc == 0) warn_msg = 'Encounter is NOT set - ';
	if(form == '' || form == 0) warn_msg = 'No Form Name Specified - ';
	if(warn_msg != '') {
		alert(warn_msg + 'Not Able to Pop Open this Form');
		return false;
	}
	wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/'+form+'/view.php?mode=update&pid='+pid+'&id='+id+'&enc='+enc, '_blank', 900, 900, 1);
}

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Lead/TB Screens','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='tb_rpt.php?mode=search'>

<div id="report_parameters">
<table>
 <tr>
  <td width='800px'>
    <div style='float:left'>

      <table class='text'>
        <tr>
          <td class='label'><?php xl('Facility','e'); ?>: </td>
          <td>
	    <?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', true); ?></td>
          <td class='label'><?php xl('Provider','e'); ?>: </td>
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
          <td class='label'><?php xl('Form Type','e'); ?>: </td>
          <td><select name='form_name'>
             <option value='' <?php echo (($form_name == '')?'selected':''); ?> >-- Both  --</option>

             <option value='lead_screen' <?php echo (($form_name == 'lead_screen')?'selected':''); ?> >Lead Screen</option>
             <option value='tb_screen' <?php echo (($form_name == 'tb_screen')?'selected':''); ?> >TB Screen</option>
          </select></td>
        </tr>
				<tr>
					<td colspan="2" class="label" style="text-align: left;"><input name="form_do_test" id="form_do_test" type="checkbox" value="1" <?php echo (($form_do_test)?'checked':''); ?> onclick="TogglePair('form_do_test','form_no_test');" /><label for="form_do_test">&nbsp;&nbsp;Only Report Forms Indicating Test</label></td>
					<td colspan="2" class="label" style="text-align: left;"><input name="form_no_test" id="form_no_test" type="checkbox" value="1" <?php echo (($form_no_test)?'checked':''); ?> onchange="TogglePair('form_no_test','form_do_test');" /><label for="form_no_test">&nbsp;&nbsp;Only Report Forms Not Indicating Test</label></td>
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
					<span><?php xl('Submit','e'); ?></span></a>

            <?php if (isset($_POST['form_refresh']) || isset($_POST['form_orderby'])) { ?>
            <a href='#' class='css_button' onclick='window.print()'>
						<span><?php xl('Print','e'); ?> </span></a>
            <?php } ?>
          </div>
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
   <?php  xl('Testing Indicated','e'); ?>
  </th>
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
	$doc_tests = 0;
  while ($row = sqlFetchArray($res)) {

    $docname = '';
    if (!empty($row['ulname']) || !empty($row['ufname'])) {
      $docname = $row['ulname'];
      if (!empty($row['ufname']) || !empty($row['umname']))
        $docname .= ', ' . $row['ufname'] . ' ' . $row['umname'];
    }
		$sql = "SELECT * FROM form_".$row{'formdir'}." WHERE id=?";
		$dres = sqlQuery($sql, array($row{'form_id'}));
		if($form_complete) {
			if($dres{'form_complete'} != $form_complete) { continue; }
		}
		if ($form_do_test) {
			if($dres{'test_needed'} != 1) { continue; }
		}
		if ($form_no_test) {
			if($dres{'test_needed'}) { continue; }
		}

    $errmsg  = "";
    $fstatus = ListLook($dres['form_complete'],'Form_Status');
    if(!$fstatus) { $status = 'Unassigned'; }
		$do_test = 'No';
		if($dres['test_needed'] == 1) { $do_test = 'Yes'; }
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
   <?php echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']; ?>&nbsp;
  </td>
  <td>
   <?php echo $row['pubpid']; ?>&nbsp;
  </td>
  <td>
   <?php echo $fstatus; ?>&nbsp;
  </td>
  <td>
   <?php echo $do_test; ?>&nbsp;
  </td>
  <td>
	<?php if($pop_used) { ?>
		<a href="javascript:;" onclick="ScreenPop('<?php echo $row{'pid'}; ?>', '<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', '<?php echo $row{'formdir'}; ?>');">
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
		if($dres{'test_needed'}) { $doc_tests++; }
  }

}
?>
</tbody>
</table>
</div>  <!-- end encresults -->
<br>
<?php if($doc_encounters || $doc_tests) { ?>
<div class='text'>
 	<?php echo xl('Number of Forms Reported', 'e' ); ?>:&nbsp;<?php echo $doc_encounters; ?>
</div><br>
<div class='text'>
 	<?php echo xl('Number of Tests Needed', 'e' ); ?>:&nbsp;<?php echo $doc_tests; ?>
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

<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/wmt/wmtpopup.js"></script>
<script type="text/javascript" src="../../../library/wmt/wmtstandard.popup.js"></script>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>
function formCreateCSV() {
	var my_action = "../../../interface/reports/myreports/tp_rpt.php?form_from_date=<?php echo $form_from_date; ?>&form_to_date=<?php echo $form_to_date; ?>&create=csv&mode=search";
	var my_action = "../../../interface/reports/myreports/tb_rpt.php?create=csv&mode=search";
	document.forms[0].action = my_action;
	document.forms[0].submit();
}

</script>

</html>

<!-- This is my temporary end condition -->
<?php } ?>
