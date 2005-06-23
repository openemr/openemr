<?
include_once("../../globals.php");

include_once("$srcdir/forms.inc");
include_once("$srcdir/calendar.inc");

?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">


</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<span class="title">This Encounter</span> 
<?
if (is_numeric($pid)) {
  $result = getPatientData($pid, "fname,lname");
  echo " for " . $result['fname'] . " " . $result['lname'];
}

?>
:<br>

<?

if ($result = getFormByEncounter($pid, $encounter, "id, date, form_id, form_name,formdir,user")) {
	echo "<table>";
	foreach ($result as $iter) {
		$form_info = getFormInfoById($iter['id']);
		echo '<tr valign="top">';
		$user = $iter['user'];
		$user = getNameFromUsername($user);

		$form_name = ($iter['formdir'] == 'newpatient') ? "Patient Encounter" : $iter['form_name'];

		echo '<td class="text"><span style="font-weight:bold;">' .
			$user['fname'] . " " . $user['lname'] .'</span></td>';
		echo "<td valign='top'><a target='Main' href='$rootdir/patient_file/encounter/view_form.php?" .
			"formname=" . $iter{"formdir"} . "&id=" . $iter{"form_id"} .
			"' class='text'>$form_name</a></td>\n" .
			"<td width='25'></td>\n" .
			"<td valign='top'>";

		if (true) {
			// Instead of the garbage below, let's use the form's report.php.
			//
			include_once($GLOBALS['incdir'] . "/forms/" . $iter['formdir'] . "/report.php");
			call_user_func($iter['formdir'] . "_report", $pid, $iter['encounter'], 2, $iter['form_id']);
		}
		else {
			// Garbage starts here. Delete this after some testing.
			//
			echo "<table valign='top' cellspacing='0' cellpadding='0'><tr>\n";
			$counter = 0;
			foreach ($form_info as $field_name => $field) {
				if ($field_name == "id" || $field_name == "date" || $field_name == "pid" ||
					$field_name == "user" || $field_name == "groupname" ||
					$field_name == "authorized" || $field_name == "activity" ||
					$field_name == "encounter") {
					;// don't display meta data fields
					$field_name=ucwords(str_replace("_"," ",$field_name));
				}
				elseif (empty($field)) {
					;//don't diplay empty fields
				}
				elseif ($field == "0000-00-00" || $field == "00:00" || $field == "0000-00-00 00:00") {
					;//don't diplay empty dates
				}
				else {
					$counter++;
					$width="";
					if (strlen($field) > 15) {
						// $width="width=\"300\"";
						$counter += 2;
					}
					echo '<td ' . $width . ' valign="top"><span class="text" style="font-size:8pt;font-weight: bold">' .
						$field_name . ':</span> <span style="font-size:8pt;">' . $field . '</span></td><td width="5"></td>';
				}
				if ($counter > 4) {
					echo "</tr><tr>";
					$counter = 0;
				}
			}
			echo "</tr></table>\n";
			//
			// End of garbage
		}

		echo "</td></tr>";
	}
	echo "</table>";
}
?>

</body>
</html>
