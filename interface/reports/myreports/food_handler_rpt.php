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
require_once($GLOBALS['srcdir'].'/billing.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/formatting.inc.php');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');

use OpenEMR\Core\Header;

$alertmsg = ''; // not used yet but maybe later

$ORDERHASH = array(
  'doctor'  => 'lower(u.lname), lower(u.fname), fe.date',
  'patient' => 'lower(p.lname), lower(p.fname), fe.date',
  'pubpid'  => 'lower(p.pubpid), fe.date',
  'time'    => 'fe.date, lower(u.lname), lower(u.fname)',
);
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
$form_results  = 0;
$form_no_results  = 0;
$create         = '';
if(isset($_POST['form_provider'])) $form_provider = $_POST['form_provider'];
if(isset($_POST['form_facility'])) $form_facility = $_POST['form_facility'];
if(isset($_POST['form_status'])) $form_complete = $_POST['form_status'];
if(isset($_POST['form_results'])) $form_results = $_POST['form_results'];
if(isset($_POST['form_no_results'])) $form_no_results = $_POST['form_no_results'];
if(isset($_POST['form_partial_results'])) $form_partial_results = $_POST['form_partial_results'];
if(isset($_GET['create'])) $create = strip_tags($_GET['create']);
$form_details   = '1';

$form_orderby = 'doctor';
if(isset($_REQUEST['form_orderby'])) $form_orderby = $_REQUEST['form_orderby'];
$orderby = $ORDERHASH[$form_orderby];

$columns = array("Provider", "Date", "Patient Name", "Patient ID", 
	"Nasal Swab Result", "Stool Sample Result", "Certificate Printed");
$start_date = $form_from_date . ' 00:00:00';
$end_date = $form_to_date . ' 23:59:59';
$binds = array();

$query = "SELECT " .
  "f.formdir, f.form_name, f.deleted, f.form_id, f.user, " .
  "fe.encounter, fe.date, fe.reason, " .
	"fh.*, " .
  "p.fname, p.mname, p.lname, p.pubpid, p.pid, " .
  "u.lname AS ulname, u.fname AS ufname, u.mname AS umname, " .
  "u.username FROM forms AS f " .
  "LEFT JOIN form_encounter AS fe USING (encounter) " .
  "LEFT JOIN users AS u ON fe.provider_id = u.id " .
  "LEFT JOIN patient_data AS p ON f.pid = p.pid " .
  "LEFT JOIN form_food_handler AS fh ON f.form_id = fh.id " .
  "WHERE " .
  "f.deleted != '1' AND f.formdir = 'food_handler' ";
if ($form_to_date) {
  $query .= "AND fe.date >= ? AND fe.date <= ? ";
	$binds[] = $start_date;
	$binds[] = $end_date;
} else {
  $query .= "AND fe.date >= ? AND fe.date <= ? ";
	$binds[] = $start_date;
	$binds[] = $end_date;
}
if ($form_provider !== '') {
  $query .= "AND fe.provider_id = ? ";
	$binds[] = $form_provider;
}
if ($form_facility) {
  $query .= "AND fe.facility_id = ?";
	$binds[] = $form_facility;
}

$query .= "ORDER BY $orderby";

$res=array();
if(isset($_GET['mode'])) { 
	set_time_limit(0);
	$res = sqlStatement($query, $binds);
	$cnt = sqlNumRows($res);
}

// echo "Query: $query<br>\n";
if($create == 'csv' && $res) {
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=testforms.csv');
	$output = fopen('php://output', 'w');
	fputcsv($output, $columns);
 	while ($row = sqlFetchArray($res)) {

    $docname = '';
    if (!empty($row['ulname']) || !empty($row['ufname'])) {
      $docname = $row['ulname'];
      if (!empty($row['ufname']) || !empty($row['umname']))
        $docname .= ', ' . $row['ufname'] . ' ' . $row['umname'];
    }
		if($form_complete) {
			if($dres{'form_complete'} != $form_complete) continue;
		}
		if ($form_results) {
			if(!$row{'a_result'} || !$row{'b_result'}) continue;
		}
		if ($form_no_results) {
			if($row{'a_result'} || $row{'b_result'}) continue;
		}
		if ($form_partial_results) {
			if(($row{'a_result'} && !$row{'b_result'}) || 
					($row{'b_result'} && !$row{'a_result'})) continue;
		}
		$a = ListLook($row{'a_result'},'Pos_Neg','');
		$b = ListLook($row{'b_result'},'Pos_Neg','');

		$referral = 'No';
		if($dres['referral'] == 1 || 
			strtolower(substr($dres{'referral'},0,1)) == 'y') $referral = 'Yes';
		$fdate = oeFormatShortDate(substr($row['date'], 0, 10));
		$patname = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
		unset($data);
		$data = array($docname, $fdate, $patname, $row{'pubpid'}, $a, $b, $referral);
		fputcsv($output, $data);
	}
	fclose($output);
} else {

?>
<html>
<head>
<title><?php xl('Food Handler Forms Report','e'); ?></title>
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

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/report_tools.js"></script>

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

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Food Handler Forms','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' name='theform' id='theform' action='food_handler_rpt.php?mode=search'>

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
					<td colspan="2" class="label" style="text-align: left;"><input name="form_partial_results" id="form_partial_results" type="checkbox" value="1" <?php echo $form_partial_results ? 'checked' : ''; ?> onclick="ToggleTrio('form_partial_results','form_no_results','form_results');" /><label for="form_partial_results">&nbsp;&nbsp;Only Report Forms With Partial Results</label></td>
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
					<td colspan="2" class="label" style="text-align: left;"><input name="form_results" id="form_results" type="checkbox" value="1" <?php echo $form_results ? 'checked' : ''; ?> onclick="ToggleTrio('form_results','form_no_results','form_partial_results');" /><label for="form_results">&nbsp;&nbsp;Only Report Forms With a All Results</label></td>
        </tr>
				<tr>
           <td colspan="2"><span class='label'><?php xl('From','e'); ?>: </span>
             <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'>&nbsp;&nbsp;
           <span class='label'><?php xl('To','e'); ?>: </span>
             <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>' onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
             <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php xl('Click here to choose a date','e'); ?>'></td>
					<td colspan="2" class="label" style="text-align: left;"><input name="form_no_results" id="form_no_results" type="checkbox" value="1" <?php echo $form_no_results ? 'checked' : ''; ?> onchange="ToggleTrio('form_no_results','form_results','form_partial_results');" /><label for="form_no_results">&nbsp;&nbsp;Only Report Forms With No Results</label></td>
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
$doc_encounters = 0;
$with = $with_no = $with_partial = 0;
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
   <?php xl('Status','e'); ?>
  </th>
  <th>
   <?php xl('Nasal Swab Result','e'); ?>
  </th>
  <th>
   <?php xl('Stool Sample Result','e'); ?>
  </th>
  <th>
   <?php xl('Certificate Printed','e'); ?>
  </th>
<?php } ?>
 </thead>
 <tbody>
