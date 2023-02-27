<?php
// Copyright (C) 2017-2020 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once($GLOBALS['srcdir']."/options.inc.php");
require_once($GLOBALS['srcdir']."/patient.inc");

use OpenEMR\Core\Header;

if(!isset($_POST['form_csvexport'])) $_POST['form_csvexport'] = '';
if(!isset($_POST['form_refresh'])) $_POST['form_refresh'] = '';
if(!isset($_POST['form_provider'])) $_POST['form_provider'] = '';
if(!isset($_POST['form_facility'])) $_POST['form_facility'] = '';
$rpt_lines = 0;

function bucks($amount) {
  if ($amount) echo oeFormatMoney($amount);
}

function display_desc($desc) {
  if (preg_match('/^\S*?:(.+)$/', $desc, $matches)) {
    $desc = $matches[1];
  }
  return $desc;
}

$default_date = fixDate(date('Y-m-d'), date('Y-m-d'));
$last_month = mktime(0, 0, 0, date('m') - 1, date('d'), date('Y'));
$default_start = fixDate(date('Y-m-d'), $last_month);
if(!isset($_POST['form_from_date'])) $_POST['form_from_date'] = $default_start;
if(!isset($_POST['form_to_date'])) $_POST['form_to_date'] = $default_date;
$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_facility  = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_provider = isset($_POST['form_provider']) ? $_POST['form_provider'] : '';
$form_csvexport = $_POST['form_csvexport'];
$form_details = 1;

if($form_csvexport) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=pats_declined_rto.csv");
  header("Content-Description: File Transfer");
  if ($form_details) {
		echo '"PID",';
		echo '"Patient Name",';
		echo '"Work Phone",';
		echo '"Home Phone",';
		echo '"Cell Phone",';
    echo '"Encounter",';
    echo '"Provider",';
    echo '"Service Dt"' . "\n";
  }
	// End of Export
} else {
?>
<html>
<head>
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
    #report_results {
       margin-top: 30px;
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

<title><?php echo xl('Patients Who Declined Further Treatment') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0' class="body_top">

<span class='title'><?php echo xl('Report'); ?> - <?php echo xl('Patients Who Declined Further Treatment'); ?></span>

<form method='post' action='pat_declined_rto.php' id='theform'>

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>
<table>
 <tr>
  <td width='600px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'>
				<?php echo xl('Facility'); ?>:
			</td>
			<td>
			<?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', true); ?>
			</td>
			<td class='label'>
			   <?php echo xl('From'); ?>:
			</td>
			<td>
			   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php echo xl('Click here to choose a date'); ?>'>
			</td>
		</tr>
		<tr>
      <td class='label'><?php xl('Provider','e'); ?>: </td>
      <td><?php
      // Build a drop-down list of providers.
      $query = "SELECT id, username, lname, fname FROM users " .
			 "WHERE authorized=1 AND username!='' AND active='1' ".
			 "AND (UPPER(specialty) LIKE '%PROVIDER%' OR ".
			 "UPPER(specialty) LIKE '%SUPERVISOR%') ORDER BY lname, fname";
      $ures = sqlStatement($query);

      echo "   <select name='form_provider'>\n";
      echo "    <option value=''";
			if($form_provider == '') echo 'selected="selected"';
			echo ">-- " . xl('All') . " --</option>\n";
      while ($urow = sqlFetchArray($ures)) {
        $provid = $urow['id'];
        echo "    <option value='$provid'";
        if ($provid == $form_provider) echo " selected";
        echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
     }
     echo "   </select>\n";
     ?></td>
			<td class='label'>
			   <?php echo xl('To'); ?>:
			</td>
			<td>
			   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php echo xl('Click here to choose a date'); ?>'>
			</td>
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
					<span><?php echo xl('Submit'); ?></span></a>

					<?php if ($_POST['form_refresh'] || $_POST['form_csvexport']) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span><?php echo xl('Print'); ?></span></a>
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

</div> <!-- end of parameters -->

<?php
	if($_POST['form_refresh']) {
?>
	<div id="report_results">
	<table >
 	<thead>
  	<th><?php xl('PID', 'e'); ?> </th>
  	<th><?php xl('Patient Name', 'e'); ?> </th>
  	<th><?php xl('Work Phone', 'e'); ?> </th>
  	<th><?php xl('Home Phone', 'e'); ?> </th>
  	<th><?php xl('Cell Phone', 'e'); ?> </th>
  	<th><?php xl('Encounter', 'e'); ?> </th>
  	<th><?php xl('Provider', 'e'); ?> </th>
  	<th><?php xl('Service Dt', 'e'); ?> </th>
 	</thead>
<?php
	}
} // end not export

