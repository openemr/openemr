<?php
// Copyright (C) 2015-2018 Williams Medical Technologies (WMT)
// Author: Rich Genandt - <rgenandt@gmail.com> <rich@williamsmedtech.net>
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

require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/wmt-v2/list_tools.inc");
require_once("../../../custom/code_types.inc.php");

use OpenEMR\Core\Header;

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
  if ($amount) echo oeFormatMoney($amount);
 }

 function endDoctor(&$docrow) {
  global $grand_total_charges, $grand_total_copays, $grand_total_encounters;
  if (!$docrow['docname']) return;

  echo " <tr class='report_totals'>\n";
  echo "  <td colspan='4'>\n";
  echo "   &nbsp;" . xl('Totals for','','',' ') . $docrow['docname'] . "\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;" . $docrow['encounters'] . "&nbsp;\n";
  echo "  </td>\n";
	/**
  echo "  <td align='right'>\n";
  echo "   &nbsp;"; bucks($docrow['charges']); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;"; bucks($docrow['copays']); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td colspan='2'>\n";
  echo "   &nbsp;\n";
  echo "  </td>\n";
	**/
  echo " </tr>\n";

  $grand_total_charges     += $docrow['charges'];
  $grand_total_copays      += $docrow['copays'];
  $grand_total_encounters  += $docrow['encounters'];

  $docrow['charges']     = 0;
  $docrow['copays']      = 0;
  $docrow['encounters']  = 0;
 }

 $form_facility = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
 $form_provider = isset($_POST['form_provider']) ? $_POST['form_provider'] : '';
 $form_no_show  = isset($_POST['form_noshow']) ? $_POST['form_noshow'] : '';
 $form_csv      = isset($_POST['form_csv']) ? $_POST['form_csv'] : '';
 $form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
 $form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
 // $form_provider = '';
 if ($_POST['form_refresh']) {
  $form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
  $form_to_date = fixDate($_POST['form_to_date'], "");
	$exclude = LoadList('Exclude_Appt_No_Enc_Statuses');

  // MySQL doesn't grok full outer joins so we do it the hard way.
  //
  $query = "SELECT " .
   "e.pc_eventDate, e.pc_startTime, " .
   "fe.encounter, fe.date AS encdate, fe.pc_catid, " .
   "p.fname, p.lname, p.pid, p.pubpid, " .
   "CONCAT( u.lname, ', ', u.fname ) AS docname " .
   "FROM openemr_postcalendar_events AS e " .
   "LEFT OUTER JOIN form_encounter AS fe " .
   "ON fe.date = e.pc_eventDate AND fe.pid = e.pc_pid " .
   "LEFT OUTER JOIN list_options AS lo " .
	 "ON (lo.list_id = 'apptstat' AND e.pc_apptstatus = lo.option_id) ".
   "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
   "LEFT OUTER JOIN users AS u ON u.id = e.pc_aid WHERE ";
  if ($form_to_date) {
   $query .= "e.pc_eventDate >= '$form_from_date' AND e.pc_eventDate <= '$form_to_date' ";
  } else {
   $query .= "e.pc_eventDate = '$form_from_date' ";
  }
  if ($form_facility !== '') {
   $query .= "AND e.pc_facility = '$form_facility' ";
  }
  if ($form_provider !== '') {
   $query .= "AND e.pc_aid = '$form_provider' ";
  }
  if ($form_noshow == '') {
   $query .= "AND UPPER(lo.title) NOT LIKE '%NO%SHOW%' ";
  }
  $query .= "AND e.pc_pid != '' ";
	$enc_filter = '';
	foreach($exclude as $e) {
		if(!$enc_filter) {
			$enc_filter = 'AND (';
		} else {
			$enc_filter .= ' AND ';
		}
		$enc_filter .= "fe.pc_apptstatus != '".$e['option_id']."'";
	}
	if($enc_filter) $enc_filter .= ') ';
	$query .= $enc_filter;
  $query .= "ORDER BY docname, pc_eventDate, pc_startTime";

  $res = sqlStatement($query);
 }
?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
    
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
<title><?php  xl('Appointments w/No Encounter','e'); ?></title>
</head>

<body class="body_top">

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Appointments w/No Encounter','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form method='post' id='theform' action='appt_no_encounter.php'>

<div id="report_parameters">

