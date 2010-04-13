<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

 // This module shows relative insurance usage by unique patients
 // that are seen within a given time period.  Each patient that had
 // a visit is counted only once, regardless of how many visits.

 include_once("../globals.php");
 include_once("../../library/patient.inc");
 include_once("../../library/acl.inc");
 require_once("../../library/formatting.inc.php");

 // Might want something different here.
 //
 // if (! acl_check('acct', 'rep')) die("Unauthorized access.");

 $from_date = fixDate($_POST['form_from_date']);
 $to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));

if ($_POST['form_csvexport']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=insurance_distribution.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  if (true) {
    echo '"Insurance",';
    echo '"Charges",';
    echo '"Visits",';
    echo '"Patients",';
    echo '"Pt Pct"' . "\n";
  }
}
else {
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Patient Insurance Distribution','e'); ?></title>
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
<script language="JavaScript">
 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
</script>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
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
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Patient Insurance Distribution','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form name='theform' method='post' action='insurance_allocation_report.php' id='theform'>

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>

<table>
 <tr>
  <td width='410px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'>
			   <?php xl('From','e'); ?>:
			</td>
			<td>
			   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
			<td class='label'>
			   <?php xl('To','e'); ?>:
			</td>
			<td>
			   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
				onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
			   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
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
						<?php xl('Submit','e'); ?>
					</span>
					</a>

					<?php if ($_POST['form_refresh']) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php xl('Print','e'); ?>
						</span>
					</a>
					<a href='#' class='css_button' onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>
					<span>
						<?php xl('Export to CSV','e'); ?>
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

</form>
</div> <!-- end parameters -->

<div id="report_results">
<table>

 <thead>
  <th align='left'> <?php xl('Primary Insurance','e'); ?> </th>
  <th align='right'> <?php xl('Charges','e'); ?> </th>
  <th align='right'> <?php xl('Visits','e'); ?> </th>
  <th align='right'> <?php xl('Patients','e'); ?> </th>
  <th align='right'> <?php xl('Pt %','e'); ?> </th>
 </thead>
 <tbody>
<?php
} // end not export
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {

  $from_date = fixDate($_POST['form_from_date']);
  $to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));

  $query = "SELECT b.pid, b.encounter, SUM(b.fee) AS charges, " .
    "MAX(fe.date) AS date " .
    "FROM form_encounter AS fe, billing AS b " .
    "WHERE fe.date >= '$from_date' AND fe.date <= '$to_date' " .
    "AND b.pid = fe.pid AND b.encounter = fe.encounter " .
    "AND b.code_type != 'COPAY' AND b.activity > 0 AND b.fee != 0 " .
    "GROUP BY b.pid, b.encounter ORDER BY b.pid, b.encounter";

  $res = sqlStatement($query);
  $insarr = array();
  $prev_pid = 0;
  $patcount = 0;

  while ($row = sqlFetchArray($res)) {
    $patient_id = $row['pid'];
    $encounter_date = $row['date'];
    $irow = sqlQuery("SELECT insurance_companies.name " .
      "FROM insurance_data, insurance_companies WHERE " .
      "insurance_data.pid = $patient_id AND " .
      "insurance_data.type = 'primary' AND " .
      "insurance_data.date <= '$encounter_date' AND " .
      "insurance_companies.id = insurance_data.provider " .
      "ORDER BY insurance_data.date DESC LIMIT 1");
    $plan = $irow['name'] ? $irow['name'] : '-- No Insurance --';
    $insarr[$plan]['visits'] += 1;
    $insarr[$plan]['charges'] += sprintf('%0.2f', $row['charges']);
    if ($patient_id != $prev_pid) {
      ++$patcount;
      $insarr[$plan]['patients'] += 1;
      $prev_pid = $patient_id;
    }
  }

  ksort($insarr);

  while (list($key, $val) = each($insarr)) {
    if ($_POST['form_csvexport']) {
        echo '"' . $key                                                . '",';
        echo '"' . oeFormatMoney($val['charges'])                      . '",';
        echo '"' . $val['visits']                                      . '",';
        echo '"' . $val['patients']                                    . '",';
        echo '"' . sprintf("%.1f", $val['patients'] * 100 / $patcount) . '"' . "\n";
    }
    else {
?>
 <tr>
  <td>
   <?php echo $key ?>
  </td>
  <td align='right'>
   <?php echo oeFormatMoney($val['charges']) ?>
  </td>
  <td align='right'>
   <?php echo $val['visits'] ?>
  </td>
  <td align='right'>
   <?php echo $val['patients'] ?>
  </td>
  <td align='right'>
   <?php printf("%.1f", $val['patients'] * 100 / $patcount) ?>
  </td>
 </tr>
<?php
    } // end not export
  } // end while
} // end if

if (! $_POST['form_csvexport']) {
?>

</tbody>
</table>
</div> <!-- end of results -->

</body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>
</html>
<?php
} // end not export
?>
