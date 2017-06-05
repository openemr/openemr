<?php
require_once("../globals.php");
require_once("../../library/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/erx_javascript.inc.php");
use OpenEMR\Core\Header;

$facilityService = new \services\FacilityService();
$alertmsg = '';
?>
<html>
<head>
    <title>Add New User</title>
    <?php Header::setupHeader('common'); ?>

<script src="checkpwd_validation.js" type="text/javascript"></script>

<!-- validation library -->
<!--//Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation-->
<?php    $use_validate_js = 1;?>
<?php  require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>
<?php
//Gets validation rules from Page Validation list.
//Note that for technical reasons, we are bypassing the standard validateUsingPageRules() call.
$collectthis = collectValidationPageRules("/interface/usergroup/usergroup_admin_add.php");
if (empty($collectthis)) {
    $collectthis = "undefined";
}
else {
    $collectthis = $collectthis["new_user"]["rules"];
}
?>
<script type="text/javascript">

/*
* validation on the form with new client side validation (using validate.js).
* this enable to add new rules for this form in the pageValidation list.
* */
var collectvalidation = <?php echo($collectthis); ?>;

function trimAll(sString)
{
	while (sString.substring(0,1) == ' ')
	{
		sString = sString.substring(1, sString.length);
	}
	while (sString.substring(sString.length-1, sString.length) == ' ')
	{
		sString = sString.substring(0,sString.length-1);
	}
	return sString;
}

function submitform() {

    var valid = submitme(1, undefined, 'new_user', collectvalidation);
    if (!valid) return;

   top.restoreSession();

   //Checking if secure password is enabled or disabled.
   //If it is enabled and entered password is a weak password, alert the user to enter strong password.
   if(document.new_user.secure_pwd.value == 1){
      var password = trim(document.new_user.stiltskin.value);
      if(password != "") {
         var pwdresult = passwordvalidate(password);
         if(pwdresult == 0){
            alert("<?php echo xl('The password must be at least eight characters, and should'); echo '\n'; echo xl('contain at least three of the four following items:'); echo '\n'; echo xl('A number'); echo '\n'; echo xl('A lowercase letter'); echo '\n'; echo xl('An uppercase letter'); echo '\n'; echo xl('A special character');echo '('; echo xl('not a letter or number'); echo ').'; echo '\n'; echo xl('For example:'); echo ' healthCare@09'; ?>");
            return false;
         }
      }
   } //secure_pwd if ends here

   <?php if($GLOBALS['erx_enable']){ ?>
   alertMsg='';
   f=document.forms[0];
   for(i=0;i<f.length;i++){
      if(f[i].type=='text' && f[i].value)
      {
         if(f[i].name == 'rumple')
         {
            alertMsg += checkLength(f[i].name,f[i].value,35);
            alertMsg += checkUsername(f[i].name,f[i].value);
         }
         else if(f[i].name == 'fname' || f[i].name == 'mname' || f[i].name == 'lname')
         {
            alertMsg += checkLength(f[i].name,f[i].value,35);
            alertMsg += checkUsername(f[i].name,f[i].value);
         }
         else if(f[i].name == 'federaltaxid')
         {
            alertMsg += checkLength(f[i].name,f[i].value,10);
            alertMsg += checkFederalEin(f[i].name,f[i].value);
         }
         else if(f[i].name == 'state_license_number')
         {
            alertMsg += checkLength(f[i].name,f[i].value,10);
            alertMsg += checkStateLicenseNumber(f[i].name,f[i].value);
         }
         else if(f[i].name == 'npi')
         {
            alertMsg += checkLength(f[i].name,f[i].value,35);
            alertMsg += checkTaxNpiDea(f[i].name,f[i].value);
         }
         else if(f[i].name == 'federaldrugid')
         {
            alertMsg += checkLength(f[i].name,f[i].value,30);
            alertMsg += checkAlphaNumeric(f[i].name,f[i].value);
         }
      }
   }
   if(alertMsg)
   {
      alert(alertMsg);
      return false;
   }
   <?php } // End erx_enable only include block?>

    document.forms[0].submit();

}
function authorized_clicked() {
     var f = document.forms[0];
     f.calendar.disabled = !f.authorized.checked;
     f.calendar.checked  =  f.authorized.checked;
}

