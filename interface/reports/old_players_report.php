<?php
 // Copyright (C) 2005-2010 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report simply lists all players/patients by name within
 // squad.  It is applicable only for sports teams.

 include_once("../globals.php");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/acl.inc");

 $squads = acl_get_squads();
 $auth_notes_a  = acl_check('encounters', 'notes_a');

 $alertmsg = ''; // not used yet but maybe later

/*********************************************************************
// Get fitness level names and colors.
$PLAYER_FITNESSES = array();
$fres = sqlStatement("SELECT * FROM list_options WHERE " .
  "list_id = 'fitness' ORDER BY seq");
while ($frow = sqlFetchArray($fres)) $PLAYER_FITNESSES[] = $frow['title'];
if (!empty($GLOBALS['fitness_colors'])) $PLAYER_FITCOLORS = $GLOBALS['fitness_colors'];
*********************************************************************/
// Get attributes of the default fitless level.
$fdefault = sqlQuery("SELECT * FROM list_options WHERE " .
  "list_id = 'fitness' ORDER BY is_default DESC, seq ASC LIMIT 1");
/********************************************************************/

 $query = "SELECT pid, squad, lname, fname FROM " .
  "patient_data"; // ORDER BY squad, lname, fname
 $res = sqlStatement($query);

 // Sort the patients in squad priority order.
 function patient_compare($a, $b) {
  global $squads;
  if ($squads[$a['squad']][3] == $squads[$b['squad']][3]) {
   if ($a['lname'] == $b['lname']) {
    return ($a['fname'] < $b['fname']) ? -1 : 1;
   }
   return ($a['lname'] < $b['lname']) ? -1 : 1;
  }
  // The squads are different so compare their order attributes,
  // or unassigned squads sort last.
  if (! $squads[$a['squad']][3]) return 1;
  if (! $squads[$b['squad']][3]) return -1;
  return ($squads[$a['squad']][2] < $squads[$b['squad']][2]) ? -1 : 1;
 }
 $ordres = array();
 if ($res) {
  while ($row = sqlFetchArray($res)) $ordres[] = $row;
  usort($ordres, "patient_compare");
 }
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">

<script language="JavaScript">

 function gopid(pid) {
<?php
$maintop = $_GET['embed'] ? "top" : "opener.top";
echo "  $maintop.restoreSession();\n";
if ($GLOBALS['concurrent_layout']) {

    echo "  $maintop.RTop.location = '../patient_file/summary/demographics.php?set_pid=' + pid;\n";

} else {
  echo "  $maintop.location = '../patient_file/patient_file.php?set_pid=' + pid;\n";
}
if (empty($_GET['embed'])) echo "  window.close();\n";
?>
 }

</script>

<title><?php  xl('Team Roster','e'); ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<form method='post' action='old_players_report.php'>

<table border='0' cellpadding='5' cellspacing='0' width='98%'>

 <tr>
  <td height="1" colspan="2">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td align='left'>
   <h2><?php  xl('Team Roster','e'); ?></h2>
  </td>
  <td align='right'>
   <b><?php  echo date('l, F j, Y') ?></b>
  </td>
 </tr>

 <tr>
  <td height="1" colspan="2">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   &nbsp;<?php  xl('Squad','e'); ?>
  </td>
  <td class="dehead">
   &nbsp;<?php  xl('Player','e'); ?>
  </td>
  <td class="dehead">
   &nbsp;<?php  xl('Fitness','e'); ?>
  </td>
  <td class="dehead">
   &nbsp;<?php  xl('Last Encounter','e'); ?>
  </td>
 </tr>
<?php 
 // if ($res) {
  $lastsquad = '';
  foreach ($ordres as $row) {
   $squadvalue = $row['squad'];
   $squadname = $squads[$squadvalue][3];
   if ($squadname) {
    if (! acl_check('squads', $squadvalue)) continue;
   } else {
    $squadname = "None";
   }
   $patient_id = $row['pid'];

   /******************************************************************
   $fitness = $row['fitness'];
   if (! $fitness) $fitness = 1;
   ******************************************************************/
    $date = date('Y-m-d');
    $dfrow = sqlQuery("SELECT df.*, lf.title AS lf_title, lf.mapping AS lf_mapping " .
      "FROM daily_fitness AS df " .
      "LEFT JOIN list_options AS lf ON lf.list_id = 'fitness' AND lf.option_id = df.fitness " .
      "WHERE df.pid = '$patient_id' AND df.date = '$date'");
    if (empty($dfrow)) {
      $dfrow = array(
        'fitness' => $fdefault['option_id'],
        'lf_title' => $fdefault['title'],
        'lf_mapping' => $fdefault['mapping'],
      );
    }
    $mapping = explode(':', $dfrow['lf_mapping']);
    $bgcolor = $mapping[0];
   /*****************************************************************/

   $query = "SELECT date, reason " .
    "FROM form_encounter WHERE " .
    "pid = '$patient_id' " .
    "ORDER BY date DESC LIMIT 1";
   $erow = sqlQuery($query);
?>
 <tr>
  <td class="detail">
   &nbsp;<?php  echo ($squadname == $lastsquad) ? "" : $squadname ?>
  </td>
  <td class="detail" bgcolor="<?php echo $bgcolor ?>">
   &nbsp;<a href='javascript:gopid(<?php  echo $patient_id ?>)' style='color:#000000'><?php  echo $row['lname'] . ", " . $row['fname'] ?></a>
  </td>
  <td class="detail" bgcolor="<?php echo $bgcolor ?>">
   <?php echo $dfrow['lf_title'] ?>&nbsp;
  </td>
  <td class="detail" bgcolor="<?php echo $bgcolor ?>">
   &nbsp;<?php
    if ($auth_notes_a) {
     echo substr($erow['date'], 0, 10) . ' ' . $erow['reason'];
    } else {
     echo '(No access)';
    }
   ?>&nbsp;
  </td>
 </tr>
<?php 
   $lastsquad = $squadname;
  }
 // }
?>

</table>

</form>
</center>
<script>
<?php 
	if ($alertmsg) {
		echo " alert('$alertmsg');\n";
	}
?>
</script>
</body>
</html>
