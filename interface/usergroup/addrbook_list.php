<?php
/**
 * The address book entry editor.
 * Available from Administration->Addr Book in the concurrent layout.
 *
 * Copyright (C) 2006-2010 Rod Roark <rod@sunsetsystems.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * Improved slightly by tony@mi-squared.com 2011, added organization to view
 * and search
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @link    http://open-emr.org
 */

 //SANITIZE ALL ESCAPES
 $sanitize_all_escapes=true;
 //

 //STOP FAKE REGISTER GLOBALS
 $fake_register_globals=false;
 //

 require_once("../globals.php");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/formdata.inc.php");
 require_once("$srcdir/options.inc.php");
 require_once("$srcdir/htmlspecialchars.inc.php");

 $popup = empty($_GET['popup']) ? 0 : 1;

 $form_fname = trim($_POST['form_fname']);
 $form_lname = trim($_POST['form_lname']);
 $form_specialty = trim($_POST['form_specialty']);
 $form_organization = trim($_POST['form_organization']);
 $form_abook_type = trim($_REQUEST['form_abook_type']);
 $form_external = $_POST['form_external'] ? 1 : 0;

$sqlBindArray = array();
$query = "SELECT u.*, lo.option_id AS ab_name, lo.option_value as ab_option FROM users AS u " .
  "LEFT JOIN list_options AS lo ON " .
  "list_id = 'abook_type' AND option_id = u.abook_type " .
  "WHERE u.active = 1 AND ( u.authorized = 1 OR u.username = '' ) ";
if ($form_organization) {
 $query .= "AND u.organization LIKE ? ";
 array_push($sqlBindArray,$form_organization."%");
}
if ($form_lname) {
 $query .= "AND u.lname LIKE ? ";
 array_push($sqlBindArray,$form_lname."%");
}
if ($form_fname) {
 $query .= "AND u.fname LIKE ? ";
 array_push($sqlBindArray,$form_fname."%");
}
if ($form_specialty) {
 $query .= "AND u.specialty LIKE ? ";
 array_push($sqlBindArray,"%".$form_specialty."%");
}
if ($form_abook_type) {
 $query .= "AND u.abook_type LIKE ? ";
 array_push($sqlBindArray,$form_abook_type);
}
if ($form_external) {
 $query .= "AND u.username = '' ";
}
if ($form_lname) { 
    $query .= "ORDER BY u.lname, u.fname, u.mname";
} else if ($form_organization) {
    $query .= "ORDER BY u.organization";
} else {
    $query .= "ORDER BY u.organization, u.lname, u.fname";
}
$query .= " LIMIT 500";
$res = sqlStatement($query,$sqlBindArray);
?>
<html>

<head>

<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<title><?php echo xlt('Address Book'); ?></title>

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

// Process click to pop up the add window.
function doedclick_add(type) {
 top.restoreSession(); 
 dlgopen('addrbook_edit.php?type=' + type, '_blank', 700, 550);
}

