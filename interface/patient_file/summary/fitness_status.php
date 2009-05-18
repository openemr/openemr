<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/pnotes.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/acl.inc");

$accounting_enabled = $GLOBALS['oer_config']['ws_accounting']['enabled'];

// Get relevant ACL info.
$auth_notes_a  = acl_check('encounters', 'notes_a');
$auth_notes    = acl_check('encounters', 'notes');
$auth_relaxed  = acl_check('encounters', 'relaxed');
$auth_demo     = acl_check('patients'  , 'demo');

$tmp = getPatientData($pid, "squad");
if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
  $auth_notes_a = $auth_notes = $auth_demo = $auth_relaxed = 0;

if (!($auth_notes_a || $auth_notes || $auth_relaxed)) {
  echo "<body>\n<html>\n";
  echo "<p>(".xl('Encounters not authorized').")</p>\n";
  echo "</body>\n</html>\n";
  exit();
}
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../../library/tooltip.js"></script>

<script language="JavaScript">

 function toencounter(enc, datestr) {
  top.restoreSession();
<?php if ($GLOBALS['concurrent_layout']) { ?>
  parent.parent.left_nav.setEncounter(datestr, enc, parent.window.name);
  parent.parent.left_nav.setRadio(parent.window.name, 'enc');
  parent.location.href  = '../encounter/encounter_top.php?set_encounter=' + enc;
<?php } else { ?>
  top.Title.location.href = '../encounter/encounter_title.php?set_encounter='   + enc;
  top.Main.location.href  = '../encounter/patient_encounter.php?set_encounter=' + enc;
<?php } ?>
 }

</script>

</head>

<body class="body_bottom">

<font class='title'><?php xl('Stength and Conditioning','e'); ?></font>

<br>

<table>
 <tr>
  <td class='bold'><?php xl('Date','e');     ?></td>
  <td class='bold'><?php xl('Reason','e');   ?></td>
  <td class='bold'><?php xl('Provider','e'); ?></td>
 </tr>

<?php
$res = sqlStatement("SELECT " .
  "f.encounter, f.user, fe.date, fe.reason, fe.sensitivity " .
  "FROM forms AS f " .
  "LEFT OUTER JOIN form_encounter AS fe ON fe.pid = f.pid " .
  "AND fe.encounter = f.encounter " .
  "WHERE f.pid = '$pid' AND f.formdir = 'strength_conditioning' " .
  "ORDER BY fe.date DESC, f.encounter DESC");

while ($row = sqlFetchArray($res)) {
  $raw_encounter_date = date("Y-m-d", strtotime($row['date']));
  $reason_string = $row['reason'];
  $auth_sensitivity = true;

  $href = "javascript:window.toencounter(" . $row['encounter'] . ",\"$raw_encounter_date\")";
  $linkbeg = "<a class='text' href='$href'>";
  $linkend = "</a>";

  if ($row['sensitivity']) {
    $auth_sensitivity = acl_check('sensitivities', $row['sensitivity']);
    if (!$auth_sensitivity) {
      $reason_string = "(No access)";
      $linkbeg = "<span class='text'>";
      $linkend = "</span>";
    }
  }

  echo "<tr>\n";

  // show encounter date
  echo "<td valign='top'>$linkbeg$raw_encounter_date$linkend</td>\n";

  // show encounter reason/title
  echo "<td valign='top'>$linkbeg" . $reason_string . "$linkend</td>\n";

  // show user who created the encounter
  echo "<td valign='top'>$linkbeg" . $row['user'] . "$linkend</td>\n";

  echo "</tr>\n";
}
?>

</table>

</body>
</html>
