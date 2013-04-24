<?php
/**
* Maintenance for the list of procedure providers.
*
* Copyright (C) 2012 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Rod Roark <rod@sunsetsystems.com>
*/

$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once("../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/htmlspecialchars.inc.php");

$popup = empty($_GET['popup']) ? 0 : 1;

$query = "SELECT pp.* FROM procedure_providers AS pp";
$query .= " ORDER BY pp.name";
$res = sqlStatement($query);
?>
<html>

<head>

<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<title><?php echo xlt('Procedure Providers'); ?></title>

<?php if ($popup) { ?>
<script type="text/javascript" src="../../library/topdialog.js"></script>
<?php } ?>
<script type="text/javascript" src="../../library/dialog.js"></script>

<script language="JavaScript">

<?php if ($popup) require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

// Callback from popups to refresh this display.
function refreshme() {
 // location.reload();
 document.forms[0].submit();
}

// Process click to pop up the add window.
function doedclick_add() {
 top.restoreSession(); 
 dlgopen('procedure_provider_edit.php?ppid=0', '_blank', 700, 550);
}

// Process click to pop up the edit window.
function doedclick_edit(ppid) {
 top.restoreSession();
 dlgopen('procedure_provider_edit.php?ppid=' + ppid, '_blank', 700, 550);
}

</script>

</head>

<body class="body_top">

<div id="addressbook_list">
<form method='post' action='procedure_provider_list.php'>

<table>
 <tr class='search'> <!-- bgcolor='#ddddff' -->
  <td>
   <input type='submit' class='button' name='form_search' value='<?php echo xla("Refresh")?>' />
   <input type='button' class='button' value='<?php echo xla("Add New"); ?>' onclick='doedclick_add()' />
  </td>
 </tr>
</table>

<table>
 <tr class='head'>
  <td title='<?php echo xla('Click to view or edit'); ?>'><?php echo xlt('Name'); ?></td>
  <td><?php echo xlt('NPI'); ?></td>
  <td><?php echo xlt('Protocol'); ?></td>
 </tr>

<?php
 $encount = 0;
 while ($row = sqlFetchArray($res)) {
  ++$encount;
  $bgclass = (($encount & 1) ? "evenrow" : "oddrow");

  if (acl_check('admin', 'practice' )) {
   $trTitle = xl('Edit') . ' ' . $row['name'];
   echo " <tr class='detail $bgclass' style='cursor:pointer' " .
        "onclick='doedclick_edit(" . $row['ppid'] . ")' title='" . attr($trTitle) . "'>\n"; 
  }
  else {
   $trTitle = $displayName . " (" . xl("Not Allowed to Edit") . ")";
   echo " <tr class='detail $bgclass' title='" . attr($trTitle) . "'>\n";
  }
  echo "  <td>" . text($row['name']    ) . "</td>\n";
  echo "  <td>" . text($row['npi']     ) . "</td>\n";
  echo "  <td>" . text($row['protocol']) . "</td>\n";
  echo " </tr>\n";
 }
?>
</table>

</body>
</html>
