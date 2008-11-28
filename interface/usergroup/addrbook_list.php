<?php
 // Copyright (C) 2006-2008 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 require_once("../globals.php");
 require_once("$srcdir/acl.inc");

 $popup = empty($_GET['popup']) ? 0 : 1;

 $form_fname = trim($_POST['form_fname']);
 $form_lname = trim($_POST['form_lname']);
 $form_specialty = trim($_POST['form_specialty']);

 $query = "SELECT * FROM users WHERE active = 1 AND ( authorized = 1 OR username = '' ) ";
 if ($form_lname) $query .= "AND lname LIKE '$form_lname%' ";
 if ($form_fname) $query .= "AND fname LIKE '$form_fname%' ";
 if ($form_specialty) $query .= "AND specialty LIKE '%$form_specialty%' ";
 $query .= "ORDER BY lname, fname, mname LIMIT 500";
 $res = sqlStatement($query);
?>
<html>

<head>

<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<title><?php xl('Address Book','e'); ?></title>

<!-- style tag moved into proper CSS file -->

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

// Process click to pop up the add/edit window.
function doedclick(userid) {
 dlgopen('addrbook_edit.php?userid=' + userid, '_blank', 700, 550);
}

</script>

</head>

<body class="body_top">

<div id="addressbook_list">
<form method='post' action='addrbook_list.php'>

<table>
 <tr class='search'> <!-- bgcolor='#ddddff' -->
  <td>
   <?php xl('First Name:','e')?>
   <input type='text' name='form_fname' size='10' value='<?php echo $form_fname; ?>'
    class='inputtext' title='<?php xl("All or part of the first name","e") ?>' />&nbsp;
   <?php xl('Last Name:','e')?>
   <input type='text' name='form_lname' size='10' value='<?php echo $form_lname; ?>'
    class='inputtext' title='<?php xl("All or part of the last name","e") ?>' />&nbsp;
   <?php xl('Specialty:','e')?>
   <input type='text' name='form_specialty' size='10' value='<?php echo $form_specialty; ?>'
    class='inputtext' title='<?php xl("Any part of the desired specialty","e") ?>' />&nbsp;&nbsp;
   <input type='submit' class='button' name='form_search' value='<?php xl("Search","e")?>' />
  </td>
  <td align='right'>
   <input type='button' class='button' value='Add New' onclick='doedclick(0)' />
  </td>
 </tr>
</table>

<table>
 <tr class='head'>
  <td title='Click to view or edit'><?php xl('Name','e'); ?></td>
  <td><?php xl('Local','e'); ?></td><!-- empty for external -->
  <td><?php xl('Specialty','e'); ?></td>
  <td><?php xl('Phone','e'); ?></td>
  <td><?php xl('Mobile','e'); ?></td>
  <td><?php xl('Fax','e'); ?></td>
  <td><?php xl('Email','e'); ?></td>
  <td><?php xl('Street','e'); ?></td>
  <td><?php xl('City','e'); ?></td>
  <td><?php xl('State','e'); ?></td>
  <td><?php xl('Postal','e'); ?></td>
 </tr>

<?php
 $encount = 0;
 while ($row = sqlFetchArray($res)) {
  ++$encount;
  //$bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
  $bgclass = (($encount & 1) ? "evenrow" : "oddrow");
  $username = $row['username'];
  if (! $row['active']) $username = '--';
  $trTitle = "Edit ". $row['fname'] . ' ' . $row['mname'] . $row['lname'];
  echo " <tr class='detail $bgclass' style='cursor:pointer' " .
       "onclick='doedclick(" . $row['id'] . ")' title='$trTitle'>\n";
  echo "  <td>" . $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] . "</td>\n";
  echo "  <td>" . ($username ? '*' : '') . "</td>\n";
  echo "  <td>" . $row['specialty'] . "</td>\n";
  echo "  <td>" . $row['phonew1']   . "</td>\n";
  echo "  <td>" . $row['phonecell'] . "</td>\n";
  echo "  <td>" . $row['fax']       . "</td>\n";
  echo "  <td>" . $row['email']     . "</td>\n";
  echo "  <td>" . $row['street']    . "</td>\n";
  echo "  <td>" . $row['city']      . "</td>\n";
  echo "  <td>" . $row['state']     . "</td>\n";
  echo "  <td>" . $row['zip']       . "</td>\n";
  echo " </tr>\n";
 }
?>
</table>

</body>
</html>