<table>
 <tr>
  <td width='830px'>
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
				 echo "    <option value=''>-- All Facilities --\n";
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
			   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php  echo $form_from_date; ?>'
				title='Date of appointments mm/dd/yyyy' >
			   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
			<td class='label'>
			   <?php xl('To','e'); ?>:
			</td>
			<td>
			   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php  echo $form_to_date; ?>'
				title='Optional end date mm/dd/yyyy' >
			   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
				id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
				title='<?php xl('Click here to choose a date','e'); ?>'>
			</td>
		</tr>
		<tr>
      <td class='label'><?php xl('Provider','e'); ?>: </td>
      <td style='width: 18%;'><?php
        // Build a drop-down list of providers.
        $query = "SELECT id, username, lname, fname FROM users " .
								"WHERE authorized=1 AND username!='' AND active='1' ".
								"AND (UPPER(specialty) LIKE '%PROVIDER%' OR ".
								"UPPER(specialty) LIKE '%SUPERVISOR%') AND calendar!=0 ".
								"ORDER BY lname, fname";
        $ures = sqlStatement($query);

        echo "   <select name='form_provider'>\n";
        echo "    <option value=''";
				if($form_provider == '') { echo " selected"; }
							echo ">-- " . xl('All') . " --</option>\n";

              while ($urow = sqlFetchArray($ures)) {
                $provid = $urow['id'];
                echo "    <option value='$provid'";
                if ($provid == $form_provider) echo " selected";
                echo ">" . $urow['lname'] . ", " . $urow['fname'] . "</option>\n";
              }
              echo "   </select>\n";
      ?></td>
			<td colspan="2">
			   <input type='checkbox' name='form_details' id='form_details' 
				value='1'<?php if ($_POST['form_details']) echo " checked"; ?>>&nbsp;&nbsp;<label for="form_details"><?php xl('Show Details','e') ?></label>
			</td>
			<td colspan="2">
			   <input type='checkbox' name='form_noshow' id='form_noshow' 
				value='1'<?php if ($_POST['form_noshow']) echo " checked"; ?>>&nbsp;&nbsp;<label for="form_noshow"><?php xl('Include No Show','e') ?></label>
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
  <th align='right'> <?php  xl('Chart','e'); ?>&nbsp; </th>
	<!--
  <th align='right'> <?php // xl('Encounter','e'); ?>&nbsp; </th>
  <th align='right'> <?php // xl('Charges','e'); ?>&nbsp; </th>
  <th align='right'> <?php // xl('Copays','e'); ?>&nbsp; </th>
  <th> <?php // xl('Billed','e'); ?> </th>
  <th> &nbsp;<?php // xl('Error','e'); ?> </th>
	-->
 </thead>
 <tbody>
<?php
 if ($res) {
  $docrow = array('docname' => '', 'charges' => 0, 'copays' => 0, 'encounters' => 0);

  while ($row = sqlFetchArray($res)) {
   $patient_id = $row['pid'];
   $encounter  = $row['encounter'];
   if($encounter) { continue; }
   $docname    = $row['docname'] ? $row['docname'] : xl('Unknown');

   if ($docname != $docrow['docname']) {
    endDoctor($docrow);
   }

   $errmsg  = "";
   $gcac_related_visit = false;

   $docrow['encounters']++;

   if ($_POST['form_details']) {
?>
 <tr>
  <td>
   &nbsp;<?php  echo ($docname == $docrow['docname']) ? "" : $docname ?>
  </td>
  <td>
   &nbsp;<?php echo oeFormatShortDate($row['pc_eventDate']) . ' ' . 
					substr($row['pc_startTime'], 0, 5); ?>
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
	<!--
  <td align='right'>
   <?php // echo $encounter ?>&nbsp;
  </td>
  <td align='right'>
   <?php // bucks($charges) ?>&nbsp;
  </td>
  <td align='right'>
   <?php // bucks($copays) ?>&nbsp;
  </td>
  <td>
   <?php // echo $billed ?>
  </td>
  <td style='color:#cc0000'>
   <?php // echo $errmsg; ?>&nbsp;
  </td>
	-->
 </tr>
<?php
   } // end of details line

   $docrow['docname'] = $docname;
  } // end of row

  endDoctor($docrow);

  echo " <tr class='report_totals'>\n";
  echo "  <td colspan='4'>\n";
  echo "   &nbsp;" . xl('Grand Totals') . "\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;" . $grand_total_encounters . "&nbsp;\n";
  echo "  </td>\n";
	/**
  echo "  <td align='right'>\n";
  echo "   &nbsp;"; bucks($grand_total_charges); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;"; bucks($grand_total_copays); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td colspan='2'>\n";
  echo "   &nbsp;\n";
  echo "  </td>\n";
	**/
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

<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
