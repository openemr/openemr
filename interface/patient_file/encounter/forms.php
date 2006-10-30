<?php
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
  dlgopen('../deleter.php?encounter=<?php echo $encounter; ?>', '_blank', 500, 450);
  return false;
 }

 // Called by the deleter.php window on a successful delete.
 function imdeleted() {
  top.Title.location.href = '../patient_file/encounter/encounter_title.php';
  top.Main.location.href  = '../patient_file/encounter/patient_encounter.php?mode=new';
 }

</script>

</head>

<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<span class="title"><?php xl('This Encounter','e'); ?></span>
<?php
 $auth_notes_a  = acl_check('encounters', 'notes_a');
 $auth_notes    = acl_check('encounters', 'notes');
 $auth_relaxed  = acl_check('encounters', 'relaxed');

 if (is_numeric($pid)) {
  // Check for no access to the patient's squad.
  $result = getPatientData($pid, "fname,lname,squad");
  echo " for " . $result['fname'] . " " . $result['lname'];
  if ($result['squad'] && ! acl_check('squads', $result['squad'])) {
   $auth_notes_a = $auth_notes = $auth_relaxed = 0;
  }
  // Check for no access to the encounter's sensitivity level.
  $result = sqlQuery("SELECT sensitivity FROM form_encounter WHERE " .
   "pid = '$pid' AND encounter = '$encounter' LIMIT 1");
  if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
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
   $formdir = $iter['formdir'];

   // Skip forms that we are not authorized to see.
   if (($auth_notes_a) ||
       ($auth_notes && $iter['user'] == $_SESSION['authUser']) ||
       ($auth_relaxed && ($formdir == 'sports_fitness' || $formdir == 'podiatry'))) ;
   else continue;

   // $form_info = getFormInfoById($iter['id']);
   echo '<tr valign="top">';
   $user = getNameFromUsername($iter['user']);

   $form_name = ($formdir == 'newpatient') ? "Patient Encounter" : $iter['form_name'];

   echo '<td class="text"><span style="font-weight:bold;">' .
    $user['fname'] . " " . $user['lname'] .'</span></td>';
   echo "<td valign='top'><a target='Main' href='$rootdir/patient_file/encounter/view_form.php?" .
    "formname=" . $formdir . "&id=" . $iter{"form_id"} .
    "' class='text'>$form_name</a></td>\n" .
    "<td width='25'></td>\n" .
    "<td valign='top'>";

   // Use the form's report.php for display.
   //
   include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
   call_user_func($formdir . "_report", $pid, $iter['encounter'], 2, $iter['form_id']);

   echo "</td></tr>";
  }
  echo "</table>";
 }
?>

</body>
</html>
