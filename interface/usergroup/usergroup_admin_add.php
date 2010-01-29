<?php
require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/md5.js");
require_once("$srcdir/sql.inc");
require_once("$srcdir/formdata.inc.php");
require_once(dirname(__FILE__) . "/../../library/classes/WSProvider.class.php");

$alertmsg = '';

?>
<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<script type="text/javascript" src="../../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>

<script language="JavaScript">

function submitform() {
    if (document.forms[0].rumple.value.length>0 && document.forms[0].stiltskin.value.length>0) {
        top.restoreSession();
        document.forms[0].newauthPass.value=MD5(document.forms[0].stiltskin.value);
        document.forms[0].stiltskin.value='';
        document.forms[0].submit();
    } else {
        if (document.forms[0].rumple.value.length<=0)
            {document.forms[0].rumple.focus();document.forms[0].rumple.style.backgroundColor="red";}
        if (document.forms[0].stiltskin.value.length<=0)
            {document.forms[0].stiltskin.focus();document.forms[0].stiltskin.style.backgroundColor="red";}
    }
}

function authorized_clicked() {
     var f = document.forms[0];
     f.calendar.disabled = !f.authorized.checked;
     f.calendar.checked  =  f.authorized.checked;
}

</script>

</head>
<body class="body_top">
<table><tr><td>
<span class="title"><?php xl('Add User','e'); ?></span>&nbsp;</td>
<td>
<a class="css_button" name='form_save' id='form_save' href='#' onclick="submitform()">
	<span><?php xl('Save','e');?></span></a>
<a class="css_button large_button" id='cancel' href='#'>
	<span class='css_button_span large_button_span'><?php xl('Cancel','e');?></span>
</a>
</td></tr></table>
<br><br>

<table border=0>

<tr><td valign=top>
<form name='new_user' method='post'  target="_parent" action="usergroup_admin.php"
 onsubmit='return top.restoreSession()'>
<input type=hidden name=mode value=new_user>
<span class="bold">&nbsp;</span>
</td><td>
<table border=0 cellpadding=0 cellspacing=0 style="width:600px;">
<tr>
<td style="width:150px;"><span class="text"><?php xl('Username','e'); ?>: </span></td><td  style="width:220px;"><input type=entry name=rumple style="width:120px;"> <span class="mandatory">&nbsp;*</span></td>
<td style="width:150px;"><span class="text"><?php xl('Password','e'); ?>: </span></td><td style="width:250px;"><input type="entry" style="width:120px;" name=stiltskin><span class="mandatory">&nbsp;*</span></td>
</tr>
<tr>
<td><span class="text"<?php if ($GLOBALS['disable_non_default_groups']) echo " style='display:none'"; ?>><?php xl('Groupname','e'); ?>: </span></td>
<td>
<select name=groupname<?php if ($GLOBALS['disable_non_default_groups']) echo " style='display:none'"; ?>>
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
<td><span class="text"><?php xl('Provider','e'); ?>: </span></td><td>
 <input type='checkbox' name='authorized' value='1' onclick='authorized_clicked()' />
 &nbsp;&nbsp;<span class='text'><?php xl('Calendar','e'); ?>:
 <input type='checkbox' name='calendar' disabled />
</td>
</tr>
<tr>
<td><span class="text"><?php xl('First Name','e'); ?>: </span></td><td><input type=entry name='fname' style="width:120px;"></td>
<td><span class="text"><?php xl('Middle Name','e'); ?>: </span></td><td><input type=entry name='mname' style="width:120px;"></td>
</tr>
<tr>
<td><span class="text"><?php xl('Last Name','e'); ?>: </span></td><td><input type=entry name='lname' style="width:120px;"></td>
<td><span class="text"><?php xl('Default Facility','e'); ?>: </span></td><td><select style="width:120px;" name=facility_id>
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
<td><span class="text"><?php xl('Federal Tax ID','e'); ?>: </span></td><td><input type=entry name='federaltaxid' style="width:120px;"></td>
<td><span class="text"><?php xl('Federal Drug ID','e'); ?>: </span></td><td><input type=entry name='federaldrugid' style="width:120px;"></td>
</tr>
<tr>
<td><span class="text"><?php xl('UPIN','e'); ?>: </span></td><td><input type="entry" name="upin" style="width:120px;"></td>
<td class='text'><?php xl('See Authorizations','e'); ?>: </td>
<td><select name="see_auth" style="width:120px;">
<?php
 foreach (array(1 => xl('None'), 2 => xl('Only Mine'), 3 => xl('All')) as $key => $value)
 {
  echo " <option value='$key'";
  echo ">$value</option>\n";
 }
?>
</select></td>

<tr>
<td><span class="text"><?php xl('NPI','e'); ?>: </span></td><td><input type="entry" name="npi" style="width:120px;"></td>
<td><span class="text"><?php xl('Job Description','e'); ?>: </span></td><td><input type="entry" name="specialty" style="width:120px;"></td>
</tr>

<!-- (CHEMED) Calendar UI preference -->
<tr>
<td><span class="text"><?php xl('Taxonomy','e'); ?>: </span></td>
<td><input type="entry" name="taxonomy" style="width:120px;" value="207Q00000X"></td>
<td><span class="text"><?php xl('Calendar UI','e'); ?>: </span></td><td><select name="cal_ui" style="width:120px;">
<?php
 foreach (array(3 => xl('Outlook'), 1 => xl('Original'), 2 => xl('Fancy')) as $key => $value)
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
  <td><select name="access_group[]" multiple style="width:120px;">
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
  </select></td>
  <td><span class="text"><?php xl('Additional Info','e'); ?>: </span></td>
  <td><textarea name=info style="width:120px;" cols=27 rows=4 wrap=auto></textarea></td>

  </tr>
  <tr height="25"><td colspan="4">&nbsp;</td></tr>
<?php
 }
?>

</table>

<br>
<input type="hidden" name="newauthPass">
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
<input type="submit" value=<?php xl('Save','e'); ?>>
</form>
</td>

</tr>

<tr <?php if ($GLOBALS['disable_non_default_groups']) echo " style='display:none'"; ?>>

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
$(document).ready(function(){
    $("#cancel").click(function() {
		  parent.$.fn.fancybox.close();
	 });

});
</script>
<table>

</table>

</body>
</html>
