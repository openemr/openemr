<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report shows past encounters with filtering and sorting.

require_once("../../globals.php");
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/billing.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/formatting.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later
if(!isset($GLOBALS['wmt::link_appt_ins'])) 
		$GLOBALS['wmt::link_appt_ins'] = '';

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  return $desc;
}

// For each sorting option, specify the ORDER BY argument.
//
$ORDERHASH = array(
  'doctor'  => 'lower(users.lname), lower(users.fname), fe.date',
  'patient' => 'lower(patient_data.lname), lower(patient_data.fname), fe.date',
  'pubpid'  => 'lower(patient_data.pubpid), fe.date',
  'time'    => 'fe.date, lower(users.lname), lower(users.fname)',
);
$pop_forms = getFormsByType(array('pop_form'));
$pop_used  = checkSettingMode('wmt::form_popup');

$last_month = mktime(0,0,0,date('m')-1,date('d'),date('Y'));
$form_from_date = date('Y-m-d', $last_month);
if(isset($_POST['form_from_date'])) $form_from_date = 
		fixDate(DateToYYYYMMDD($_POST['form_from_date']), date('Y-m-d'));
$form_to_date = fixDate(date('Y-m-d'), date('Y-m-d'));
if(isset($_POST['form_to_date'])) $form_to_date = 
		fixDate(DateToYYYYMMDD($_POST['form_to_date']), date('Y-m-d'));
$form_provider   = '';
$form_facility   = '';
$form_complete   = '';
$form_status     = '';
$form_code       = '';
$form_user       = '';
$form_csvexport  = '';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_facility'])) $form_facility = $_POST['form_facility'];
if(isset($_POST['form_status'])) $form_complete = $_POST['form_status'];
if(isset($_POST['form_code'])) $form_code = $_POST['form_code'];
if(isset($_POST['form_user'])) $form_user = $_POST['form_user'];
if(isset($_POST['form_csvexport'])) $form_csvexport = $_POST['form_csvexport'];
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';
if(!isset($_POST['form_orderby'])) $_POST['form_orderby'] = '';
$form_details   = "1";

$form_orderby = 'doctor';
if(isset($_REQUEST['form_orderby'])) $form_orderby = $_REQUEST['form_orderby'];
$orderby = $ORDERHASH[$form_orderby];

$ins_fields = sqlListFields('insurance_data');
$binds = array('1', 'consult2');
$query = "SELECT " .
  "f.formdir, f.form_name, f.deleted, f.form_id, f.user, " .
  "fe.encounter, fe.date, fe.reason, " .
  "patient_data.fname, patient_data.mname, patient_data.lname, " .
  "patient_data.pubpid, patient_data.pid, " ;
if($GLOBALS['wmt::link_appt_ins'])
		$query .= "ope.pc_insurance, ic.name AS ins_name, ";
$query .= "ic2.name AS prim_ins_name, ";
$query .= "users.lname AS ulname, users.fname AS ufname, " .
	"users.mname AS umname, users.username, " .
	"form_consult2.form_complete, form_consult2.form_priority, " .
	"form_consult2.c2_code, form_consult2.c2_user " .
  "FROM forms AS f " .
  "LEFT JOIN form_encounter AS fe USING (encounter) " .
  "LEFT JOIN users ON fe.provider_id = users.id " .
  "LEFT JOIN patient_data ON f.pid = patient_data.pid " .
  "LEFT JOIN form_consult2 ON f.form_id = form_consult2.id ";
// JOIN THE ENCOUNTER INSURANCE TO FALL BACK ON
$query .= 'LEFT JOIN insurance_data AS id ON (id.id = (SELECT i.id '.
	'FROM insurance_data AS i WHERE fe.pid = i.pid '.
	'AND i.type = "primary" AND i.date <= SUBSTRING(fe.date,1,10) '.
	'AND i.provider AND i.date != "0000-00-00" ';
