<?php
require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/md5.js");
require_once("$srcdir/sql.inc");
require_once(dirname(__FILE__) . "/../../library/classes/WSProvider.class.php");
?>

<html>
<head>

<link rel=stylesheet href="<?php echo $css_header; ?>" type="text/css">

</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<a href="usergroup_admin.php"><span class="title"><?php xl('User Administration','e'); ?></span></a>
<br><br>

<?php
if (!$_GET["id"] || !acl_check('admin', 'users'))
  exit();

if ($_GET["mode"] == "update") {
  if ($_GET["username"]) {
    $tqvar = addslashes($_GET["username"]);
    $user_data = mysql_fetch_array(sqlStatement("select * from users where id={$_GET["id"]}"));
    sqlStatement("update users set username='$tqvar' where id={$_GET["id"]}");
    sqlStatement("update groups set user='$tqvar' where user='". $user_data["username"]  ."'");
    //echo "query was: " ."update groups set user='$tqvar' where user='". $user_data["username"]  ."'" ;
  }
  if ($_GET["taxid"]) {
    $tqvar = addslashes($_GET["taxid"]);
    sqlStatement("update users set federaltaxid='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["drugid"]) {
    $tqvar = addslashes($_GET["drugid"]);
    sqlStatement("update users set federaldrugid='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["upin"]) {
    $tqvar = addslashes($_GET["upin"]);
    sqlStatement("update users set upin='$tqvar' where id={$_GET["id"]}");
  }
    if ($_GET["npi"]) {
    $tqvar = addslashes($_GET["npi"]);
    sqlStatement("update users set npi='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["lname"]) {
    $tqvar = addslashes($_GET["lname"]);
    sqlStatement("update users set lname='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["job"]) {
    $tqvar = addslashes($_GET["job"]);
    sqlStatement("update users set specialty='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["mname"]) {
          $tqvar = addslashes($_GET["mname"]);
          sqlStatement("update users set mname='$tqvar' where id={$_GET["id"]}");
  }
  if ($_GET["facility_id"]) {
          $tqvar = addslashes($_GET["facility_id"]);
          sqlStatement("update users set facility_id = '$tqvar' where id = {$_GET["id"]}");
          //(CHEMED) Update facility name when changing the id
          sqlStatement("update users set facility = (SELECT facility.name FROM facility WHERE facility.id = '$tqvar' LIMIT 1) where id = {$_GET["id"]}");
          //END (CHEMED)
  }
  if ($_GET["fname"]) {
          $tqvar = addslashes($_GET["fname"]);
          sqlStatement("update users set fname='$tqvar' where id={$_GET["id"]}");
  }

  //(CHEMED) Calendar UI preference
  if ($_GET["cal_ui"]) {
          $tqvar = addslashes($_GET["cal_ui"]);
          sqlStatement("update users set cal_ui = '$tqvar' where id = {$_GET["id"]}");
  }
  //END (CHEMED) Calendar UI preference

  if ($_GET["newauthPass"] && $_GET["newauthPass"] != "d41d8cd98f00b204e9800998ecf8427e") { // account for empty
    $tqvar = addslashes($_GET["newauthPass"]);
    sqlStatement("update users set password='$tqvar' where id={$_GET["id"]}");
  }

  $tqvar  = $_GET["authorized"] ? 1 : 0;
  $actvar = $_GET["active"]     ? 1 : 0;

  sqlStatement("UPDATE users SET authorized = $tqvar, active = $actvar, " .
    "see_auth = '" . $_GET['see_auth'] . "' WHERE " .
    "id = {$_GET["id"]}");

  if ($_GET["comments"]) {
    $tqvar = addslashes($_GET["comments"]);
    sqlStatement("update users set info = '$tqvar' where id = {$_GET["id"]}");
  }

  if (isset($phpgacl_location) && acl_check('admin', 'acl') && $_GET["access_group"]) {
    // Set the access control group of user
    $user_data = mysql_fetch_array(sqlStatement("select username from users where id={$_GET["id"]}"));
    set_user_aro($_GET["access_group"], $user_data["username"], $_GET["fname"], $_GET["mname"], $_GET["lname"]);
  }

  $ws = new WSProvider($_GET['id']);
}

$res = sqlStatement("select * from users where id={$_GET["id"]}");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
                $result[$iter] = $row;
$iter = $result[0];

?>
<FORM NAME="user_form" METHOD="GET" ACTION="user_admin.php">
<TABLE border=0 cellpadding=0 cellspacing=0>
<TR>
<TD><span class=text><? xl('Username','e'); ?>: </span></TD><TD><input type=entry name=username size=20 value="<? echo $iter["username"]; ?>" disabled> &nbsp;</td>
<TD><span class=text><? xl('Password','e'); ?>: </span></TD><TD class='text'><input type=password name=clearPass size=20 value=""> * <? xl('Leave blank to keep password unchanged.','e'); ?></td>
</TR>

<TR>
<td><span class="text">&nbsp;</span></td><td>&nbsp;</td>
<TD><span class=text><? xl('Authorized','e'); ?>: </TD>
<TD>
 <input type="checkbox" name="authorized"<?php if ($iter["authorized"]) echo " checked"; ?> />
 &nbsp;&nbsp;<span class='text'><? xl('Active','e'); ?>:
 <input type="checkbox" name="active"<?php if ($iter["active"]) echo " checked"; ?> />
</TD>
</TR>

<TR>
<TD><span class=text><? xl('First Name','e'); ?>: </span></TD>
<TD><input type=entry name=fname size=20 value="<? echo $iter["fname"]; ?>"></td>
<td><span class=text><? xl('Middle Name','e'); ?>: </span></TD><td><input type=entry name=mname size=20 value="<? echo $iter["mname"]; ?>"></td>
</TR>

<TR>
<td><span class=text><? xl('Last Name','e'); ?>: </span></td><td><input type=entry name=lname size=20 value="<? echo $iter["lname"]; ?>"></td>
<td><span class=text><? xl('Default Facility','e'); ?>: </span></td><td><select name=facility_id>
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

<TR>
<TD><span class=text><? xl('Federal Tax ID','e'); ?>: </span></TD><TD><input type=text name=taxid size=20 value="<? echo $iter["federaltaxid"]?>"></td>
<TD><span class=text><? xl('Federal Drug ID','e'); ?>: </span></TD><TD><input type=text name=drugid size=20 value="<? echo $iter["federaldrugid"]?>"></td>
</TR>

<tr>
<td><span class="text"><? xl('UPIN','e'); ?>: </span></td><td><input type="text" name="upin" size="20" value="<? echo $iter["upin"]?>"></td>
<td class='text'><? xl('See Authorizations','e'); ?>: </td>
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
<td><span class="text"><? xl('NPI','e'); ?>: </span></td><td><input type="text" name="npi" size="20" value="<? echo $iter["npi"]?>"></td>

<td><span class="text"><? xl('Job Description','e'); ?>: </span></td><td><input type="text" name="job" size="20" value="<? echo $iter["specialty"]?>"></td>
</tr>
<!-- (CHEMED) Calendar UI preference -->
<tr>
<td><span class="text"><? xl('Calendar UI','e'); ?>: </span></td><td><select name="cal_ui">
<?php
 foreach (array(1 => xl('Default'), 2 => xl('Fancy')) as $key => $value)
 {
  echo " <option value='$key'";
  if ($key == $iter['cal_ui']) echo " selected";
  echo ">$value</option>\n";
 }
?>
</select></td>
<td>&nbsp;</td>
</tr>
<!-- END (CHEMED) Calendar UI preference -->

<?php
 // Collect the access control group of user
 if (isset($phpgacl_location) && acl_check('admin', 'acl')) {
?>
  <tr>
  <td class='text'><? xl('Access Control','e'); ?>:</td>
  <td><select name="access_group[]" multiple>
  <?php
   $list_acl_groups = acl_get_group_title_list();
   $username_acl_groups = acl_get_group_titles($iter["username"]);
   if (!$username_acl_groups) {
    //set default if not yet set
    $username_acl_groups = array('Administrators');
   }
   foreach ($list_acl_groups as $value) {
    if (in_array($value,$username_acl_groups)) {
     echo " <option selected>$value</option>\n";
    }
    else {
     echo " <option>$value</option>\n";
    }
   }
  ?>
  </select></td></tr>
<?php
 }
?>

</tr>
</table>

<span class=text><? xl('Additional Info','e'); ?>:</span><br>
<textarea name="comments" wrap=auto rows=4 cols=30><? echo $iter["info"];?></textarea>

<br>&nbsp;&nbsp;&nbsp;
<INPUT TYPE="HIDDEN" NAME="id" VALUE="<? echo $_GET["id"]; ?>">
<INPUT TYPE="HIDDEN" NAME="mode" VALUE="update">
<INPUT TYPE="HIDDEN" NAME="newauthPass" VALUE="">
<INPUT TYPE="Submit" VALUE=<? xl('Save Changes','e'); ?> onClick="javascript:this.form.newauthPass.value=MD5(this.form.clearPass.value);this.form.clearPass.value='';">
&nbsp;&nbsp;&nbsp;
<a href="usergroup_admin.php" class=link_submit>[<? xl('Back','e'); ?>]</font></a>
</FORM>

<br><br>
</BODY>
</HTML>

<?php
//  d41d8cd98f00b204e9800998ecf8427e == blank
?>