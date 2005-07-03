<?
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report simply lists all players/patients by name within
 // squad.  It is applicable only for sports teams.

 include_once("../globals.php");
 include_once("../../library/patient.inc");

 $squads = array(
  'None',
  'Senior',
  'Academy',
  'Ladies'
 );

 $fitnesses = array(
  'Full Play',
  'Full Training',
  'Restricted Training',
  'Injured Out'
 );

 $fitcolors = array(
  '#00ff00',
  '#ffff00',
  '#ff8800',
  '#ff3333'
 );

 $alertmsg = ''; // not used yet but maybe later

 $query = "SELECT * FROM patient_data ORDER BY squad, lname, fname";
 $res = sqlStatement($query);
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<script language="JavaScript">

 function gopid(pid) {
<? if ($_GET['embed']) { ?>
  top.location = '../patient_file/patient_file.php?set_pid=' + pid;
<? } else { ?>
  opener.top.location = '../patient_file/patient_file.php?set_pid=' + pid;
  window.close();
<? } ?>
 }

</script>

<title>Team Roster</title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<form method='post' action='players_report.php'>

<table border='0' cellpadding='5' cellspacing='0' width='98%'>

 <tr>
  <td height="1" colspan="2">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td align='left'>
   <h2>Team Roster</h2>
  </td>
  <td align='right'>
   <b><? echo date('l, F j, Y') ?></b>
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
   &nbsp;Squad
  </td>
  <td class="dehead">
   &nbsp;Player
  </td>
  <td class="dehead">
   &nbsp;Fitness
  </td>
  <td class="dehead">
   &nbsp;Last Encounter
  </td>
 </tr>
<?
 if ($res) {
  $lastsquad = '';
  while ($row = sqlFetchArray($res)) {
   $patient_id = $row['pid'];
   $squad = $squads[$row['squad']];
   $fitness = $row['fitness'];
   if (! $fitness) $fitness = 1;
   $query = "SELECT date, reason " .
    "FROM form_encounter WHERE " .
    "pid = '$patient_id' " .
    "ORDER BY date DESC LIMIT 1";
   $erow = sqlQuery($query);
?>
 <tr bgcolor="<? echo $fitcolors[$fitness-1] ?>">
  <td class="detail">
   &nbsp;<? echo ($squad == $lastsquad) ? "" : $squad ?>
  </td>
  <td class="detail">
   &nbsp;<a href='javascript:gopid(<? echo $patient_id ?>)'><? echo $row['lname'] . ", " . $row['fname'] ?></a>
  </td>
  <td class="detail">
   <? echo $fitnesses[$fitness-1] ?>&nbsp;
  </td>
  <td class="detail">
   &nbsp;<? echo substr($erow['date'], 0, 10) . ' ' . $erow['reason'] ?>&nbsp;
  </td>
 </tr>
<?
   $lastsquad = $squad;
  }
 }
?>

</table>

</form>
</center>
<script>
<?
	if ($alertmsg) {
		echo " alert('$alertmsg');\n";
	}
?>
</script>
</body>
</html>