if(in_array('termination_date', $ins_fields)) {
	$query .= 'AND (termination_date = "" OR termination_date IS NULL '.
		'OR termination_date = "0000-00-00" OR termination_date > '.
		'SUBSTRING(fe.date,1,10)) ';
}
$query .= 'ORDER BY date DESC LIMIT 1) ) '.
	'LEFT JOIN insurance_companies AS ic2 ON (id.provider = ic2.id) ';

if($GLOBALS['wmt::link_appt_ins']) {
	$query .= "LEFT JOIN appointment_encounter USING (encounter) " .
		"LEFT JOIN openemr_postcalendar_events AS ope ON (eid = pc_eid) " .
		"LEFT JOIN insurance_companies AS ic ON (ic.id = pc_insurance) ";
}
$query .= "WHERE " .
  "f.deleted != ? AND f.formdir = ? ";
if ($form_to_date) {
  $query .= "AND fe.date >= ? AND fe.date <= ? ";
	$binds[] = $form_from_date . ' 00:00:00';
	$binds[] = $form_to_date . ' 23:59:59';
} else {
  $query .= "AND fe.date >= ? AND fe.date <= ? ";
	$binds[] = $form_from_date . ' 00:00:00';
	$binds[] = $form_from_date . ' 23:59:59';
}
if ($form_provider !== '') {
  $query .= "AND fe.provider_id = ? ";
	$binds[] = $form_provider;
}
if ($form_facility) {
  $query .= "AND fe.facility_id = ? ";
	$binds[] = $form_facility;
}
if ($form_complete) {
  $query .= "AND form_consult2.form_complete = ? ";
	$binds[] = $form_complete;
}

if(is_array($form_user) && (count($form_user) > 0) ) {
	if(!in_array("", $form_user)) {
  	$query .= "AND (form_consult2.c2_user) IN (";
		$first = true;
		foreach($form_user as $user) {
			if(!$first) $query .= ', ';
			$query .= "?";
			$binds[] = $user;
			$first = false;
		}
		$query .= ') ';
	}

} else {
	if ($form_user !== '') {
  	$query .= "AND form_consult2.c2_user = ? ";
		$binds[] = $form_user;
	}
}

$query .= "ORDER BY $orderby";

$res=array();
if(isset($_GET['mode'])) $res = sqlStatement($query, $binds);
// echo "Query: $query<br>\n";

