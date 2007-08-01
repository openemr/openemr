<?php
// Copyright (C) 2007 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../library/lists.inc");
require_once("../../library/acl.inc");

// Might want something different here.
//
// if (! acl_check('acct', 'rep')) die("Unauthorized access.");

$from_date       = fixDate($_POST['form_from_date']);
$to_date         = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_issue_type = $_POST['form_issue_type'];
$form_squads     = $_POST['form_squads']; // this is an array
?>
<html>
<head>
<title><?php xl('Injury Overview Report','e'); ?></title>
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<style type="text/css">
 body       { font-family:sans-serif; font-size:10pt; font-weight:normal }
 .dehead    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>
<script type="text/javascript" src="../../library/topdialog.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="JavaScript">

 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

// callback from add_edit_issue.php:
function refreshIssue(issue, title) {
 top.restoreSession();
 document.forms[0].submit();
}

// Process click on issue title.
function dopclick(id,pid) {
 dlgopen('../patient_file/summary/add_edit_issue.php?issue=' + id + '&thispid=' + pid,
  '_blank', 600, 475);
}

</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<center>

<h2><? xl('Injury Overview Report','e'); ?></h2>

<form name='theform' method='post' action='injury_overview_report.php'
 onsubmit='return top.restoreSession()'>

<table border='0' cellspacing='0' cellpadding='2'>

 <tr>
  <td valign='top' nowrap>
   Issues:
  </td>
  <td valign='top'>
   <select name='form_issue_type' title='Types of issues to show'>
    <option value=''>All</option>
<?php
	foreach ($ISSUE_TYPES as $key => $value) {
		echo "    <option value='$key'";
		if ($key == $form_issue_type) echo " selected";
		echo ">" . $value[0] . "</option>\n";
	}
?>
   </select>
  </td>
  <td valign='top' rowspan='3' nowrap>
   &nbsp;
   Squads:
  </td>
  <td valign='top' rowspan='3'>
   <select name='form_squads[]' size='4' multiple
    title='<?php xl('Hold down Ctrl to select multiple squads','e'); ?>'>
<?php
	$squads = acl_get_squads();
	if ($squads) {
		foreach ($squads as $key => $value) {
			echo "    <option value='$key'";
			if (!is_array($form_squads) || in_array($key, $form_squads)) echo " selected";
			echo ">" . $value[3] . "</option>\n";
		}
	}