<?php
if ($res) {
	$bgcolor = '#ffdddd';
  $lastdocname = "";
  while ($row = sqlFetchArray($res)) {

    $docname = '';
    if (!empty($row['ulname']) || !empty($row['ufname'])) {
      $docname = $row['ulname'];
      if (!empty($row['ufname']) || !empty($row['umname']))
        $docname .= ', ' . $row['ufname'] . ' ' . $row['umname'];
    }
		if ($form_results) {
			if(!$row{'a_result'} || !$row{'b_result'}) continue;
		}
		if ($form_no_results) {
			if($row{'a_result'} || $row{'b_result'}) continue;
		}
		if ($form_partial_results) {
			if(($row{'a_result'} && !$row{'b_result'}) || 
					($row{'b_result'} && !$row{'a_result'})) continue;
		}
		$a = ListLook($row{'a_result'},'Pos_Neg','');
		$b = ListLook($row{'b_result'},'Pos_Neg','');
		if($a && $b) {
			$with++;
		} else if(!$a && !$b) {
			$with_no++;
		} else {
			$with_partial++;
		}

    $errmsg  = "";
    $fstatus = ListLook($row['form_complete'],'Form_Status');
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
	<?php if($pop_used) { ?>
		<a href="javascript:;" onclick="FormPop('<?php echo $row{'pid'}; ?>', '<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', '<?php echo $row{'formdir'}; ?>');">
	<?php } ?>
   <?php echo $fstatus; ?>&nbsp;
	<?php if($pop_used) { ?>
		</a>
	<?php } ?>
  </td>
  <td>
	<?php if($pop_used) { ?>
		<a href="javascript:;" onclick="FormPop('<?php echo $row{'pid'}; ?>', '<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', '<?php echo $row{'formdir'}; ?>');">
	<?php } ?>
   <?php echo $a; ?>&nbsp;
	<?php if($pop_used) { ?>
		</a>
	<?php } ?>
  </td>
  <td>
	<?php if($pop_used) { ?>
		<a href="javascript:;" onclick="FormPop('<?php echo $row{'pid'}; ?>', '<?php echo $row{'form_id'}; ?>', '<?php echo $row{'encounter'}; ?>', '<?php echo $row{'formdir'}; ?>');">
	<?php } ?>
   <?php echo $b; ?>&nbsp;
	<?php if($pop_used) { ?>
		</a>
	<?php } ?>
  </td>
	<td><?php echo $referrall ?></td>
 </tr>
<?php
    $lastdocname = $docname;
		$doc_encounters++;
  }
}
?>
</tbody>
</table>
</div>  <!-- end encresults -->
<?php } ?>
<br>
<?php if($doc_encounters) { ?>
<div class='text'>
 	<?php echo xl('Number of Forms Reported', 'e' ); ?>:&nbsp;<?php echo $doc_encounters; ?>
</div><br>
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>
<?php if($with_no) { ?>
<div class='text'>
 	<?php echo xl('Number with No Results', 'e' ); ?>:&nbsp;<?php echo $with_no; ?>
</div><br>
<?php } ?>
<?php if($with_partial) { ?>
<div class='text'>
 	<?php echo xl('Number with Partial Results', 'e' ); ?>:&nbsp;<?php echo $with_partial; ?>
</div><br>
<?php } ?>
<?php if($with) { ?>
<div class='text'>
 	<?php echo xl('Number with Full Results', 'e' ); ?>:&nbsp;<?php echo $with; ?>
</div>
<?php } ?>

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
</body>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtpopup.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtstandard.popup.js"></script>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

<?php if ($alertmsg) echo " alert('$alertmsg');\n"; ?>
function formCreateCSV() {
	var my_action = "<?php echo $GLOBALS['webroot']; ?>/interface/reports/myreports/food_handler_rpt.php?form_from_date=<?php echo $form_from_date; ?>&form_to_date=<?php echo $form_to_date; ?>&create=csv&mode=search";
	var my_action = "<?php echo $GLOBALS['webroot']; ?>/interface/reports/myreports/food_handler_rpt.php?create=csv&mode=search";
	document.forms[0].action = my_action;
	document.forms[0].submit();
}

</script>

</html>
<?php } ?>
