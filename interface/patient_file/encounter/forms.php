<?
 include_once("../../globals.php");
 include_once("$srcdir/forms.inc");
 include_once("$srcdir/calendar.inc");
 include_once("$srcdir/acl.inc");
?>
<html>

<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../../library/dialog.js"></script>

<script language="JavaScript">

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?encounter=<?php echo $encounter ?>', '_blank', 500, 450);
  return false;
 }

 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
  top.Title.location.href = '../patient_file/encounter/encounter_title.php';
  top.Main.location.href  = '../patient_file/encounter/patient_encounter.php?mode=new';
 }

</script>

</head>

<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<span class="title">This Encounter</span>
<?
 $auth_notes_a  = acl_check('encounters', 'notes_a');
 $auth_notes    = acl_check('encounters', 'notes');
 $auth_relaxed  = acl_check('encounters', 'relaxed');

 if (is_numeric($pid)) {
  $result = getPatientData($pid, "fname,lname,squad");
  echo " for " . $result['fname'] . " " . $result['lname'];
  if ($result['squad'] && ! acl_check('squads', $result['squad'])) {
   $auth_notes_a = $auth_notes = $auth_relaxed = 0;
  }
 }

 echo ":";
 if (acl_check('admin', 'super')) {
  echo "&nbsp;&nbsp;<a href='' onclick='return deleteme()'>" .
   "<font class='more' style='color:red'>(Delete)</font></a>";
 }
 echo "<br>\n";

 if ($result = getFormByEncounter($pid, $encounter, "id, date, form_id, form_name,formdir,user")) {
  echo "<table>";
  foreach ($result as $iter) {

   // Skip forms that we are not authorized to see.
   if (($auth_notes_a) ||
       ($auth_notes && $iter['user'] == $_SESSION['authUser']) ||
       ($auth_relaxed && $iter['formdir'] == 'sports_fitness')) ;
   else continue;

   $form_info = getFormInfoById($iter['id']);
   echo '<tr valign="top">';
   $user = getNameFromUsername($iter['user']);

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
