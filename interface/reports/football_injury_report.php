<?php
// This module is for team sports use and reports on various attributes
// of injuries for a given time period and reporting key.

include_once("../globals.php");
include_once("../../library/patient.inc");
include_once("../../library/acl.inc");
include_once("../forms/football_injury_audit/fia.inc.php");

// Might want something different here.
//
// if (! acl_check('acct', 'rep')) die("Unauthorized access.");

$from_date   = fixDate($_POST['form_from_date']);
$to_date     = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_by     = $_POST['form_by'];     // this is a scalar
$form_show   = $_POST['form_show'];   // this is an array
$form_squads = $_POST['form_squads']; // this is an array

// One of these is chosen as the left column, or Y-axis, of the report.
//
$arr_by = array(
	1  => 'Activity Type',
	2  => 'Body Region',
	3  => 'Footwear Type',
	4  => 'Game Period',
	5  => 'Injury Mechanism',
	6  => 'Injury Type',
	7  => 'Player',
	8  => 'Playing Position',
	9  => 'Sanction Type',
	10 => 'Surface Type',
	11 => 'Training Type',
);

// A reported value is either scalar, or an array listed horizontally.  If
// multiple items are chosen then each starts in the next available column.
//
$arr_show = array(
	1 => array('Number of Injuries'),
	2 => array('Days/Games Missed'),
	3 => array('Body Region'),
	4 => array('Injury Type'),
	5 => array('Issue Title'),
);

$arr_regions_osics = array(
	'A' => 'Ankle + heel',
	'B' => 'Buttock + S.I.',
	'C' => 'Chest',
	'D' => 'Thoracic spine',
	'E' => 'Elbow',
	'F' => 'Foot',
	'G' => 'Hip + groin',
	'H' => 'Head',
	'K' => 'Knee',
	'L' => 'Lumbar spine',
	'M' => 'Medical problem',
	'N' => 'Neck',
	'O' => 'Abdominal',
	'P' => 'Hand + fingers',
	'Q' => 'Lower leg',
	'R' => 'Forearm',
	'S' => 'Shoulder + clavicle',
	'T' => 'Thigh + hamstring',
	'U' => 'Upper arm',
	'W' => 'Wrist',
	'X' => 'Multiple areas',
	'Z' => 'Area not specified',
);

$arr_regions_ucsmc = array(
	'AN' => 'Ankle + heel',
	'AR' => 'Upper arm',
	'BL' => 'Medical problem',
	'CV' => 'Medical problem',
	'DE' => 'Medical problem',
	'EL' => 'Elbow',
	'EN' => 'Medical problem',
	'EV' => 'Environmental',
	'FA' => 'Forearm',
	'FE' => 'Fluid and electrolyte problem',
	'FO' => 'Foot',
	'GI' => 'Abdominal',
	'GU' => 'Medical problem',
	'HA' => 'Hand + fingers',
	'HE' => 'Head',
	'HI' => 'Hip + groin',
	'ID' => 'Medical problem',
	'KN' => 'Knee',
	'LE' => 'Lower leg',
	'LP' => 'Lumbar spine',
	'NE' => 'Neck',
	'NS' => 'Medical problem',
	'PS' => 'Medical problem',
	'RE' => 'Medical problem',
	'RM' => 'Medical problem',
	'SH' => 'Shoulder + clavicle',
	'TH' => 'Thigh + hamstring',
	'TR' => 'Thoracic spine',
	'WR' => 'Wrist',
);

$arr_types_osics = array(
	'A' => 'Arthritis / degen joint disease',
	'B' => 'Developmental abnormality',
	'C' => 'Cartilage / chondral / disc damage',
	'D' => 'Dislocation',
	'E' => 'Tumour',
	'F' => 'Fracture',
	'G' => 'Avulsion / avulsion fracture',
	'H' => 'Haematoma / bruising',
	'I' => 'Infection / Abscess',
	'J' => 'Minor joint strain +/- synovitis',
	'K' => 'Laceration / skin condition',
	'L' => 'Ligament tear or sprain',
	'M' => 'Strain of muscle',
	'N' => 'Neural condition / nerve damage',
	'O' => 'Visceral damage/trauma/surgery',
	'P' => 'Chronic synovitis / effusion / joint pain / gout',
	'Q' => 'Old fracture non / malunion',
	'R' => 'Rupture',
	'S' => 'Stress fracture',
	'T' => 'Tendonitis / osis / bursitis',
	'U' => 'Instability / subluxation',
	'V' => 'Vascular condition',
	'X' => 'Medical problem',
	'Y' => 'Trigger point / compartment syndrome / DOMS / cramp',
	'Z' => 'Undiagnosed',
);

