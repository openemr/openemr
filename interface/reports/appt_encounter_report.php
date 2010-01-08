<?php
 // Copyright (C) 2005-2009 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report cross-references appointments with encounters.
 // For a given date, show a line for each appointment with the
 // matching encounter, and also for each encounter that has no
 // matching appointment.  This helps to catch these errors:
 //
 // * Appointments with no encounter
 // * Encounters with no appointment
 // * Codes not justified
 // * Codes not authorized
 // * Procedure codes without a fee
 // * Fees assigned to diagnoses (instead of procedures)
 // * Encounters not billed
 //
 // For decent performance the following indexes are highly recommended:
 //   openemr_postcalendar_events.pc_eventDate
 //   forms.encounter
 //   billing.pid_encounter

 include_once("../globals.php");
 include_once("../../library/patient.inc");
 include_once("../../custom/code_types.inc.php");

 $errmsg  = "";
 $alertmsg = ''; // not used yet but maybe later
 $grand_total_charges    = 0;
 $grand_total_copays     = 0;
 $grand_total_encounters = 0;

function postError($msg) {
  global $errmsg;
  if ($errmsg) $errmsg .= '<br />';
  $errmsg .= $msg;
}

 function bucks($amount) {
  if ($amount) printf("%.2f", $amount);
 }

 function endDoctor(&$docrow) {
  global $grand_total_charges, $grand_total_copays, $grand_total_encounters;
  if (!$docrow['docname']) return;

  echo " <tr class='report_totals'>\n";
  echo "  <td colspan='5'>\n";
  echo "   &nbsp;" . xl('Totals for','','',' ') . $docrow['docname'] . "\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;" . $docrow['encounters'] . "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;"; bucks($docrow['charges']); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;"; bucks($docrow['copays']); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td colspan='2'>\n";
  echo "   &nbsp;\n";
  echo "  </td>\n";
  echo " </tr>\n";

  $grand_total_charges     += $docrow['charges'];
  $grand_total_copays      += $docrow['copays'];
  $grand_total_encounters  += $docrow['encounters'];

  $docrow['charges']     = 0;
  $docrow['copays']      = 0;
  $docrow['encounters']  = 0;
 }

 $form_facility  = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
 $form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
 $form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
 if ($_POST['form_refresh']) {
  $form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
  $form_to_date = fixDate($_POST['form_to_date'], "");

  // MySQL doesn't grok full outer joins so we do it the hard way.
  //
  $query = "( " .
   "SELECT " .
   "e.pc_eventDate, e.pc_startTime, " .
   "fe.encounter, fe.date AS encdate, " .
   "f.authorized, " .
   "p.fname, p.lname, p.pid, p.pubpid, " .
   "CONCAT( u.lname, ', ', u.fname ) AS docname " .
   "FROM openemr_postcalendar_events AS e " .
   "LEFT OUTER JOIN form_encounter AS fe " .
   "ON LEFT(fe.date, 10) = e.pc_eventDate AND fe.pid = e.pc_pid " .
   "LEFT OUTER JOIN forms AS f ON f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
   "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
   // "LEFT OUTER JOIN users AS u ON BINARY u.username = BINARY f.user WHERE ";
   "LEFT OUTER JOIN users AS u ON u.id = fe.provider_id WHERE ";
  if ($form_to_date) {
   $query .= "e.pc_eventDate >= '$form_from_date' AND e.pc_eventDate <= '$form_to_date' ";
  } else {
   $query .= "e.pc_eventDate = '$form_from_date' ";
  }
  if ($form_facility !== '') {
   $query .= "AND e.pc_facility = '$form_facility' ";
  }
  // $query .= "AND ( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
  $query .= "AND e.pc_pid != '' AND e.pc_apptstatus != '?' " .
   ") UNION ( " .
   "SELECT " .
   "e.pc_eventDate, e.pc_startTime, " .
   "fe.encounter, fe.date AS encdate, " .
   "f.authorized, " .
   "p.fname, p.lname, p.pid, p.pubpid, " .
   "CONCAT( u.lname, ', ', u.fname ) AS docname " .
   "FROM form_encounter AS fe " .
   "LEFT OUTER JOIN openemr_postcalendar_events AS e " .
   "ON LEFT(fe.date, 10) = e.pc_eventDate AND fe.pid = e.pc_pid AND " .
   // "( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
   "e.pc_pid != '' AND e.pc_apptstatus != '?' " .
   "LEFT OUTER JOIN forms AS f ON f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
   "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
   // "LEFT OUTER JOIN users AS u ON BINARY u.username = BINARY f.user WHERE ";
   "LEFT OUTER JOIN users AS u ON u.id = fe.provider_id WHERE ";
  if ($form_to_date) {
   // $query .= "LEFT(fe.date, 10) >= '$form_from_date' AND LEFT(fe.date, 10) <= '$form_to_date' ";
   $query .= "fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_to_date 23:59:59' ";
  } else {
   // $query .= "LEFT(fe.date, 10) = '$form_from_date' ";
   $query .= "fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_from_date 23:59:59' ";
  }
  if ($form_facility !== '') {
   $query .= "AND fe.facility_id = '$form_facility' ";
  }
  $query .= ") ORDER BY docname, pc_eventDate, pc_startTime";

  $res = sqlStatement($query);
 }
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
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
<title><?php  xl('Appointments and Encounters','e'); ?></title>
</head>