?>
   </select>
  </td>
  <td valign='top' rowspan='3' nowrap>
   &nbsp;
   <input type='submit' name='form_refresh' value='<?php xl('Show Report','e'); ?>'
    title='<?php xl('Click to generate the report','e'); ?>'>
  </td>
 </tr>
 <tr>
  <td valign='top' nowrap>
   From:
  </td>
  <td valign='top' nowrap>
   <input type='text' name='form_from_date' id='form_from_date' size='10' value='<?php echo $from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Start date yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
  </td>
 </tr>
 <tr>
  <td valign='top' nowrap>
   To:
  </td>
  <td valign='top' nowrap>
   <input type='text' name='form_to_date' id='form_to_date' size='10' value='<?php echo $to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='End date yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<?php
	if ($_POST['form_squads']) {

		// fetch all the issues of the desired type and corresponding FIA
		// data.  We are reporting only values from issues (though often by FIA
		// fields), so it seems we want to retain one array row per issue and
		// discard extra FIA forms.

		$issuetypematches = '';
		if ($form_issue_type) {
			$issuetypematches = " AND lists.type = '$form_issue_type'";
		}

		$squadmatches = '1 = 2'; // an always-false condition
		foreach ($form_squads as $squad)
			$squadmatches .= " OR pd.squad = '$squad'";

		$query = "SELECT lists.id AS listid, lists.diagnosis, lists.pid, " .
			"lists.extrainfo AS gmissed, lists.begdate, lists.enddate, " .
			"lists.returndate, lists.title, lists.type, " .
			"pd.lname, pd.fname, pd.mname, pd.fitness " .
			"FROM lists " .
			"JOIN patient_data AS pd ON pd.pid = lists.pid AND ( $squadmatches ) " .
			"WHERE ( lists.enddate IS NULL OR lists.enddate >= '$from_date' ) AND " .
			"( lists.begdate IS NULL OR lists.begdate <= '$to_date' )" .
			"$issuetypematches " .
			"ORDER BY pd.lname, pd.fname, pd.mname, lists.pid, lists.begdate";

		// echo "\n<!-- $query -->\n"; // debugging

		$res = sqlStatement($query);

		$areport = array();
		$last_listid  = 0;
		$last_pid     = 0;
		$last_endsecs = 0;
		$encount      = 0;
?>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">Player</td>
  <td class="dehead">Fitness</td>
  <td class="dehead">Issue</td>
  <td class="dehead">Diagnosis</td>
  <td class="dehead">Start Date</td>
  <td class="dehead" align="right">Days Missed</td>
  <td class="dehead" align="right">Games Missed</td>
  <td class="dehead">Last Provider</td>
 </tr>

<?php
		while ($row = sqlFetchArray($res)) {
			$listid = $row['listid'];
			$thispid = $row['pid'];
			$issue_title = trim($row['title']);
			$ptname  = '&nbsp;';
			$fitness = '&nbsp;';

			$issue_style = $ISSUE_TYPES[$row['type']][3];

			// Compute days missed.  Force non-overlap of multiple issues for the
			// same player.  This logic assumes sorting on begdate within pid.
			//
			$begsecs = $row['begdate'] ? strtotime($row['begdate']) : 0;
			$endsecs = $row['returndate'] ? strtotime($row['returndate']) : time();
			if ($thispid == $last_pid) {
				if ($begsecs < $last_endsecs) {
					$begsecs = $last_endsecs;
				}
			}
			else {
				$last_pid = $thispid;
				$last_endsecs = 0;
				$ptname = trim($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']);
				if ($row['fitness']) $fitness = $PLAYER_FITNESSES[$row['fitness'] - 1];
				$bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";
			}
			$daysmissed = '&nbsp;';
			if ($row['begdate'] && $issue_style == 0) {
				if ($begsecs > $endsecs) $begsecs = $endsecs;
				if ($last_endsecs < $endsecs) $last_endsecs = $endsecs;
				$daysmissed = round(($endsecs - $begsecs) / (60 * 60 * 24));
			}

			// Get the name of the last provider for this issue.
			$query = "SELECT users.lname, users.fname, users.mname " .
				"FROM issue_encounter, forms, users WHERE " .
				"issue_encounter.list_id = $listid AND " .
				"forms.pid = issue_encounter.pid AND " .
				"forms.encounter = issue_encounter.encounter AND " .
				"users.username = forms.user " .
				"ORDER BY forms.date DESC LIMIT 1";
			$user = sqlQuery($query);
			$provname = $user['lname'] ?
				$user['lname'] . ', ' . $user['fname'] . ' ' . $user['mname'] :
				'&nbsp;';

			echo " <tr bgcolor='$bgcolor' onclick='dopclick($listid,$thispid)' style='cursor:pointer'>\n";
			echo "  <td class='detail'>$ptname</td>\n";
			echo "  <td class='detail'>$fitness</td>\n";
			echo "  <td class='detail'>$issue_title</td>\n";
			echo "  <td class='detail'>" . $row['diagnosis'] . "</td>\n";
			echo "  <td class='detail'>" . $row['begdate'] . "</td>\n";
			echo "  <td class='detail' align='right'>$daysmissed</td>\n";
			echo "  <td class='detail' align='right'>" . $row['gmissed'] . "</td>\n";
			echo "  <td class='detail'>$provname</td>\n";

			echo " </tr>\n";
		} // end while
?>

</table>

<?php } // end of if ($_POST['form_refresh']) ?>

</form>
</center>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</body>
</html>