$arr_types_ucsmc = array(
	'01' => 'Ligament tear or sprain',
	'02' => 'Ligament tear or sprain',
	'03' => 'Ligament tear or sprain',
	'07' => 'Strain of muscle',
	'09' => 'Rupture',
	'10' => 'Dislocation',
	'11' => 'Instability / subluxation',
	'12' => 'Instability / subluxation',
	'13' => 'Fracture',
	'14' => 'Avulsion / avulsion fracture',
	'15' => 'Old fracture non / malunion',
	'16' => 'Fracture',
	'17' => 'Cartilage / chondral / disc damage',
	'18' => 'Stress fracture',
	'23' => 'Haematoma / bruising',
	'24' => 'Laceration / skin condition',
	'25' => 'Haematoma / bruising',
	'26' => 'Tendonitis / osis / bursitis',
	'27' => 'Tendonitis / osis / bursitis',
	'28' => 'Tendonitis / osis / bursitis',
	'29' => 'Tendonitis / osis / bursitis',
	'33' => 'Arthritis / degen joint disease',
	'36' => 'Trigger point / compartment syndrome / DOMS / cramp',
	'38' => 'Infection / Abscess',
	'39' => 'Medical problem',
	'40' => 'Cartilage / chondral / disc damage',
	'42' => 'Tumour',
	'44' => 'Neural condition / nerve damage',
);

