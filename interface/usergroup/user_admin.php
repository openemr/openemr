<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/md5.js");
require_once("$srcdir/sql.inc");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/formdata.inc.php");
require_once(dirname(__FILE__) . "/../../library/classes/WSProvider.class.php");

if (!$_GET["id"] || !acl_check('admin', 'users'))
  exit();

if ($_GET["mode"] == "update") {
  if ($_GET["username"]) {
    // $tqvar = addslashes(trim($_GET["username"]));
    $tqvar = trim(formData('username','G'));
    $user_data = mysql_fetch_array(sqlStatement("select * from users where id={$_GET["id"]}"));
    sqlStatement("update users set username='$tqvar' where id={$_GET["id"]}");
    sqlStatement("update groups set user='$tqvar' where user='". $user_data["username"]  ."'");
    //echo "query was: " ."update groups set user='$tqvar' where user='". $user_data["username"]  ."'" ;
  }
  if ($_GET["taxid"]) {
    $tqvar = formData('taxid','G');
    sqlStatement("update users set federaltaxid='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["drugid"]) {
    $tqvar = formData('drugid','G');
    sqlStatement("update users set federaldrugid='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["upin"]) {
    $tqvar = formData('upin','G');
    sqlStatement("update users set upin='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["npi"]) {
    $tqvar = formData('npi','G');
    sqlStatement("update users set npi='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["taxonomy"]) {
    $tqvar = formData('taxonomy','G');
    sqlStatement("update users set taxonomy = '$tqvar' where id= {$_GET["id"]}");
  }
  if ($_GET["lname"]) {
    $tqvar = formData('lname','G');
    sqlStatement("update users set lname='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["job"]) {
    $tqvar = formData('job','G');
    sqlStatement("update users set specialty='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["mname"]) {
          $tqvar = formData('mname','G');
          sqlStatement("update users set mname='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["facility_id"]) {
          $tqvar = formData('facility_id','G');
          sqlStatement("update users set facility_id = '$tqvar' where id = {$_GET["id"]}");
          //(CHEMED) Update facility name when changing the id
          sqlStatement("UPDATE users, facility SET users.facility = facility.name WHERE facility.id = '$tqvar' AND users.id = {$_GET["id"]}");
          //END (CHEMED)
  }
  if ($GLOBALS['restrict_user_facility'] && $_GET["schedule_facility"]) {
	  sqlStatement("delete from users_facility
	    where tablename='users'
	    and table_id={$_GET["id"]}
	    and facility_id not in (" . implode(",", $_GET['schedule_facility']) . ")");
	  foreach($_GET["schedule_facility"] as $tqvar) {
      sqlStatement("replace into users_facility set 
		    facility_id = '$tqvar',
		    tablename='users',
		    table_id = {$_GET["id"]}");
    }
  }
  if ($_GET["fname"]) {
          $tqvar = formData('fname','G');
          sqlStatement("update users set fname='$tqvar' where id={$_GET["id"]}");
  }

  //(CHEMED) Calendar UI preference
  if ($_GET["cal_ui"]) {
          $tqvar = formData('cal_ui','G');
          sqlStatement("update users set cal_ui = '$tqvar' where id = {$_GET["id"]}");
      
          // added by bgm to set this session variable if the current user has edited
	  //   their own settings
	  if ($_SESSION['authId'] == $_GET["id"]) {
	    $_SESSION['cal_ui'] = $tqvar;   
	  }
  }
  //END (CHEMED) Calendar UI preference

  if ($_GET["newauthPass"] && $_GET["newauthPass"] != "d41d8cd98f00b204e9800998ecf8427e") { // account for empty
    $tqvar = formData('newauthPass','G');
    sqlStatement("update users set password='$tqvar' where id={$_GET["id"]}");
  }

  // for relay health single sign-on
  if ($_GET["ssi_relayhealth"]) {
    $tqvar = formData('ssi_relayhealth','G');
    sqlStatement("update users set ssi_relayhealth = '$tqvar' where id = {$_GET["id"]}");
  }

  $tqvar  = $_GET["authorized"] ? 1 : 0;
  $actvar = $_GET["active"]     ? 1 : 0;
  $calvar = $_GET["calendar"]   ? 1 : 0;

  sqlStatement("UPDATE users SET authorized = $tqvar, active = $actvar, " .
    "calendar = $calvar, see_auth = '" . $_GET['see_auth'] . "' WHERE " .
    "id = {$_GET["id"]}");

  if ($_GET["comments"]) {
    $tqvar = formData('comments','G');
    sqlStatement("update users set info = '$tqvar' where id = {$_GET["id"]}");
  }

  if (isset($phpgacl_location) && acl_check('admin', 'acl')) {
    // Set the access control group of user
    $user_data = mysql_fetch_array(sqlStatement("select username from users where id={$_GET["id"]}"));
    set_user_aro($_GET['access_group'], $user_data["username"],
      formData('fname','G'), formData('mname','G'), formData('lname','G'));
  }

  $ws = new WSProvider($_GET['id']);

  // On a successful update, return to the users list.
  include("usergroup_admin.php");
  exit(0);
}

$res = sqlStatement("select * from users where id={$_GET["id"]}");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
                $result[$iter] = $row;
$iter = $result[0];
?>
<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">

<script language="JavaScript">

function authorized_clicked() {
 var f = document.forms[0];
 f.calendar.disabled = !f.authorized.checked;
 f.calendar.checked  =  f.authorized.checked;
}

</script>

</head>
<body class="body_top">

<a href="usergroup_admin.php"><span class="title"><?php xl('User Administration','e'); ?></span></a>
<br><br>

<FORM NAME="user_form" METHOD="GET" ACTION="user_admin.php">
<TABLE border=0 cellpadding=0 cellspacing=0>
<TR>
<TD><span class=text><?php xl('Username','e'); ?>: </span></TD><TD><input type=entry name=username size=20 value="<?php echo $iter["username"]; ?>" disabled> &nbsp;</td>
<TD><span class=text><?php xl('Password','e'); ?>: </span></TD><TD class='text'><input type=entry name=clearPass size=20 value=""> * <?php xl('Leave blank to keep password unchanged.','e'); ?></td>
</TR>

<TR>
<td><span class="text">&nbsp;</span></td><td>&nbsp;</td>
<TD><span class=text><?php xl('Provider','e'); ?>: </TD>
<TD>
 <input type="checkbox" name="authorized" onclick="authorized_clicked()"<?php
  if ($iter["authorized"]) echo " checked"; ?> />

 &nbsp;&nbsp;<span class='text'><?php xl('Calendar','e'); ?>:
 <input type="checkbox" name="calendar"<?php
  if ($iter["calendar"]) echo " checked";
  if (!$iter["authorized"]) echo " disabled"; ?> />

 &nbsp;&nbsp;<span class='text'><?php xl('Active','e'); ?>:
 <input type="checkbox" name="active"<?php if ($iter["active"]) echo " checked"; ?> />
</TD>
</TR>

<TR>
<TD><span class=text><?php xl('First Name','e'); ?>: </span></TD>
<TD><input type=entry name=fname size=20 value="<?php echo $iter["fname"]; ?>"></td>
<td><span class=text><?php xl('Middle Name','e'); ?>: </span></TD><td><input type=entry name=mname size=20 value="<?php echo $iter["mname"]; ?>"></td>
</TR>

<TR>
<td><span class=text><?php xl('Last Name','e'); ?>: </span></td><td><input type=entry name=lname size=20 value="<?php echo $iter["lname"]; ?>"></td>
<td><span class=text><?php xl('Default Facility','e'); ?>: </span></td><td><select name=facility_id>
<?php
$fres = sqlStatement("select * from facility where service_location != 0 order by name");
if ($fres) {
for ($iter2 = 0; $frow = sqlFetchArray($fres); $iter2++)
                $result[$iter2] = $frow;
foreach($result as $iter2) {
?>
<option value="<?php echo $iter2['id']; ?>" <?php if ($iter['facility_id'] == $iter2['id']) echo "selected"; ?>><?php echo $iter2['name']; ?></option>
<?php
}
}
?>
</select></td>
</tr>

<?php if ($GLOBALS['restrict_user_facility']) { ?>
<tr>
 <td colspan=2>&nbsp;</td>
 <td><span class=text><?php xl('Schedule Facilities:', 'e');?></td>
 <td>
  <select name="schedule_facility[]" multiple>
<?php
  $userFacilities = getUserFacilities($_GET['id']);
  $ufid = array();
  foreach($userFacilities as $uf)
    $ufid[] = $uf['id'];
  $fres = sqlStatement("select * from facility where service_location != 0 order by name");
  if ($fres) {
    while($frow = sqlFetchArray($fres)): 
?>
   <option <?php echo in_array($frow['id'], $ufid) || $frow['id'] == $iter['facility_id'] ? "selected" : null ?>
    value="<?php echo $frow['id'] ?>"><?php echo $frow['name'] ?></option>
<?php
  endwhile;
}
?>
  </select>
 </td>
</tr>
<?php } ?>

<TR>
<TD><span class=text><?php xl('Federal Tax ID','e'); ?>: </span></TD><TD><input type=text name=taxid size=20 value="<?php echo $iter["federaltaxid"]?>"></td>
<TD><span class=text><?php xl('Federal Drug ID','e'); ?>: </span></TD><TD><input type=text name=drugid size=20 value="<?php echo $iter["federaldrugid"]?>"></td>
</TR>

<tr>
<td><span class="text"><?php xl('UPIN','e'); ?>: </span></td><td><input type="text" name="upin" size="20" value="<?php echo $iter["upin"]?>"></td>
<td class='text'><?php xl('See Authorizations','e'); ?>: </td>
<td><select name="see_auth">
<?php
 foreach (array(1 => xl('None'), 2 => xl('Only Mine'), 3 => xl('All')) as $key => $value)
 {
  echo " <option value='$key'";
  if ($key == $iter['see_auth']) echo " selected";
  echo ">$value</option>\n";
 }
?>
</select></td>
</tr>

<tr>
<td><span class="text"><?php xl('NPI','e'); ?>: </span></td><td><input type="text" name="npi" size="20" value="<?php echo $iter["npi"]?>"></td>
<td><span class="text"><?php xl('Job Description','e'); ?>: </span></td><td><input type="text" name="job" size="20" value="<?php echo $iter["specialty"]?>"></td>
</tr>

<?php if (!empty($GLOBALS['ssi']['rh'])) { ?>
<tr>
<td><span class="text"><?php xl('Relay Health ID', 'e'); ?>: </span></td>
<td><input type="password" name="ssi_relayhealth" size="20" value="<?php echo $iter["ssi_relayhealth"]; ?>"></td>
</tr>
<?php } ?>

<!-- (CHEMED) Calendar UI preference -->
<tr>
<td><span class="text"><?php xl('Taxonomy','e'); ?>: </span></td>
<td><input type="text" name="taxonomy" size="20" value="<?php echo $iter["taxonomy"]?>"></td>
<td><span class="text"><?php xl('Calendar UI','e'); ?>: </span></td><td><select name="cal_ui">
<?php
 foreach (array(1 => xl('Default'), 2 => xl('Fancy'), 3 => xl('Outlook')) as $key => $value)
 {
  echo " <option value='$key'";
  if ($key == $iter['cal_ui']) echo " selected";
  echo ">$value</option>\n";
 }
?>
</select></td>
</tr>
<!-- END (CHEMED) Calendar UI preference -->

<?php
 // Collect the access control group of user
 if (isset($phpgacl_location) && acl_check('admin', 'acl')) {
?>
  <tr>
  <td class='text'><?php xl('Access Control','e'); ?>:</td>
  <td><select name="access_group[]" multiple>
  <?php
   $list_acl_groups = acl_get_group_title_list();
   $username_acl_groups = acl_get_group_titles($iter["username"]);
   foreach ($list_acl_groups as $value) {
    if (($username_acl_groups) && in_array($value,$username_acl_groups)) {
     // Modified 6-2009 by BM - Translate group name if applicable
     echo " <option value='$value' selected>" . xl_gacl_group($value) . "</option>\n";
    }
    else {
     // Modified 6-2009 by BM - Translate group name if applicable
     echo " <option value='$value'>" . xl_gacl_group($value) . "</option>\n";
    }
   }
  ?>
  </select></td></tr>
<?php
 }
?>

</tr>
</table>

<span class=text><?php xl('Additional Info','e'); ?>:</span><br>
<textarea name="comments" wrap=auto rows=4 cols=30><?php echo $iter["info"];?></textarea>

<br>&nbsp;&nbsp;&nbsp;
<INPUT TYPE="HIDDEN" NAME="id" VALUE="<?php echo $_GET["id"]; ?>">
<INPUT TYPE="HIDDEN" NAME="mode" VALUE="update">
<INPUT TYPE="HIDDEN" NAME="newauthPass" VALUE="">
<INPUT TYPE="Submit" VALUE=<?php xl('Save Changes','e'); ?> onClick="javascript:this.form.newauthPass.value=MD5(this.form.clearPass.value);this.form.clearPass.value='';">
&nbsp;&nbsp;&nbsp;
<a href="usergroup_admin.php" class=link_submit>[<?php xl('Back','e'); ?>]</font></a>
</FORM>

<br><br>
</BODY>
</HTML>

<?php
//  d41d8cd98f00b204e9800998ecf8427e == blank
?>