<body class="body_top">

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Appointments and Encounters','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' id='theform' action='appt_encounter_report.php'>

<div id="report_parameters">

<table>
 <tr>
  <td width='630px'>
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
				 echo "    <option value=''>-- " . xl('All Facilities', 'e') . " --\n";
				 while ($frow = sqlFetchArray($fres)) {
				  $facid = $frow['id'];
				  echo "    <option value='$facid'";
				  if ($facid == $form_facility) echo " selected";
				  echo ">" . $frow['name'] . "\n";
				 }
				 echo "    <option value='0'";
				 if ($form_facility === '0') echo " selected";
				 echo ">-- " . xl('Unspecified') . " --\n";
				 echo "   </select>\n";
				?>
			</td>
			<td class='label'>
			   <?php xl('DOS','e'); ?>:
			</td>
			<td>
			   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php  echo $form_from_date; ?>'
				title='Date of appointments mm/dd/yyyy' >
			   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
			<td class='label'>
			   <?php xl('To','e'); ?>:
			</td>
			<td>
			   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php  echo $form_to_date; ?>'
				title='Optional end date mm/dd/yyyy' >
			   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
			   <input type='checkbox' name='form_details'
				value='1'<?php if ($_POST['form_details']) echo " checked"; ?>><?php xl('Details','e') ?>
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

</div> <!-- end apptenc_report_parameters -->