?>
<html>
<head>
<title>Football Injury Report</title>
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<style type="text/css">
 body       { font-family:sans-serif; font-size:10pt; font-weight:normal }
 .dehead    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail    { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
</style>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="JavaScript">
 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';
</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<center>

<h2><? xl('Football Injury Report','e'); ?></h2>

<form name='theform' method='post' action='football_injury_report.php'>

<table border='0' cellspacing='0' cellpadding='2'>

 <tr>
  <td valign='top' nowrap>
   For each
  </td>
  <td valign='top'>
   <select name='form_by' title='Left column of report'>
<?php
	foreach ($arr_by as $key => $value) {
		echo "    <option value='$key'";
		if ($key == $form_by) echo " selected";
		echo ">" . $value . "</option>\n";
	}
?>
   </select>
  </td>
  <td valign='top' rowspan='3' nowrap>
   &nbsp;
   <input type='submit' name='form_refresh' value='Show' title='Click to generate the report'> :
  </td>
  <td valign='top' rowspan='3'>
   <select name='form_show[]' size='4' multiple
    title='Hold down Ctrl to select multiple items'>
<?php
	foreach ($arr_show as $key => $value) {
		echo "    <option value='$key'";
		if (is_array($form_show) && in_array($key, $form_show)) echo " selected";
		echo ">" . $value[0] . "</option>\n";
	}
?>
   </select>
  </td>
  <td valign='top' rowspan='3' nowrap>
   &nbsp;
   for:
  </td>
  <td valign='top' rowspan='3'>
   <select name='form_squads[]' size='4' multiple
    title='Hold down Ctrl to select multiple squads'>
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
 </tr>
 <tr>
  <td valign='top' nowrap>
   from
  </td>
  <td valign='top' nowrap>
   <input type='text' name='form_from_date' id='form_from_date' size='10' value='<?php echo $from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='Start date yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='Click here to choose a date'>
  </td>
 </tr>
 <tr>
  <td valign='top' nowrap>
   to
  </td>
  <td valign='top' nowrap>
   <input type='text' name='form_to_date' id='form_to_date' size='10' value='<?php echo $to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='End date yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='Click here to choose a date'>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<?php
	if ($_POST['form_refresh']) {

		// fetch all the issues that are medical problems and corresponding FIA
		// data.  We are reporting only values from issues (though often by FIA
		// fields), so it seems we want to retain one array row per issue and
		// discard extra FIA forms.

		$squadmatches = '1 = 2'; // an always-false condition
		foreach ($form_squads as $squad)
			$squadmatches .= " OR pd.squad = '$squad'";

		$query = "SELECT lists.id AS listid, lists.diagnosis, lists.pid, " .
			"lists.extrainfo AS gmissed, lists.begdate, lists.enddate, " .
			"lists.returndate, lists.title, fia.*, pd.lname, pd.fname, pd.mname " .
			"FROM lists " .
			"JOIN patient_data AS pd ON pd.pid = lists.pid AND ( $squadmatches ) " .
			"JOIN issue_encounter AS ie ON ie.list_id = lists.id " .
			"JOIN forms ON forms.pid = ie.pid AND forms.encounter = ie.encounter " .
			"AND forms.formdir = 'football_injury_audit' " .
			"JOIN form_football_injury_audit as fia ON fia.id = forms.form_id " .
			"WHERE ( lists.enddate IS NULL OR lists.enddate >= '$from_date' ) AND " .
			"lists.begdate <= '$to_date' AND " .
			"lists.type = 'medical_problem' AND lists.title NOT LIKE '%Illness%'" .
			"ORDER BY lists.pid, lists.begdate";

		$res = sqlStatement($query);

		$areport = array();
		$arr_my_body_regions = array();
		$arr_my_injury_types = array();
		$arr_my_issue_titles = array();

		$last_listid  = 0;
		$last_pid     = 0;
		$last_endsecs = 0;

		while ($row = sqlFetchArray($res)) {

			// Throw away extra injury forms.
			if ($row['listid'] == $last_listid) continue;
			$last_listid = $rows['listid'];

			$body_region = 'Undiagnosed';
			if (preg_match('/^(.)..$/', $row['diagnosis'], $matches)) {
				$body_region = $arr_regions_osics[$matches[1]];
			}
			else if (preg_match('/^(..)\...\...$/', $row['diagnosis'], $matches)) {
				$body_region = $arr_regions_ucsmc[$matches[1]];
			}

			$injury_type = 'Undiagnosed';
			if (preg_match('/^.(.).$/', $row['diagnosis'], $matches)) {
				$injury_type = $arr_types_osics[$matches[1]];
			}
			else if (preg_match('/^..\...\.(..)$/', $row['diagnosis'], $matches)) {
				$injury_type = $arr_types_ucsmc[$matches[1]];
			}

			$issue_title = trim($row['title']);

			$key = 'Unspecified';

			if ($form_by == '1') { // Activity Type
				if ($row['fimech_tackling'] || $row['fimech_tackled'] ||
					$row['fimech_collision'] || $row['fimech_kicked'] ||
					$row['fimech_elbow'] || $row['fimech_othercon'])
				{
					$key = 'Contact';
				} else {
					$key = 'Non-contact';
				}
			}

			else if ($form_by == '2') { // Body Region
				$key = $body_region;
			}

			else if ($form_by == '3') { // Footwear Type
				if ($row['fifootwear']) $key = $arr_footwear[$row['fifootwear']];
			}

			else if ($form_by == '4') { // Game Period
				if ($row['fiinjmin']) {
					$key = 15 * (int)(($row['fiinjmin'] + 14) / 15);
				}
			}

			else if ($form_by == '5') { // Injury Mechanism
				foreach ($arr_activity as $imkey => $imvalue) {
					if ($row["fimech_$imkey"]) {
						$key = $imvalue;
						break;
					}
				}
			}

			else if ($form_by == '6') { // Injury Type
				$key = $injury_type;
			}

			else if ($form_by == '7') { // Player
				$key = trim($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']);
			}

			else if ($form_by == '8') { // Playing Position
				if ($row['fiposition']) $key = $arr_position[$row['fiposition']];
			}

			else if ($form_by == '9') { // Referee Sanction Type
				foreach ($arr_sanction as $imkey => $imvalue) {
					if ($row["fimech_$imkey"]) {
						$key = $imvalue;
						break;
					}
				}
			}

			else if ($form_by == '10') { // Surface Type
				if ($row['fisurface']) $key = $arr_surface[$row['fisurface']];
			}

			else if ($form_by == '11') { // Training Type
				if ($row['fiinjtime']) $key = $arr_injtime[$row['fiinjtime']];
			}

			// OK we now have the reporting key for this issue.

			// If first instance of this key, initialize its arrays.
			if (! $areport[$key]) {
				$areport[$key] = array();
				$areport[$key]['inj']     = 0;  // number of injuries
				$areport[$key]['dmissed'] = 0;  // days missed
				$areport[$key]['gmissed'] = 0;  // games missed
				$areport[$key]['br'] = array(); // body region array
				$areport[$key]['it'] = array(); // injury type array
				$areport[$key]['ti'] = array(); // issue title array
			}

			// Compute days missed.  Force non-overlap of multiple issues for the
			// same player.  This logic assumes sorting on begdate within pid.
			//
			$begsecs = strtotime($row['begdate']);
			$endsecs = $row['returndate'] ? strtotime($row['returndate']) : time();
			if ($row['pid'] == $last_pid) {
				if ($begsecs < $last_endsecs) {
					$begsecs = $last_endsecs;
				}
			}
			else {
				$last_pid = $row['pid'];
				$last_endsecs = 0;
			}
			if ($begsecs > $endsecs) $begsecs = $endsecs;
			if ($last_endsecs < $endsecs) $last_endsecs = $endsecs;
			$daysmissed = round(($endsecs - $begsecs) / (60 * 60 * 24));

			// Store values that we might want to report on.
			$areport[$key]['inj'] += 1;                   // count number of injuries
			$areport[$key]['dmissed'] += $daysmissed;     // count days missed
			$areport[$key]['gmissed'] += $row['gmissed']; // count games missed
			$areport[$key]['br'][$body_region] += 1;      // count injuries to this body part
			$areport[$key]['it'][$injury_type] += 1;      // count injuries of this type
			$areport[$key]['ti'][$issue_title] += 1;      // count injuries with this title

			// These track all body regions and injury types encountered.
			$arr_my_body_regions[$body_region] += 1;
			$arr_my_injury_types[$injury_type] += 1;
			$arr_my_issue_titles[$issue_title] += 1;

		} // end while

		// Sort everything by key for reporting.
		ksort($areport);
		ksort($arr_my_body_regions);
		ksort($arr_my_injury_types);
		ksort($arr_my_issue_titles);
?>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   <?php echo $arr_by[$form_by]; ?>
  </td>

<?php
		// Generate headings for values to be shown.
		foreach ($form_show as $value) {
			if ($value == '1') { // Number of injuries
				echo "  <td class='dehead' align='right'>Injuries</td>\n";
			}
			else if ($value == '2') { // days and games missed
				echo "  <td class='dehead' align='right'>Days Missed</td>\n";
				echo "  <td class='dehead' align='right'>Games Missed</td>\n";
			}
			else if ($value == '3') { // body region
				foreach ($arr_my_body_regions as $br => $nothing) {
					echo "  <td class='dehead' align='right'>$br</td>\n";
				}
			}
			else if ($value == '4') { // injury type
				foreach ($arr_my_injury_types as $it => $nothing) {
					echo "  <td class='dehead' align='right'>$it</td>\n";
				}
			}
			else if ($value == '5') { // issue titles
				foreach ($arr_my_issue_titles as $ti => $nothing) {
					echo "  <td class='dehead' align='right'>$ti</td>\n";
				}
			}
		}

		echo " </tr>\n";

		$encount = 0;

		foreach ($areport as $key => $varr) {
			$bgcolor = (++$encount & 1) ? "#ddddff" : "#ffdddd";

			echo " <tr bgcolor='$bgcolor'>\n";
			echo "  <td class='detail'>$key</td>\n";

			// Generate data for this row.
			foreach ($form_show as $value) {
				if ($value == '1') { // Number of injuries
					echo "  <td class='detail' align='right'>" . $areport[$key]['inj'] . "</td>\n";
				}
				else if ($value == '2') { // days and games missed
					echo "  <td class='detail' align='right'>" . $areport[$key]['dmissed'] . "</td>\n";
					echo "  <td class='detail' align='right'>" . $areport[$key]['gmissed'] . "</td>\n";
				}
				else if ($value == '3') { // body region
					foreach ($arr_my_body_regions as $body_region => $nothing) {
						echo "  <td class='detail' align='right'>" . $areport[$key]['br'][$body_region] . "</td>\n";
					}
				}
				else if ($value == '4') { // injury type
					foreach ($arr_my_injury_types as $injury_type => $nothing) {
						echo "  <td class='detail' align='right'>" . $areport[$key]['it'][$injury_type] . "</td>\n";
					}
				}
				else if ($value == '5') { // issue title
					foreach ($arr_my_issue_titles as $issue_title => $nothing) {
						echo "  <td class='detail' align='right'>" . $areport[$key]['ti'][$issue_title] . "</td>\n";
					}
				}
			}

			echo " </tr>\n";
		} // end foreach
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
