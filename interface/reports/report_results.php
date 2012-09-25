<?php
/**
 * Generic script to list stored reports. Part of the module to allow the tracking,
 * storing, and viewing of reports.
 *
 * Copyright (C) 2012 Brady Miller <brady@sparmy.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/clinical_rules.php";
require_once "$srcdir/report_database.inc";
?>

<html>

<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<title><?php echo htmlspecialchars( xl('Report Results/History'), ENT_NOQUOTES); ?></title>

<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<script LANGUAGE="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

</script>

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

<span class='title'><?php echo htmlspecialchars( xl('Report History/Results'), ENT_NOQUOTES); ?></span>

<form method='post' name='theform' id='theform' action='report_results.php' onsubmit='return top.restoreSession()'>

<div id="report_parameters">

<table>
 <tr>
  <td width='470px'>
	<div style='float:left'>

	<table class='text'>

                   <tr>
                      <td class='label'>
                         <?php echo htmlspecialchars( xl('Begin Date'), ENT_NOQUOTES); ?>:
                      </td>
                      <td>
                         <input type='text' name='form_begin_date' id='form_begin_date' size='20' value='<?php echo htmlspecialchars( $_POST['form_begin_date'], ENT_QUOTES); ?>'
                            onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo htmlspecialchars( xl('yyyy-mm-dd hh:mm:ss'), ENT_QUOTES); ?>'>
                           <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
                            id='img_begin_date' border='0' alt='[?]' style='cursor:pointer'
                            title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>'>
                      </td>
                   </tr>

                <tr>
                        <td class='label'>
                              <?php echo htmlspecialchars( xl('End Date'), ENT_NOQUOTES); ?>:
                        </td>
                        <td>
                           <input type='text' name='form_end_date' id='form_end_date' size='20' value='<?php echo htmlspecialchars( $_POST['form_end_date'], ENT_QUOTES); ?>'
                                onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo htmlspecialchars( xl('yyyy-mm-dd hh:mm:ss'), ENT_QUOTES); ?>'>
                             <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
                                id='img_end_date' border='0' alt='[?]' style='cursor:pointer'
                                title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>'>
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
					<a id='search_button' href='#' class='css_button' onclick='top.restoreSession(); $("#theform").submit()'>
					<span>
						<?php echo htmlspecialchars( xl('Search'), ENT_NOQUOTES); ?>
					</span>
					</a>
                                        <a id='refresh_button' href='#' class='css_button' onclick='top.restoreSession(); $("#theform").submit()'>
                                        <span>
                                                <?php echo htmlspecialchars( xl('Refresh'), ENT_NOQUOTES); ?>
                                        </span>
                                        </a>
				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>

</div>  <!-- end of search parameters -->

<br>



<div id="report_results">
<table>

 <thead>
  <th align='center'>
   <?php echo htmlspecialchars( xl('Title'), ENT_NOQUOTES); ?>
  </th>

  <th align='center'>
   <?php echo htmlspecialchars( xl('Date'), ENT_NOQUOTES); ?>
  </th>

  <th align='center'>
   <?php echo htmlspecialchars( xl('Status'), ENT_NOQUOTES); ?>
  </th>

 </thead>
 <tbody>  <!-- added for better print-ability -->
<?php

 $res = listingReportDatabase($_POST['form_begin_date'],$_POST['form_end_date']);
 while ($row = sqlFetchArray($res)) {

  // Figure out the title and link
  if ($row['type'] == "cqm") {
    if (!$GLOBALS['enable_cqm']) continue;
    $type_title = xl('Clinical Quality Measures (CQM)');
    $link="cqm.php?report_id=" . attr($row["report_id"]) . "&back=list";
  }
  else if ($row['type'] == "amc") {
    if (!$GLOBALS['enable_amc']) continue;
    $type_title = xl('Automated Measure Calculations (AMC)');
    $link="cqm.php?report_id=" . attr($row["report_id"]) . "&back=list";
  }
  else if ($row['type'] == "process_reminders") {
    if (!$GLOBALS['enable_cdr']) continue;
    $type_title = xl('Processing Patient Reminders');
    $link="../batchcom/batch_reminders.php?report_id=" . attr($row["report_id"]);
  }
  else if ($row['type'] == "process_send_reminders") {
    if (!$GLOBALS['enable_cdr']) continue;
    $type_title = xl('Processing and Sending Patient Reminders');
    $link="../batchcom/batch_reminders.php?report_id=" . attr($row["report_id"]);
  }
  else if ($row['type'] == "passive_alert") {
    if (!$GLOBALS['enable_cdr']) continue;
    $type_title = xl('Standard Measures (Passive Alerts)');
    $link="cqm.php?report_id=" . attr($row["report_id"]) . "&back=list";
  }
  else if ($row['type'] == "active_alert") {
    if (!$GLOBALS['enable_cdr']) continue;
    $type_title = xl('Standard Measures (Active Alerts)');
    $link="cqm.php?report_id=" . attr($row["report_id"]) . "&back=list";
  }
  else if ($row['type'] == "patient_reminder") {
    if (!$GLOBALS['enable_cdr']) continue;
    $type_title = xl('Standard Measures (Patient Reminders)');
    $link="cqm.php?report_id=" . attr($row["report_id"]) . "&back=list";
  }
  else {
    // Not identified, so give an unknown title
    $type_title = xl('Unknown');
    $link="";
  }
?>
 <tr>
    <?php if ($row["progress"] == "complete") { ?>
      <td align='center'><a href='<?php echo $link; ?>' onclick='top.restoreSession()'><?php echo text($type_title); ?></a></td>
    <?php } else { ?>
      <td align='center'><?php echo text($type_title); ?></td>
    <?php } ?>
    <td align='center'><?php echo text($row["date_report"]); ?></td>
    <?php if ($row["progress"] == "complete") { ?>
      <td align='center'><?php echo xlt("Complete") . " (" . xlt("Processing Time") . ": " . text($row['report_time_processing']) . " " . xlt("Minutes") . ")"; ?></td>
    <?php } else { ?>
      <td align='center'><?php echo xlt("Pending") . " (" . text($row["progress_items"]) . " / " . text($row["total_items"]) . " " . xlt("Patients Processed") . ")"; ?></td>
    <?php } ?>

 </tr>

<?php
 } // $row = sqlFetchArray($res) while
?>
</tbody>
</table>
</div>  <!-- end of search results -->

</form>

</body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_begin_date", ifFormat:"%Y-%m-%d %H:%M:%S", button:"img_begin_date", showsTime:'true'});
 Calendar.setup({inputField:"form_end_date", ifFormat:"%Y-%m-%d %H:%M:%S", button:"img_end_date", showsTime:'true'});
</script>

</html>

