<?
 include_once("../../globals.php");
 include_once("$srcdir/forms.inc");
 include_once("$srcdir/sql.inc");
 include_once("$srcdir/encounter.inc");
 include_once("$srcdir/acl.inc");

foreach ($_POST as $k => $var) {
  if (! is_array($var)) $_POST[$k] = mysql_escape_string($var);
  echo "$var\n";
}

$conn = $GLOBALS['adodb']['db'];

// $date = $_POST["year"]."-".$_POST["month"]."-".$_POST["day"];
// $onset_date = $_POST["onset_year"]."-".$_POST["onset_month"]."-".$_POST["onset_day"];

$date        = $_POST['form_date'];
$onset_date  = $_POST['form_onset_date'];
$sensitivity = $_POST['form_sensitivity'];
$facility    = $_POST['facility'];
$reason      = $_POST['reason'];
$mode        = $_POST['mode'];

if ($mode == 'new')
{
  $encounter = $conn->GenID("sequences");
  addForm($encounter, "New Patient Encounter",
    sqlInsert("INSERT INTO form_encounter SET " .
      "date = '$date', " .
      "onset_date = '$onset_date', " .
      "reason = '$reason', " .
      "facility = '$facility', " .
      "sensitivity = '$sensitivity', " .
      "pid = '$pid', " .
      "encounter = '$encounter'"),
    "newpatient", $pid, $userauthorized, $date);
}
else if ($mode == 'update')
{
  $id = $_POST["id"];
  $result = sqlQuery("SELECT encounter, sensitivity FROM form_encounter WHERE id = '$id'");
  if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
   die("You are not authorized to see this encounter.");
  }
  $encounter = $result['encounter'];
  // See view.php to allow or disallow updates of the encounter date.
  // $datepart = $_POST["day"] ? "date = '$date', " : "";
  $datepart = acl_check('encounters', 'date_a') ? "date = '$date', " : "";
  sqlStatement("UPDATE form_encounter SET " .
    $datepart .
    "onset_date = '$onset_date', " .
    "reason = '$reason', " .
    "facility = '$facility', " .
    "sensitivity = '$sensitivity' " .
    "WHERE id = '$id'");
}
else {
  die("Unknown mode '$mode'");
}

setencounter($encounter);

// Update the list of issues associated with this encounter.
sqlStatement("DELETE FROM issue_encounter WHERE " .
  "pid = '$pid' AND encounter = '$encounter'");
if (is_array($_POST['issues'])) {
  foreach ($_POST['issues'] as $issue) {
    $query = "INSERT INTO issue_encounter ( " .
      "pid, list_id, encounter " .
      ") VALUES ( " .
      "'$pid', '$issue', '$encounter'" .
    ")";
    sqlStatement($query);
  }
}
?>
<html>
<body>
<script language="Javascript">
 window.location="<?echo "$rootdir/patient_file/encounter/patient_encounter.php";?>";
</script>

</body>
</html>