if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;

  $query = 'SELECT fee.decline_next, ' .
		'forms.encounter, forms.form_id, forms.pid, ' .
		'SUBSTRING(fe.date, 1, 10) AS serv_dt, fe.facility_id, fe.provider_id, ' .
		'pat.lname AS plast, pat.fname AS pfirst, pat.mname AS pmi, ' .
		'pat.phone_home, pat.phone_biz, pat.phone_cell, pat.pubpid, ' .
		'dr.lname AS drlast, dr.fname AS drfirst, dr.mname AS drmi ' .
    'FROM forms ' .
    'JOIN form_encounter AS fe USING (encounter) ' .
		'LEFT JOIN patient_data AS pat ON forms.pid = pat.pid ' .
		'LEFT JOIN users AS dr ON fe.provider_id = dr.id ' .
		'LEFT JOIN form_definable_fee AS fee ON form_id = fee.id ' .
    'WHERE forms.formdir = "definable_fee" AND forms.deleted = 0 AND ' .
		'fee.decline_next = 1 AND ' .
  	"fe.date >= '$from_date 00:00:00' AND fe.date <= '$to_date 23:59:59'";
  if ($form_facility) $query .= " AND fe.facility_id = '$form_facility'"; 
  if ($form_provider) $query .= " AND fe.provider_id = '$form_provider'"; 
	$query .= ' ORDER BY pat.pubpid, serv_dt ASC';
  $res = sqlStatement($query);
  while ($row = sqlFetchArray($res)) {
		$user_desc = $row['drlast'].', '.$row['drfirst'];
		$pat_desc = $row['plast'].', '.$row['pfirst'];
		if($_POST['form_csvexport']) {
			echo '"',$row{'pubpid'},'","';
			echo display_desc($pat_desc), '","';
			echo display_desc($row{'phone_biz'}), '","';
			echo display_desc($row{'phone_home'}), '","';
			echo display_desc($row{'phone_cell'}), '","';
			echo $row{'encounter'}. '","';
			echo display_desc($user_desc), '","';
			echo $row{'serv_dt'}, '"';
			echo "\n";
		} else {
		?>
	<tr>
		<td><?php echo $row{'pubpid'}; ?>&nbsp;</td>
		<td><a href="javascript: goPid('<?php echo $row{'pid'}; ?>');"><?php echo display_desc($pat_desc); ?>&nbsp;</a></td>
		<td><?php echo display_desc($row{'phone_biz'}); ?>&nbsp;</td>
		<td><?php echo display_desc($row{'phone_home'}); ?>&nbsp;</td>
		<td><?php echo display_desc($row{'phone_cell'}); ?>&nbsp;</td>
		<td><?php echo $row{'encounter'}; ?>&nbsp;</td>
		<td><?php echo display_desc($user_desc); ?>&nbsp;</td>
		<td><?php echo $row{'serv_dt'}; ?>&nbsp;</td>
	</tr>
		<?php
		}
		$rpt_lines++;
  }

	if(!$form_user && !$form_csvexport) {
	?>
 	<tr bgcolor="#ddffff">
	 <td>&nbsp;</td>
 	 <td class="detail"><?php echo xl('Grand Total'); ?></td>
	 <td>&nbsp;</td>
	 <td>&nbsp;</td>
	 <td>&nbsp;</td>
	 <td>&nbsp;</td>
	 <td>&nbsp;</td>
 	 <td><?php echo $rpt_lines; ?></td>
 	</tr>

<?php
	}
}

if(!$_POST['form_csvexport']) {
?>

</table>
</div> <!-- report results -->
	<?php if(!$rpt_lines) { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
	<?php } ?>

</form>

</body>

<!-- stuff for the popup calendar -->
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>

<?php Header::setupHeader(['datetime-picker', 'jquery', 'jquery-ui']); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/wmt/wmtcalendar.js.php"></script>

<script type="text/javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});

function goPid(pid) {
	if( (window.opener) && (window.opener.setPatient) ) {
		window.opener.loadFrame('RTop', 'RTop', 'patient_file/summary/demographics.php?set_pid=' + pid);
	} else if( (parent.left_nav) && (parent.left_nav.loadFrame) ) {
		parent.left_nav.loadFrame('RTop', 'RTop', 'patient_file/summary/demographics.php?set_pid=' + pid);
	} else {
		var newWin = window.open('../../main/main_screen.php?patientID=' + pid);
	}
}
</script>

</html>
<?php
} // End not csv export
?>
