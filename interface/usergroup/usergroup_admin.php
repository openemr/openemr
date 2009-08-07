<?php
require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/md5.js");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formdata.inc.php");
require_once(dirname(__FILE__) . "/../../library/classes/WSProvider.class.php");

$alertmsg = '';

if (isset($_POST["mode"])) {
  if ($_POST["mode"] == "new_user") {
    if ($_POST["authorized"] != "1") {
      $_POST["authorized"] = 0;
    }
    // $_POST["info"] = addslashes($_POST["info"]);

    $res = sqlStatement("select distinct username from users where username != ''");
    $doit = true;
    while ($row = mysql_fetch_array($res)) {
      if ($doit == true && $row['username'] == trim(formData('rumple'))) {
        $doit = false;
      }
    }

    if ($doit == true) {	
      $prov_id = idSqlStatement("insert into users set " .
        "username = '"         . trim(formData('rumple'       )) .
        "', password = '"      . trim(formData('newauthPass'  )) .
        "', fname = '"         . trim(formData('fname'        )) .
        "', mname = '"         . trim(formData('mname'        )) .
        "', lname = '"         . trim(formData('lname'        )) .
        "', federaltaxid = '"  . trim(formData('federaltaxid' )) .
        "', authorized = '"    . trim(formData('authorized'   )) .
        "', info = '"          . trim(formData('info'         )) .
        "', federaldrugid = '" . trim(formData('federaldrugid')) .
        "', upin = '"          . trim(formData('upin'         )) .
        "', npi  = '"          . trim(formData('npi'          )).
        "', taxonomy = '"      . trim(formData('taxonomy'     )) .
        "', facility_id = '"   . trim(formData('facility_id'  )) .
        "', specialty = '"     . trim(formData('specialty'    )) .
        "', see_auth = '"      . trim(formData('see_auth'     )) .
        "'");
      //set the facility name from the selected facility_id
      sqlStatement("UPDATE users, facility SET users.facility = facility.name WHERE facility.id = '" . trim(formData('facility_id')) . "' AND users.username = '" . trim(formData('rumple')) . "'");
	
      sqlStatement("insert into groups set name = '" . trim(formData('groupname')) .
        "', user = '" . trim(formData('rumple')) . "'");

      if (isset($phpgacl_location) && acl_check('admin', 'acl') && trim(formData('rumple'))) {
        // Set the access control group of user
        set_user_aro($_POST['access_group'], trim(formData('rumple')),
          trim(formData('fname')), trim(formData('mname')), trim(formData('lname')));
      }

      $ws = new WSProvider($prov_id);

    } else {
      $alertmsg .= xl('User','','',' ') . trim(formData('rumple')) . xl('already exists.','',' ');
    }
  }
  else if ($_POST["mode"] == "new_group") {
    $res = sqlStatement("select distinct name, user from groups");
    for ($iter = 0; $row = sqlFetchArray($res); $iter++)
      $result[$iter] = $row;
    $doit = 1;
    foreach ($result as $iter) {
      if ($doit == 1 && $iter{"name"} == trim(formData('groupname')) && $iter{"user"} == trim(formData('rumple')))
        $doit--;
    }
    if ($doit == 1) {
      sqlStatement("insert into groups set name = '" . trim(formData('groupname')) .
        "', user = '" . trim(formData('rumple')) . "'");
    } else {
      $alertmsg .= "User " . trim(formData('rumple')) .
        " is already a member of group " . trim(formData('groupname')) . ". ";
    }
  }
}

if (isset($_GET["mode"])) {

  /*******************************************************************
  // This is the code to delete a user.  Note that the link which invokes
  // this is commented out.  Somebody must have figured it was too dangerous.
  //
  if ($_GET["mode"] == "delete") {
    $res = sqlStatement("select distinct username, id from users where id = '" .
      $_GET["id"] . "'");
    for ($iter = 0; $row = sqlFetchArray($res); $iter++)
      $result[$iter] = $row;

    // TBD: Before deleting the user, we should check all tables that
    // reference users to make sure this user is not referenced!

    foreach($result as $iter) {
      sqlStatement("delete from groups where user = '" . $iter{"username"} . "'");
    }
    sqlStatement("delete from users where id = '" . $_GET["id"] . "'");
  }
  *******************************************************************/

  if ($_GET["mode"] == "delete_group") {
    $res = sqlStatement("select distinct user from groups where id = '" .
      $_GET["id"] . "'");
    for ($iter = 0; $row = sqlFetchArray($res); $iter++)
      $result[$iter] = $row;
    foreach($result as $iter)
      $un = $iter{"user"};
    $res = sqlStatement("select name, user from groups where user = '$un' " .
      "and id != '" . $_GET["id"] . "'");

    // Remove the user only if they are also in some other group.  I.e. every
    // user must be a member of at least one group.
    if (sqlFetchArray($res) != FALSE) {
      sqlStatement("delete from groups where id = '" . $_GET["id"] . "'");
    } else {
      $alertmsg .= "You must add this user to some other group before " .
        "removing them from this group. ";
    }
  }
}