</script>
</head>
<body>
<div class="container" style="padding-top: 35px;">
<div class="row">
    <div class="col-xs-12 col-md-10 col-md-offset-1">
    <form action="usergroup_admin.php" name="new_user" id="new_user" class="form-horizontal"
          method="post" target="_parent" onsubmit="return top.restoreSession()">
        <fieldset>
            <legend>Basic Information</legend>
            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group">
                        <label for="fname" class="col-xs-6 control-label"><?php xl('First Name', 'e');?></label>
                        <div class="col-xs-6">
                            <input type="entry" name='fname' id='fname' class="form-control" placeholder="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mname" class="col-xs-6 control-label"><?php xl('Middle Name', 'e');?></label>
                        <div class="col-xs-6">
                            <input type="entry" name='mname' id='mname' class="form-control" placeholder="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="lname" class="col-xs-6 control-label"><?php xl('Last Name', 'e');?></label>
                        <div class="col-xs-6">
                            <input type="entry" name='lname' id='lname' class="form-control" placeholder="">
                        </div>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="form-group">
                        <label for="rumple" class="control-label col-xs-6"><?php xl('Username', 'e'); ?></label>
                        <div class="col-xs-6">
                            <input type="text" name="rumple" class="form-control" id="rumple">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="siltskin" class="control-label col-xs-6"><?php xl('Password','e'); ?></label>
                        <div class="col-xs-6">
                            <input type="password" class="form-control" name="stiltskin" id="siltskin">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="adminPass" class="control-label col-xs-6"><?php xl("Admin Password", 'e'); ?></label>
                        <div class="col-xs-6">
                            <input type='password' class="form-control" name="adminPass" autocomplete='off' id="adminPass">
                            <span class="help-block"><em>Your</em> password</span>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Provider Information</legend>
            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group">
                        <div class="col-xs-3 col-xs-offset-3">
                            <div class="checkbox">
                                <label>
                                    <input type='checkbox' name='authorized' value='1' onclick='authorized_clicked()'>
                                    Is provider
                                </label>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="checkbox">
                                <label>
                                    <input type='checkbox' name='calendar' disabled>
                                    <?php xl('Calendar','e'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="physician_type"
                               class="col-xs-6 control-label"><?php xl("Provider Type", "e");?></label>
                        <div class="col-xs-6">
                            <?php echo generate_select_list("physician_type", "physician_type", '','',xl('Select Type'),'form-control','','',''); ?>
                        </div>
                    </div>
                    <?php if ($GLOBALS['inhouse_pharmacy']): ?>
                        <div class="form-group">
                            <label for="warehouse"
                                   class="control-label col-xs-6"><?php xl("Default Warehouse", "e");?></label>
                            <div class="col-xs-6">
                                <?php echo generate_select_list('default_warehouse', 'warehouse', '', '', '', 'form-control'); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="irnpool" class="control-label col-xs-6"><?php xl('Invoice Refno Pool','e'); ?>: </label>
                            <div class='col-xs-6'>
                                <?php
                                echo generate_select_list('irnpool', 'irnpool', '',
                                    xl('Invoice reference number pool, if used', '', '', 'form-control'));
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="taxonomy"
                               class="col-xs-6 control-label"><?php xl("Taxonomy", "e");?></label>
                        <div class="col-xs-6">
                            <input type="entry" name="taxonomy" id="taxonomy" class="form-control" value="207Q00000X">
                        </div>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="form-group">
                        <label for="state_license_number" class="col-xs-6 control-label"><?php xl("State license number", "e");?></label>
                        <div class="col-xs-6">
                            <input type="text" id="state_license_number" name="state_license_number" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="federaltaxid" class="control-label col-xs-6"><?php xl('Federal Tax ID','e'); ?></label>
                        <div class="col-xs-6">
                            <input type=entry id="federaltaxid" name='federaltaxid' class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="federdrugid" class="control-label col-xs-6"><?php xl('Federal Drug ID','e'); ?></label>
                        <div class="col-xs-6">
                            <input type=entry name='federaldrugid' id="federaldrugid" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="upin" class="control-label col-xs-6"><?php xl('UPIN','e'); ?></label>
                        <div class="col-xs-6">
                            <input type="entry" class="form-control" name="upin" id="upin">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="npi" class="control-label col-xs-6"><?php xl('NPI','e'); ?></label>
                        <div class="col-xs-6">
                            <input type="entry" name="npi" class="form-control">
                        </div>
                    </div>

                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>Role Information</legend>
            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group">
                        <label for="facility_id" class="control-label col-xs-6"><?php xl('Default Facility','e'); ?></label>
                        <div class="col-xs-6">
                            <select name="facility_id" id="facility_id" class="form-control">
                                <?php
                                $fres = $facilityService->getAllServiceLocations();
                                if ($fres) {
                                    for ($iter = 0; $iter < sizeof($fres);$iter++)
                                        $result[$iter] = $fres[$iter];
                                    foreach($result as $iter) {
                                        ?>
                                        <option value="<?php echo $iter{'id'};?>"><?php echo $iter{'name'};?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <?php if (!$GLOBALS['disable_non_default_groups']): ?>
                    <div class="form-group">
                        <label for="groupname" class="col-xs-6 control-label">
                            <?php xl("Groupname", "e");?>
                            <a href="#" data-toggle="modal" data-target="#modal-new-group" class="btn btn-default btn-sm"><i class="fa fa-plus"></i><span class="sr-only">Add New</span></a>
                        </label>
                        <div class="col-xs-6">
                            <select name="groupname" id="groupname" class="form-control">
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
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="authorization" class="col-xs-6 control-label"><?php xl('See Authorizations','e'); ?></label>
                        <div class="col-xs-6">
                            <select name="see_auth" id="authorization" class="form-control">
                                <?php
                                foreach (array(1 => xl('None'), 2 => xl('Only Mine'), 3 => xl('All')) as $key => $value)
                                {
                                    echo " <option value='$key'";
                                    echo ">$value</option>\n";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="specialty" class="col-xs-6 control-label"><?php xl('Job Description','e'); ?></label>
                        <div class="col-xs-6">
                            <input type="entry" id="specialty" name="specialty" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="form-group">
                        <label for="newcrop" class="col-xs-6 control-label"><?php xl("NewCrop eRX Role", "e");?></label>
                        <div class="col-xs-6">
                            <?php echo generate_select_list("erxrole", "newcrop_erx_role", '','','--Select Role--','form-control'); ?>
                        </div>
                    </div>
                    <?php if (isset($phpgacl_location) && acl_check('admin', 'acl')): ?>
                        <div class="form-group">
                            <label class='col-xs-6 control-label' for="access_group"><?php xl('Access Control','e'); ?>:</label>
                            <div class="col-xs-6">
                                <select name="access_group[]" class="form-control" multiple id="access_group">
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
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-6 control-label" for="info"><?php xl('Additional Info','e'); ?>: </label>
                            <div class="col-xs-6">
                                <textarea name=info class="form-control" id="info" rows=4 wrap=auto></textarea>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </fieldset>
        <div class="btn-group pull-right">
            <button type="submit" class="btn btn-default btn-save" name='form_save' id='form_save' onclick="return submitform()"><?php xl('Save','e');?></button>
            <a class="btn btn-link btn-cancel"  href='usergroup_admin.php'><?php xl('Cancel','e');?></a>
        </div>
    </form>
    </div>
</div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modal-new-group">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form name='new_group' method='post' action="usergroup_admin.php" onsubmit='return top.restoreSession()'>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">New Group</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label"><?php xl('Groupname','e'); ?></label>
                        <input type=entry name=groupname size=10 class="form-control">
                    </div>
                    <input type=hidden name=mode value=new_group>
                    <div class="form-group">

                    </div>
                    <label class="control-label"><?php xl('Initial User','e'); ?></label>
                    <select name=rumple class="form-control">
                        <?php
                        $res = sqlStatement("select distinct username from users where username != ''");
                        for ($iter = 0;$row = sqlFetchArray($res);$iter++)
                            $result[$iter] = $row;
                        foreach ($result as $iter) {
                            print "<option value='".$iter{"username"}."'>" . $iter{"username"} . "</option>\n";
                        }
                        ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-save">Save</button>
                    <button type="button" class="btn btn-link btn-cancel" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" tabindex="-1" role="dialog" id="modal-add-to-group">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form name='new_group' method='post' action="usergroup_admin.php" onsubmit='return top.restoreSession()'>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">New Group</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label"><?php xl('Groupname','e'); ?></label>
                        <input type=entry name=groupname size=10 class="form-control">
                    </div>
                    <input type=hidden name=mode value=new_group>
                    <div class="form-group">

                    </div>
                    <label class="control-label"><?php xl('Initial User','e'); ?></label>
                    <select name=rumple class="form-control">
                        <?php
                        $res = sqlStatement("select distinct username from users where username != ''");
                        for ($iter = 0;$row = sqlFetchArray($res);$iter++)
                            $result[$iter] = $row;
                        foreach ($result as $iter) {
                            print "<option value='".$iter{"username"}."'>" . $iter{"username"} . "</option>\n";
                        }
                        ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-save">Save</button>
                    <button type="button" class="btn btn-link btn-cancel" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<table border=0>
<tr><td valign=top>
<form name='new_user' id="new_user" method='post'  target="_parent" action="usergroup_admin.php"
 onsubmit='return top.restoreSession()'>
<input type='hidden' name='mode' value='new_user'>
<input type='hidden' name='secure_pwd' value="<?php echo $GLOBALS['secure_password']; ?>">
<table border=0 cellpadding=0 cellspacing=0 style="width:600px;">
<tr>

    <?php if(!$GLOBALS['use_active_directory']) { ?>
    <?php }else{ ?>
        <td> <input type="hidden" value="124" name="stiltskin" /></td>
    <?php } ?>
</tr>
</table>
<br>
<input type="hidden" name="newauthPass">
</form>
</td>
<td valign=top>

</td>

</tr>
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

<script type="text/javascript">
<?php
  if ($alertmsg = trim($alertmsg)) {
    echo "alert('$alertmsg');\n";
  }
?>
</script>
</body>
</html>