// Process click to pop up the edit window.
function doedclick_edit(userid) {
 top.restoreSession();
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
   <?php echo xlt('Organization')?>:
   <input type='text' name='form_organization' size='10' value='<?php echo attr($_POST['form_organization']); ?>'
    class='inputtext' title='<?php echo xla("All or part of the organization") ?>' />&nbsp;
   <?php echo xlt('First Name')?>:
   <input type='text' name='form_fname' size='10' value='<?php echo attr($_POST['form_fname']); ?>'
    class='inputtext' title='<?php echo xla("All or part of the first name") ?>' />&nbsp;
   <?php echo xlt('Last Name')?>:
   <input type='text' name='form_lname' size='10' value='<?php echo attr($_POST['form_lname']); ?>'
    class='inputtext' title='<?php echo xla("All or part of the last name") ?>' />&nbsp;
   <?php echo xlt('Specialty')?>:
   <input type='text' name='form_specialty' size='10' value='<?php echo attr($_POST['form_specialty']); ?>'
    class='inputtext' title='<?php echo xla("Any part of the desired specialty") ?>' />&nbsp;
<?php
  echo xlt('Type') . ": ";
  // Generates a select list named form_abook_type:
  echo generate_select_list("form_abook_type", "abook_type", $_REQUEST['form_abook_type'], '', 'All');
?>
   <input type='checkbox' name='form_external' value='1'<?php if ($form_external) echo ' checked'; ?>
    title='<?php echo xla("Omit internal users?") ?>' />
   <?php echo xlt('External Only')?>&nbsp;&nbsp;
   <input type='submit' title='<?php echo xla("Use % alone in a field to just sort on that column") ?>' class='button' name='form_search' value='<?php echo xla("Search")?>' />
   <input type='button' class='button' value='<?php echo xla("Add New"); ?>' onclick='doedclick_add(document.forms[0].form_abook_type.value)' />
</td>
</tr>
</table>

<table>
 <tr class='head'>
  <td title='<?php echo xla('Click to view or edit'); ?>'><?php echo xlt('Organization'); ?></td>
  <td><?php echo xlt('Name'); ?></td>
  <td><?php echo xlt('Local'); ?></td><!-- empty for external -->
  <td><?php echo xlt('Type'); ?></td>
  <td><?php echo xlt('Specialty'); ?></td>
  <td><?php echo xlt('Phone'); ?></td>
  <td><?php echo xlt('Mobile'); ?></td>
  <td><?php echo xlt('Fax'); ?></td>
  <td><?php echo xlt('Email'); ?></td>
  <td><?php echo xlt('Street'); ?></td>
  <td><?php echo xlt('City'); ?></td>
  <td><?php echo xlt('State'); ?></td>
  <td><?php echo xlt('Postal'); ?></td>
 </tr>

<?php
 $encount = 0;
 while ($row = sqlFetchArray($res)) {
  ++$encount;
  //$bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");
  $bgclass = (($encount & 1) ? "evenrow" : "oddrow");
  $username = $row['username'];
  if (! $row['active']) $username = '--';

  $displayName = $row['fname'] . ' ' . $row['mname'] . ' ' . $row['lname']; // Person Name

  if ( acl_check('admin', 'practice' ) || (empty($username) && empty($row['ab_name'])) ) {
   // Allow edit, since have access or (no item type and not a local user)
   $trTitle = xl('Edit'). ' ' . $displayName;
   echo " <tr class='detail $bgclass' style='cursor:pointer' " .
        "onclick='doedclick_edit(" . $row['id'] . ")' title='".attr($trTitle)."'>\n"; 
  }
  else {
   // Do not allow edit, since no access and (item is a type or is a local user)
   $trTitle = $displayName . " (" . xl("Not Allowed to Edit") . ")";
   echo " <tr class='detail $bgclass' title='".attr($trTitle)."'>\n";
  }
  echo "  <td>" . text($row['organization']) . "</td>\n";
  echo "  <td>" . text($displayName) . "</td>\n";
  echo "  <td>" . ($username ? '*' : '') . "</td>\n";
  echo "  <td>" . generate_display_field(array('data_type'=>'1','list_id'=>'abook_type'),$row['ab_name']) . "</td>\n";
  echo "  <td>" . text($row['specialty']) . "</td>\n";
  echo "  <td>" . text($row['phonew1'])   . "</td>\n";
  echo "  <td>" . text($row['phonecell']) . "</td>\n";
  echo "  <td>" . text($row['fax'])       . "</td>\n";
  echo "  <td>" . text($row['email'])     . "</td>\n";
  echo "  <td>" . text($row['street'])    . "</td>\n";
  echo "  <td>" . text($row['city'])      . "</td>\n";
  echo "  <td>" . text($row['state'])     . "</td>\n";
  echo "  <td>" . text($row['zip'])       . "</td>\n";
  echo " </tr>\n";
 }
?>
</table>

</body>
</html>
