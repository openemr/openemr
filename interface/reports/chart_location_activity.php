<?php
// Copyright (C) 2008-2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This reports checkins and checkouts for a specified patient's chart.
$fake_register_globals=false;
$sanitize_all_escapes=true;

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formatting.inc.php");

$form_patient_id = trim($_POST['form_patient_id']);
?>
<html>
<head>
<?php html_header_show(); ?>
<title><?php echo xlt('Chart Location Activity'); ?></title>

<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
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

<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

</head>

<body class="body_top">

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Chart Location Activity'); ?></span>

<?php
$curr_pid = $pid;
$ptrow = array();
if (!empty($form_patient_id)) {
  $query = "SELECT pid, pubpid, fname, mname, lname FROM patient_data WHERE " .
    "pubpid = ? ORDER BY pid LIMIT 1";
  $ptrow = sqlQuery($query,array($form_patient_id));
  if (empty($ptrow)) {
    $curr_pid = 0;
    echo "<font color='red'>" . xlt('Chart ID') . " '" . text($form_patient_id) . "' " . xlt('not found!') . "</font><br />&nbsp;<br />";
  }
  else {
    $curr_pid = $ptrow['pid'];
  }
}
else if (!empty($curr_pid)) {
  $query = "SELECT pid, pubpid, fname, mname, lname FROM patient_data WHERE " .
    "pid = ?";
  $ptrow = sqlQuery($query,array($curr_pid));
  $form_patient_id = $ptrow['pubpid'];
}
if (!empty($ptrow)) {
  echo '<span class="title">' . text(xl('for','','',' '));
  echo text($ptrow['lname']) . ', ' . text($ptrow['fname']) . ' ' . text($ptrow['mname']) . ' ';
  echo "(" . text($ptrow['pubpid']) . ")";
  echo "</span>\n";
}
?>

<div id="report_parameters_daterange">
</div>

<form name='theform' id='theform' method='post' action='chart_location_activity.php'>

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
  <td width='200px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'>
			   <?php echo xlt('Patient ID'); ?>:
			</td>
			<td>
			   <input type='text' name='form_patient_id' size='10' maxlength='31' value='<?php echo attr($form_patient_id) ?>'
				title='<?php echo xla('Patient ID'); ?>' />
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
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php echo xlt('Submit'); ?>
					</span>
					</a>

					<?php if ($_POST['form_refresh'] || !empty($ptrow) ) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php echo xlt('Print'); ?>
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

</div> <!-- end of parameters -->

<?php
 if ($_POST['form_refresh'] || !empty($ptrow) ) {
?>
<div id="report_results">
<table>
 <thead>
  <th> <?php echo xlt('Time'); ?> </th>
  <th> <?php echo xlt('Destination'); ?> </th>
 </thead>
 <tbody>
<?php
$row = array();
if (!empty($ptrow)) {
  $query = "SELECT ct.ct_when, ct.ct_userid, ct.ct_location, " .
    "u.username, u.fname, u.mname, u.lname " .
    "FROM chart_tracker AS ct " .
    "LEFT OUTER JOIN users AS u ON u.id = ct.ct_userid " .
    "WHERE ct.ct_pid = ? " .
    "ORDER BY ct.ct_when DESC";
  $res = sqlStatement($query,array($curr_pid));

  while ($row = sqlFetchArray($res)) {
?>
 <tr>
  <td>
   <?php echo text(oeFormatShortDate(substr($row['ct_when'], 0, 10))) . text(substr($row['ct_when'], 10)); ?>
  </td>
  <td>
<?php
    if (!empty($row['ct_location'])) {
      echo generate_display_field(array('data_type'=>'1','list_id'=>'chartloc'),$row['ct_location']);
    }
    else if (!empty($row['ct_userid'])) {
      echo text($row['lname']) . ', ' . text($row['fname']) . ' ' . text($row['mname']);
    }
?>
  </td>
 </tr>
<?php
  } // end while
 } // end if
?>
</tbody>
</table>
</div> <!-- end of results -->
<?php } else { ?>
<div class='text'>
 	<?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>

</form>
</body>
</html>