$form_inactive = empty($_REQUEST['form_inactive']) ? false : true;

?>
<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_top">

<span class="title"><?php xl('User and Group Administration','e'); ?></span>

<br><br>

<table width=100%>

<tr><td valign=top>
<form name='new_user' method='post' action="usergroup_admin.php"
 onsubmit='return top.restoreSession()'>
<input type=hidden name=mode value=new_user>
<span class="bold"><?php xl('New User','e'); ?>:</span>
</td><td>
<table border=0 cellpadding=0 cellspacing=0>
<tr>
<td><span class="text"><?php xl('Username','e'); ?>: </span></td><td><input type=entry name=rumple size=20> &nbsp;</td>
<td><span class="text"><?php xl('Password','e'); ?>: </span></td><td><input type="entry" size=20 name=stiltskin></td>
</tr>
<tr>
<td><span class="text"><?php xl('Groupname','e'); ?>: </span></td><td>
<select name=groupname>
<?php
$res = sqlStatement("select distinct name from groups");
$result2 = array();
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
  $result2[$iter] = $row;
foreach ($result2 as $iter) {
  print "<option value='".$iter{"name"}."'>" . $iter{"name"} . "</option>\n";
}
?>
</select></td>
<td><span class="text"><?php xl('Authorized','e'); ?>: </span></td><td><input type=checkbox name='authorized' value="1"></td>
</tr>
<tr>
<td><span class="text"><?php xl('First Name','e'); ?>: </span></td><td><input type=entry name='fname' size=20></td>
<td><span class="text"><?php xl('Middle Name','e'); ?>: </span></td><td><input type=entry name='mname' size=20></td>
</tr>
<tr>
<td><span class="text"><?php xl('Last Name','e'); ?>: </span></td><td><input type=entry name='lname' size=20></td>
<td><span class="text"><?php xl('Default Facility','e'); ?>: </span></td><td><select name=facility_id>
<?php
$fres = sqlStatement("select * from facility where service_location != 0 order by name");
if ($fres) {
  for ($iter = 0;$frow = sqlFetchArray($fres);$iter++)
    $result[$iter] = $frow;
  foreach($result as $iter) {
?>
<option value="<?php echo $iter{id};?>"><?php echo $iter{name};?></option>
<?php
  }
}
?>
</select></td>
</tr>
<tr>
<td><span class="text"><?php xl('Federal Tax ID','e'); ?>: </span></td><td><input type=entry name='federaltaxid' size=20></td>
<td><span class="text"><?php xl('Federal Drug ID','e'); ?>: </span></td><td><input type=entry name='federaldrugid' size=20></td>
</tr>
<tr>
<td><span class="text"><?php xl('UPIN','e'); ?>: </span></td><td><input type="entry" name="upin" size="20"></td>
<td class='text'><?php xl('See Authorizations','e'); ?>: </td>
<td><select name="see_auth">
<?php
 foreach (array(1 => xl('None'), 2 => xl('Only Mine'), 3 => xl('All')) as $key => $value)
 {
  echo " <option value='$key'";
  echo ">$value</option>\n";
 }
?>
</select></td>

<tr>
<td><span class="text"><?php xl('NPI','e'); ?>: </span></td><td><input type="entry" name="npi" size="20"></td>
<td><span class="text"><?php xl('Job Description','e'); ?>: </span></td><td><input type="entry" name="specialty" size="20"></td>
</tr>