if($form_csvexport) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=coded_consults.csv");
  header("Content-Description: File Transfer");
	echo '"Provider Last",';
	echo '"Provider First",';
	echo '"Date",';
	echo '"Patient Last",';
	echo '"Patient First",';
	echo '"PID",';
	echo '"Status",';
  echo '"Billed",';
  echo '"Code",';
  echo '"Employee",';
	echo '"Insurance"';
	echo  "\n";
} else { // END OF CSV EXPORT
?>
<html>
<head>
<title><?php xl('Coded Consults','e'); ?></title>
<link rel=stylesheet href="<?php echo $GLOBALS['css_header'];?>" type="text/css">

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/font-awesome-4-6-3/css/font-awesome.css" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-multiselect/dist/css/bootstrap-customx.css" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-multiselect/dist/css/bootstrap-multiselect.css" type="text/css">

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

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtpopup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/wmtstandard.popup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative']; ?>/jquery-modern-blink-0-1-3/jquery.modern-blink.js"></script>

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

function ScreenPop(pid, id, enc) {
	var warn_msg = '';
	if(pid == '' || pid == 0) warn_msg = 'Patient ID is NOT set - ';
	if(id == '' || id == 0) warn_msg = 'Form ID is NOT set - ';
	if(enc == '' || enc == 0) warn_msg = 'Encounter is NOT set - ';
	if(warn_msg != '') {
		alert(warn_msg + 'Not Able to Pop Open this Form');
		return false;
	}
	wmtOpen('<?php echo $GLOBALS['webroot']; ?>/interface/forms/consult2/view.php?mode=update&pid='+pid+'&id='+id+'&enc='+enc, '_blank', 900, 900, 1);
}

</script>

</head>
<body class="body_top">
<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Coded Consults','e'); ?></span>
<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='code_consult_rpt.php?mode=search'>
<div id="report_parameters">
<table>
 <tr>
  <td width='70%'>
    <div style='float:left'>

      <table class='text'>
        <tr>
          <td class='label'><?php xl('Facility','e'); ?>: </td>
          <td colspan="3">
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
					<td class='text'><?php xl('Employee','e'); ?>:</td>
					<td rowspan="3"><select name="form_user[]" id="form_user" multiple size="4"><?php UserSelect($form_user, false, '', '', '- ALL -', true); ?></select></td>
         </tr>
         <tr>
           <td class='label'><?php xl('From','e'); ?>: </td>
           <td>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo oeFormatShortDate($form_from_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo $date_title_fmt; ?>' />
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer; vertical-align: top; padding-top: 4px;' title='<?php xl('Click here to choose a date','e'); ?>'></td>
           <td class='label'><?php xl('To','e'); ?>: </td>
           <td>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo oeFormatShortDate($form_to_date); ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo $date_title_fmt; ?>' />
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer; vertical-align: top; padding-top: 4px;' title='<?php xl('Click here to choose a date','e'); ?>'></td>
          <td class='label'><?php xl('Status','e'); ?>: </td>
          <td><?php
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
       </table>

    </div>
  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
      <tr>
        <td>
          <div style='margin-left:15px'>
            <a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").attr("value",""); $("#theform").submit();'>
					<span>
						<?php xl('Submit','e'); ?>
					</span>
					</a>

            <?php if ($_POST['form_refresh'] || $_POST['form_orderby'] || $_POST['form_csvexport']) { ?>
            <a href='#' class='css_button' onclick='window.print()'>
						<span><?php xl('Print','e'); ?></span></a>
					  <a href='#' class='css_button' onclick='$("#form_refresh").attr("value",""); $("#form_csvexport").attr("value","true"); $("#theform").submit();'>
						<span><?php echo xl('CSV Export'); ?></span></a>
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
	if($_POST['form_refresh'] || $_POST['form_orderby']) {
?>
<div id="report_results">
<table>
 <thead>
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
  <th> <?php  xl('Status','e'); ?> </th>
  <th> <?php  xl('Billed','e'); ?> </th>
  <th> <?php  xl('Code','e'); ?> </th>
  <th> <?php  xl('Employee','e'); ?> </th>
  <th> <?php  xl('Insurance','e'); ?> </th>
 </thead>
 <tbody>
<?php 
	}
} // END OF NOT CSV EXPORT

$form_count = 0;
$bgcolor = '#FFFFFF';
if($res) {
  while ($row = sqlFetchArray($res)) {
    $docname = '';
    if (!empty($row['ulname']) || !empty($row['ufname'])) {
      $docname = $row['ulname'];
      if (!empty($row['ufname']) || !empty($row['umname']))
        $docname .= ', ' . $row['ufname'] . ' ' . $row['umname'];
    }
  	$pname = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
    $fstatus = ListLook($row['form_complete'],'Form_Status');
    if(!$fstatus) $status = 'Unassigned';
    $fbilled = ListLook($row['form_priority'],'Form_Bill');
    if(!$fbilled) $fbilled = 'Unassigned';
		$employee = UserLook($row['c2_user']);
		if(!$employee) $employee = 'Not Specified';
		$ins_name = $row['prim_ins_name'];
		if($GLOBALS['wmt::link_appt_ins']) {
			$ins_name = $row['ins_name'] ? $row['ins_name'] : '**' . $ins_name;
			if($ins_name == '**') $ins_name = '';
		}
		if($form_csvexport) {
   		echo '"' . display_desc($row{'ulname'}) . '",';
   		echo '"' . display_desc($row{'ufname'}) . '",';
   		echo '"' . oeFormatShortDate(substr($row{'date'}, 0, 10)) . '",';
   		echo '"' . display_desc($row{'lname'}) . '",';
			echo '"' . display_desc($row{'fname'}) . '",';
   		echo '"' . display_desc($row{'pubpid'}) . '",';
   		echo '"' . display_desc($fstatus) . '",';
   		echo '"' . display_desc($fbilled) . '",'; 
   		echo '"' . display_desc($row{'c2_code'}) . '",'; 
	 		echo '"' . display_desc($employee) . '",';
	 		echo '"' . display_desc($ins_name) . '"';
			echo "\n";
		} else {
?>
 <tr style="background-color: <?php echo $bgcolor ?>;">
  <td><?php echo htmlspecialchars($docname, ENT_QUOTES); ?>&nbsp;</td>
  <td><?php echo oeFormatShortDate(substr($row['date'], 0, 10)) ?>&nbsp;</td>
  <td><?php echo htmlspecialchars($pname, ENT_QUOTES); ?>&nbsp;</td>
  <td><?php echo htmlspecialchars($row['pubpid'], ENT_QUOTES); ?>&nbsp;</td>
	<td>
	<?php if($pop_used) { ?>
		<a href="javascript:;" onclick="ScreenPop('<?php echo $row{'pid'}; ?>', '<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>');">
	<?php } ?>
  <?php echo htmlspecialchars($fstatus, ENT_QUOTES); ?>&nbsp;</td>
	<?php if($pop_used) { ?>
		</a>
	<?php } ?>
	</td>
	<td>
	<?php if($pop_used) { ?>
		<a href="javascript:;" onclick="ScreenPop('<?php echo $row{'pid'}; ?>', '<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>');">
	<?php } ?>
  <?php echo htmlspecialchars($fbilled, ENT_QUOTES); ?>&nbsp;</td>
	<?php if($pop_used) { ?>
		</a>
	<?php } ?>
	</td>
  <td>
	<?php if($pop_used) { ?>
		<a href="javascript:;" onclick="ScreenPop('<?php echo $row{'pid'}; ?>', '<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>');">
	<?php } ?>
   <?php echo htmlspecialchars($row{'c2_code'}, ENT_QUOTES); ?>&nbsp;
	<?php if($pop_used) { ?>
		</a>
	<?php } ?>
  </td>
	<td><?php echo htmlspecialchars($employee, ENT_QUOTES); ?></td>
	<td><?php echo htmlspecialchars($ins_name, ENT_QUOTES); ?></td>
 </tr>
<?php
		}
		$bgcolor = ($bgcolor == '#FFFFFF') ? '#E0E0E0' : '#FFFFFF';
		$form_count++;
  }
} else {  // END OF RESULT LOOP
	echo "<div class='text'>" . xl('Please input search criteria above, and click Submit to view results.', 'e' ) . "</div>";
}

if(!$form_csvexport) {
?>
</tbody>
</table>
</div>  <!-- end encresults -->
<br>
<?php if($form_count) { ?>
<div class='text'>
 	<?php echo xl('Number of Forms Reported', 'e' ); ?>:&nbsp;<?php echo $form_count; ?>
</div>
<?php if($GLOBALS['wmt::link_appt_ins']) { ?>
<br>
<div class="text">**<?php echo xl(' Indicates Primary Insurance As No Insurance Was Linked To An Appointment'); ?>
</div>
<?php } ?>
<?php } ?>

<input type="hidden" name="form_orderby" id="form_orderby" value="<?php echo $form_orderby ?>" />
<input type="hidden" name="form_csvexport" id="form_csvexport" value="" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</body>

<script type='text/javascript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_to_date"});
</script>
</html>
<?php } // END OF NOT CSV EXPORT ?>
