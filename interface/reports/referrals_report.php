<?php
 // Copyright (C) 2008, 2010 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report lists referrals for a given date range.

 require_once("../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/formatting.inc.php");

 $from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
 $to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));
 $form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Referrals','e'); ?></title>

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>

<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<script language="JavaScript">

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 // The OnClick handler for referral display.
 function show_referral(transid) {
  dlgopen('../patient_file/transaction/print_referral.php?transid=' + transid,
   '_blank', 550, 400);
  return false;
 }

</script>

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
</head>

<body class="body_top">

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Referrals','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form name='theform' id='theform' method='post' action='referrals_report.php'>

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
  <td width='640px'>
	<div style='float:left'>

	<table class='text'>
		<tr>
			<td class='label'>
				<?php xl('Facility','e'); ?>:
			</td>
			<td>
				<?php
				 // Build a drop-down list of facilities.
				 //
				 $query = "SELECT id, name FROM facility ORDER BY name";
				 $fres = sqlStatement($query);
				 echo "   <select name='form_facility'>\n";
				 echo "    <option value=''>-- " . xl('All Facilities') . " --\n";
				 while ($frow = sqlFetchArray($fres)) {
				  $facid = $frow['id'];
				  echo "    <option value='$facid'";
				  if ($facid == $form_facility) echo " selected";
				  echo ">" . htmlspecialchars($frow['name']) . "\n";
				 }
				 echo "    <option value='0'";
				 if ($form_facility === '0') echo " selected";
				 echo ">-- " . xl('Unspecified') . " --\n";
				 echo "   </select>\n";
				?>
			</td>
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
 if ($_POST['form_refresh']) {
?>
<div id="report_results">
<table>
 <thead>
  <th> <?php xl('Refer To','e'); ?> </th>
  <th> <?php xl('Refer Date','e'); ?> </th>
  <th> <?php xl('Reply Date','e'); ?> </th>
  <th> <?php xl('Patient','e'); ?> </th>
  <th> <?php xl('ID','e'); ?> </th>
  <th> <?php xl('Reason','e'); ?> </th>
 </thead>
 <tbody>
<?php
 if ($_POST['form_refresh']) {
  $query = "SELECT t.id, t.refer_date, t.reply_date, t.body, " .
    "ut.organization, uf.facility_id, p.pubpid, " .
    "CONCAT(uf.fname,' ', uf.lname) AS referer_name, " .
    "CONCAT(p.fname,' ', p.lname) AS patient_name " .
    "FROM transactions AS t " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = t.pid " .
    "LEFT OUTER JOIN users AS ut ON ut.id = t.refer_to " .
    "LEFT OUTER JOIN users AS uf ON uf.id = t.refer_from " .
    "WHERE t.title = 'Referral' AND " .
    "t.refer_date >= '$from_date' AND t.refer_date <= '$to_date' " .
    "ORDER BY ut.organization, t.refer_date, t.id";

  // echo "<!-- $query -->\n"; // debugging
  $res = sqlStatement($query);

  while ($row = sqlFetchArray($res)) {
    // If a facility is specified, ignore rows that do not match.
    if ($form_facility !== '') {
      if ($form_facility) {
        if ($row['facility_id'] != $form_facility) continue;
      }
      else {
        if (!empty($row['facility_id'])) continue;
      }
    }
?>
 <tr>
  <td>
   <?php echo $row['organization'] ?>
  </td>
  <td>
   <a href='#' onclick="return show_referral(<?php echo $row['id']; ?>)">
   <?php echo oeFormatShortDate($row['refer_date']); ?>&nbsp;
   </a>
  </td>
  <td>
   <?php echo oeFormatShortDate($row['reply_date']) ?>
  </td>
  <td>
   <?php echo $row['patient_name'] ?>
  </td>
  <td>
   <?php echo $row['pubpid'] ?>
  </td>
  <td>
   <?php echo $row['body'] ?>
  </td>
 </tr>
<?php
  }
 }
?>
</tbody>
</table>
</div> <!-- end of results -->
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>
</form>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</body>
</html>