<?php
 if ($_POST['form_refresh'] ) {
?>
<div id="report_results">
<table>

 <thead>
  <th> &nbsp;<?php  xl('Practitioner','e'); ?> </th>
  <th> &nbsp;<?php  xl('Date/Appt','e'); ?> </th>
  <th> &nbsp;<?php  xl('Patient','e'); ?> </th>
  <th> &nbsp;<?php  xl('ID','e'); ?> </th>
  <th> <?php  xl('Chart','e'); ?>&nbsp; </th>
  <th> <?php  xl('Encounter','e'); ?>&nbsp; </th>
  <th> <?php  xl('Charges','e'); ?>&nbsp; </th>
  <th> <?php  xl('Copays','e'); ?>&nbsp; </th>
  <th> <?php  xl('Billed','e'); ?> </th>
  <th> &nbsp;<?php  xl('Error','e'); ?> </th>
 </thead>
 <tbody>
<?php
 if ($res) {
  $docrow = array('docname' => '', 'charges' => 0, 'copays' => 0, 'encounters' => 0);

  while ($row = sqlFetchArray($res)) {
   $patient_id = $row['pid'];
   $encounter  = $row['encounter'];
   $docname    = $row['docname'] ? $row['docname'] : xl('Unknown');

   if ($docname != $docrow['docname']) {
    endDoctor($docrow);
   }

   $errmsg  = "";
   $billed  = "Y";
   $charges = 0;
   $copays  = 0;
   $gcac_related_visit = false;

   // Scan the billing items for status and fee total.
   //
   $query = "SELECT code_type, code, modifier, authorized, billed, fee, justify " .
    "FROM billing WHERE " .
    "pid = '$patient_id' AND encounter = '$encounter' AND activity = 1";
   $bres = sqlStatement($query);
   //
   while ($brow = sqlFetchArray($bres)) {
    $code_type = $brow['code_type'];
    if ($code_types[$code_type]['fee'] && !$brow['billed'])
      $billed = "";
    if (!$GLOBALS['simplified_demographics'] && !$brow['authorized'])
      postError(xl('Needs Auth'));
    if ($code_types[$code_type]['just']) {
     if (! $brow['justify']) postError(xl('Needs Justify'));
    }
    if ($code_type == 'COPAY') {
     $copays -= $brow['fee'];
     if ($brow['fee'] >= 0) postError(xl('Copay not positive'));
    } else if ($code_types[$code_type]['fee']) {
     $charges += $brow['fee'];
     if ($brow['fee'] == 0 && !$GLOBALS['ippf_specific']) postError(xl('Missing Fee'));
    } else {
     if ($brow['fee'] != 0) postError(xl('Fee is not allowed'));
    }

    // Custom logic for IPPF to determine if a GCAC issue applies.
    if ($GLOBALS['ippf_specific']) {
      if (!empty($code_types[$code_type]['fee'])) {
        $query = "SELECT related_code FROM codes WHERE code_type = '" .
          $code_types[$code_type]['id'] . "' AND " .
          "code = '" . $brow['code'] . "' AND ";
        if ($brow['modifier']) {
          $query .= "modifier = '" . $brow['modifier'] . "'";
        } else {
          $query .= "(modifier IS NULL OR modifier = '')";
        }
        $query .= " LIMIT 1";
        $tmp = sqlQuery($query);
        $relcodes = explode(';', $tmp['related_code']);
        foreach ($relcodes as $codestring) {
          if ($codestring === '') continue;
          list($codetype, $code) = explode(':', $codestring);
          if ($codetype !== 'IPPF') continue;
          if (preg_match('/^25222/', $code)) $gcac_related_visit = true;
        }
      }
    } // End IPPF stuff

   } // end while

   // The following is removed, perhaps temporarily, because gcac reporting
   // no longer depends on gcac issues.  -- Rod 2009-08-11
   /******************************************************************
   // More custom code for IPPF.  Generates an error message if a
   // GCAC issue is required but is not linked to this visit.
   if (!$errmsg && $gcac_related_visit) {
    $grow = sqlQuery("SELECT l.id, l.title, l.begdate, ie.pid " .
      "FROM lists AS l " .
      "LEFT JOIN issue_encounter AS ie ON ie.pid = l.pid AND " .
      "ie.encounter = '$encounter' AND ie.list_id = l.id " .
      "WHERE l.pid = '$patient_id' AND " .
      "l.activity = 1 AND l.type = 'ippf_gcac' " .
      "ORDER BY ie.pid DESC, l.begdate DESC LIMIT 1");
    // Note that reverse-ordering by ie.pid is a trick for sorting
    // issues linked to the encounter (non-null values) first.
    if (empty($grow['pid'])) { // if there is no linked GCAC issue
      if (empty($grow)) { // no GCAC issue exists
        $errmsg = "GCAC issue does not exist";
      }
      else { // there is one but none is linked
        $errmsg = "GCAC issue is not linked";
      }
    }
   }
   ******************************************************************/
   if ($gcac_related_visit) {
      $grow = sqlQuery("SELECT COUNT(*) AS count FROM forms " .
        "WHERE pid = '$patient_id' AND encounter = '$encounter' AND " .
        "deleted = 0 AND formdir = 'LBFgcac'");
      if (empty($grow['count'])) { // if there is no gcac form
        postError(xl('GCAC visit form is missing'));
      }
   } // end if
   /*****************************************************************/

   if (!$billed) postError($GLOBALS['simplified_demographics'] ?
     xl('Not checked out') : xl('Not billed'));
   if (!$encounter) postError(xl('No visit'));

   if (! $charges) $billed = "";

   $docrow['charges'] += $charges;
   $docrow['copays']  += $copays;
   if ($encounter) ++$docrow['encounters'];

   if ($_POST['form_details']) {
?>
 <tr>
  <td>
   &nbsp;<?php  echo ($docname == $docrow['docname']) ? "" : $docname ?>
  </td>
  <td>
   &nbsp;<?php
    /*****************************************************************
    if ($form_to_date) {
        echo $row['pc_eventDate'] . '<br>';
        echo substr($row['pc_startTime'], 0, 5);
    }
    *****************************************************************/
    if (empty($row['pc_eventDate'])) {
      echo substr($row['encdate'], 0, 10);
    }
    else {
      echo $row['pc_eventDate'] . ' ' . substr($row['pc_startTime'], 0, 5);
    }
    ?>
  </td>
  <td>
   &nbsp;<?php  echo $row['fname'] . " " . $row['lname'] ?>
  </td>
  <td>
   &nbsp;<?php  echo $row['pubpid'] ?>
  </td>
  <td align='right'>
   <?php  echo $row['pid'] ?>&nbsp;
  </td>
  <td align='right'>
   <?php  echo $encounter ?>&nbsp;
  </td>
  <td align='right'>
   <?php  bucks($charges) ?>&nbsp;
  </td>
  <td align='right'>
   <?php  bucks($copays) ?>&nbsp;
  </td>
  <td>
   <?php  echo $billed ?>
  </td>
  <td style='color:#cc0000'>
   <?php echo $errmsg; ?>&nbsp;
  </td>
 </tr>
<?php
   } // end of details line

   $docrow['docname'] = $docname;
  } // end of row

  endDoctor($docrow);

  echo " <tr class='report_totals'>\n";
  echo "  <td colspan='5'>\n";
  echo "   &nbsp;" . xl('Grand Totals') . "\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;" . $grand_total_encounters . "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;"; bucks($grand_total_charges); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;"; bucks($grand_total_copays); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td colspan='2'>\n";
  echo "   &nbsp;\n";
  echo "  </td>\n";
  echo " </tr>\n";

 }
?>
</tbody>
</table>
</div> <!-- end the apptenc_report_results -->
<?php } else { ?>
<div class='text'>
 	<?php echo xl('Please input search criteria above, and click Submit to view results.', 'e' ); ?>
</div>
<?php } ?>

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
<script>
<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>
</script>
</body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
