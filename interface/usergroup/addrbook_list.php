<?php
 // Copyright (C) 2006-2010 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 require_once("../globals.php");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/formdata.inc.php");
 require_once("$srcdir/options.inc.php");

 $popup = empty($_GET['popup']) ? 0 : 1;

 $form_fname = formData("form_fname","P",true);
 $form_lname = formData("form_lname","P",true);
 $form_specialty = formData("form_specialty","P",true);
 $form_abook_type = formData("form_abook_type","R",true);
 $form_external = $_POST['form_external'] ? 1 : 0;

$query = "SELECT u.*, lo.option_id AS ab_name FROM users AS u " .
  "LEFT JOIN list_options AS lo ON " .
  "list_id = 'abook_type' AND option_id = u.abook_type " .
  "WHERE u.active = 1 AND ( u.authorized = 1 OR u.username = '' ) ";
if ($form_lname) $query .= "AND u.lname LIKE '$form_lname%' ";
if ($form_fname) $query .= "AND u.fname LIKE '$form_fname%' ";
if ($form_specialty) $query .= "AND u.specialty LIKE '%$form_specialty%' ";
if ($form_abook_type) $query .= "AND u.abook_type LIKE '$form_abook_type' ";
if ($form_external) $query .= "AND u.username = '' ";
$query .= "ORDER BY u.lname, u.fname, u.mname LIMIT 500";
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
   <?php xl('First Name','e')?>:
   <input type='text' name='form_fname' size='10' value='<?php echo htmlspecialchars(strip_escape_custom($_POST['form_fname']),ENT_QUOTES); ?>'
    class='inputtext' title='<?php xl("All or part of the first name","e") ?>' />&nbsp;
   <?php xl('Last Name','e')?>:
   <input type='text' name='form_lname' size='10' value='<?php echo htmlspecialchars(strip_escape_custom($_POST['form_lname']),ENT_QUOTES); ?>'
    class='inputtext' title='<?php xl("All or part of the last name","e") ?>' />&nbsp;
   <?php xl('Specialty','e')?>:
   <input type='text' name='form_specialty' size='10' value='<?php echo htmlspecialchars(strip_escape_custom($_POST['form_specialty']),ENT_QUOTES); ?>'
    class='inputtext' title='<?php xl("Any part of the desired specialty","e") ?>' />&nbsp;
<?php
  echo xl('Type') . ": ";
  // Generates a select list named form_abook_type:
  generate_form_field(array('data_type'=>1, 'field_id'=>'abook_type',
    'list_id'=>'abook_type','empty_title'=>'All'),
    strip_escape_custom($_REQUEST['form_abook_type']));
?>
   <input type='checkbox' name='form_external' value='1'<?php if ($form_external) echo ' checked'; ?>
    title='<?php xl("Omit internal users?","e") ?>' />
   <?php xl('External Only','e')?>&nbsp;&nbsp;
   <input type='submit' class='button' name='form_search' value='<?php xl("Search","e")?>' />
  </td>
  <td align='right'>
   <input type='button' class='button' value='<?php xl("Add New","e"); ?>' onclick='doedclick(0)' />
  </td>
 </tr>
</table>

<table>
 <tr class='head'>
  <td title=<?php xl('Click to view or edit','e','\'','\''); ?>><?php xl('Name','e'); ?></td>
  <td><?php xl('Local','e'); ?></td><!-- empty for external -->
  <td><?php xl('Type','e'); ?></td>
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
  $trTitle = xl('Edit','','',' ') . $row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname'];
  echo " <tr class='detail $bgclass' style='cursor:pointer' " .
       "onclick='doedclick(" . $row['id'] . ")' title='$trTitle'>\n";
  echo "  <td>" . $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] . "</td>\n";
  echo "  <td>" . ($username ? '*' : '') . "</td>\n";
  echo "  <td>" . generate_display_field(array('data_type'=>'1','list_id'=>'abook_type'),$row['ab_name']) . "</td>\n";
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