<!-- (CHEMED) Calendar UI preference -->
<tr>
<td><span class="text"><?php xl('Taxonomy','e'); ?>: </span></td>
<td><input type="entry" name="taxonomy" size="20" value="207Q00000X"></td>
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
 // List the access control groups if phpgacl installed
 if (isset($phpgacl_location) && acl_check('admin', 'acl')) {
?>
  <tr>
  <td class='text'><?php xl('Access Control','e'); ?>:</td>
  <td><select name="access_group[]" multiple>
  <?php
   $list_acl_groups = acl_get_group_title_list();
   $default_acl_group = 'Administrators';
   foreach ($list_acl_groups as $value) {
    if ($default_acl_group == $value) {
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

</table>
<span class="text"><?php xl('Additional Info','e'); ?>: </span><br>
<textarea name=info cols=40 rows=4 wrap=auto></textarea>
<br><input type="hidden" name="newauthPass">
<input type="submit" onClick="javascript:this.form.newauthPass.value=MD5(this.form.stiltskin.value);this.form.stiltskin.value='';" value=<?php xl('Add User','e'); ?>>
</form>
</td>

</tr>

<tr<?php if ($GLOBALS['disable_non_default_groups']) echo " style='display:none'"; ?>>

<td valign=top>
<form name='new_group' method='post' action="usergroup_admin.php"
 onsubmit='return top.restoreSession()'>
<br>
<input type=hidden name=mode value=new_group>
<span class="bold"><?php xl('New Group','e'); ?>:</span>
</td><td>
<span class="text"><?php xl('Groupname','e'); ?>: </span><input type=entry name=groupname size=10>
&nbsp;&nbsp;&nbsp;
<span class="text"><?php xl('Initial User','e'); ?>: </span>
<select name=rumple>
<?php
$res = sqlStatement("select distinct username from users where username != ''");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
  $result[$iter] = $row;
foreach ($result as $iter) {
  print "<option value='".$iter{"username"}."'>" . $iter{"username"} . "</option>\n";
}
?>
</select>
&nbsp;&nbsp;&nbsp;
<input type="submit" value=<?php xl('Add Group','e'); ?>>
</form>
</td>

</tr>

<tr<?php if ($GLOBALS['disable_non_default_groups']) echo " style='display:none'"; ?>>

<td valign=top>
<form name='new_group' method='post' action="usergroup_admin.php"
 onsubmit='return top.restoreSession()'>
<input type=hidden name=mode value=new_group>
<span class="bold"><?php xl('Add User To Group','e'); ?>:</span>
</td><td>
<span class="text">
<?php xl('User','e'); ?>
: </span>
<select name=rumple>
<?php
$res = sqlStatement("select distinct username from users where username != ''");
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
  $result3[$iter] = $row;
foreach ($result3 as $iter) {
  print "<option value='".$iter{"username"}."'>" . $iter{"username"} . "</option>\n";
}
?>
</select>
&nbsp;&nbsp;&nbsp;
<span class="text"><?php xl('Groupname','e'); ?>: </span>
<select name=groupname>
<?php
$res = sqlStatement("select distinct name from groups");
$result2 = array();
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
  $result2[$iter] = $row;
foreach ($result2 as $iter) {
  print "<option value='".$iter{"name"}."'>" . $iter{"name"} . "</option>\n";
}
?>
</select>
&nbsp;&nbsp;&nbsp;
<input type="submit" value=<?php xl('Add User To Group','e'); ?>>
</form>
</td>

</tr>

</table>

<hr>

<form name='userlist' method='post' action='usergroup_admin.php'
 onsubmit='return top.restoreSession()'>
<span class='bold'>
<input type='checkbox' name='form_inactive' value='1' onclick='submit()'
 <?php if ($form_inactive) echo 'checked '; ?>/>
<?php xl('Include inactive users','e'); ?>
</span>
</form>

<table border=0 cellpadding=1 cellspacing=2>
<tr><td><span class="bold"><?php xl('Username','e'); ?></span></td><td><span class="bold"><?php xl('Real Name','e'); ?></span></td><td><span class="bold"><?php xl('Info','e'); ?></span></td><td><span class="bold"><?php xl('Authorized','e'); ?>?</span></td></tr>
<?php
$query = "SELECT * FROM users WHERE username != '' ";
if (!$form_inactive) $query .= "AND active = '1' ";
$query .= "ORDER BY username";
$res = sqlStatement($query);
for ($iter = 0;$row = sqlFetchArray($res);$iter++)
  $result4[$iter] = $row;
foreach ($result4 as $iter) {
  if ($iter{"authorized"}) {
    $iter{"authorized"} = xl('yes');
  } else {
      $iter{"authorized"} = "";
  }

  print "<tr><td><span class='text'>" . $iter{"username"} .
    "</span><a href='user_admin.php?id=" . $iter{"id"} .
    "' class='link_submit' onclick='top.restoreSession()'>(" . xl('Edit') . ")</a>" .
    "</td><td><span class='text'>" .
    $iter{"fname"} . ' ' . $iter{"lname"}."</span></td><td><span class='text'>" .
    $iter{"info"} . "</span></td><td align='center'><span class='text'>" .
    $iter{"authorized"} . "</span></td>";
  print "<td><!--<a href='usergroup_admin.php?mode=delete&id=" . $iter{"id"} .
    "' class='link_submit'>[Delete]</a>--></td>";
  print "</tr>\n";
}
?>

</table>

<hr>

<?php
if (empty($GLOBALS['disable_non_default_groups'])) {
  $res = sqlStatement("select * from groups order by name");
  for ($iter = 0;$row = sqlFetchArray($res);$iter++)
    $result5[$iter] = $row;

  foreach ($result5 as $iter) {
    $grouplist{$iter{"name"}} .= $iter{"user"} .
      "(<a class='link_submit' href='usergroup_admin.php?mode=delete_group&id=" .
      $iter{"id"} . "' onclick='top.restoreSession()'>Remove</a>), ";
  }

  foreach ($grouplist as $groupname => $list) {
    print "<span class='bold'>" . $groupname . "</span><br>\n<span class='text'>" .
      substr($list,0,strlen($list)-2) . "</span><br>\n";
  }
}
?>

<script language="JavaScript">
<?php
  if ($alertmsg = trim($alertmsg)) {
    echo "alert('$alertmsg');\n";
  }
?>
</script>

</body>
</html>
