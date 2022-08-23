<?php

/**
 * Add new user.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/calendar.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/erx_javascript.inc.php");

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Events\User\UserEditRenderEvent;
use OpenEMR\Menu\MainMenuRole;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\UserService;

$facilityService = new FacilityService();

if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Add User")]);
    exit;
}

$alertmsg = '';

?>
<html>
<head>

<?php Header::setupHeader(['common','opener']); ?>

<script src="checkpwd_validation.js"></script>

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
} else {
    $collectthis = json_sanitize($collectthis["new_user"]["rules"]);
}
?>
<script>

/*
* validation on the form with new client side validation (using validate.js).
* this enable to add new rules for this form in the pageValidation list.
* */
var collectvalidation = <?php echo $collectthis; ?>;

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
            if(pwdresult === 0){
                alert(
                    <?php echo xlj('The password must be at least eight characters, and should'); ?> +
                    '\n' +
                    <?php echo xlj('contain at least three of the four following items:'); ?> +
                    '\n' +
                    <?php echo xlj('A number'); ?> +
                    '\n' +
                    <?php echo xlj('A lowercase letter'); ?> +
                    '\n' +
                    <?php echo xlj('An uppercase letter'); ?> +
                    '\n' +
                    <?php echo xlj('A special character'); ?> +
                    '\n' +
                    '(' +
                    <?php echo xlj('not a letter or number'); ?> +
                    ').' +
                    '\n' +
                    <?php echo xlj('For example:'); ?> +
                    ' healthCare@09'
                );
                return false;
            }
        }
    } //secure_pwd if ends here

    <?php if ($GLOBALS['erx_enable']) { ?>
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

    let post_url = $("#new_user").attr("action");
    let request_method = $("#new_user").attr("method");
    let form_data = $("#new_user").serialize();

    $.ajax({
        url: post_url,
        type: request_method,
        data: form_data
    }).done(function (r) {
        if (r) {
            alert(r);
        } else {
            dlgclose('reload', false);
        }
    });

    return false;
}
function authorized_clicked() {
     var f = document.forms[0];
     f.calendar.disabled = !f.authorized.checked;
     f.calendar.checked  =  f.authorized.checked;
}

</script>
<style>
  .physician_type_class {
    width: 120px !important;
  }
  #main_menu_role {
    width: 120px !important;
  }
</style>
</head>
<body class="body_top">

<div class="container">

<table><tr><td>
<span class="title"><?php echo xlt('Add User'); ?></span>&nbsp;</td>
<td>
<a class="btn btn-secondary btn-save" name='form_save' id='form_save' href='#' onclick="return submitform()">
    <span><?php echo xlt('Save'); ?></span></a>
<a class="btn btn-link btn-cancel" id='cancel' href='#'>
    <span><?php echo xlt('Cancel');?></span>
</a>
</td></tr></table>
<br /><br />

<table border='0'>

<tr>
<td valign='top'>
<form name='new_user' id="new_user" method='post' action="usergroup_admin.php">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<input type='hidden' name='mode' value='new_user'>
<input type='hidden' name='secure_pwd' value="<?php echo attr($GLOBALS['secure_password']); ?>">

<span class="font-weight-bold">&nbsp;</span>
<table class="border-0" cellpadding='0' cellspacing='0' style="width:600px;">
<tr>
    <td colspan="4">
        <?php
        // TODO: we eventually want to move to a responsive layout and not use tables here.  So we are going to give
        // module writers the ability to inject divs, tables, or whatever inside the cell instead of having them
        // generate additional rows / table columns which locks us into that format.
        $preRenderEvent = new UserEditRenderEvent('usergroup_admin_add');
        $GLOBALS['kernel']->getEventDispatcher()->dispatch($preRenderEvent, UserEditRenderEvent::EVENT_USER_EDIT_RENDER_BEFORE);
        ?>
    </td>
</tr>
<tr>
<td style="width:150px;"><span class="text"><?php echo xlt('Username'); ?>: </span></td><td style="width:220px;"><input type="text" name="rumple" style="width:120px;" class="form-control"><span class="mandatory"></span></td>
<?php if (empty($GLOBALS['gbl_ldap_enabled']) || empty($GLOBALS['gbl_ldap_exclusions'])) { ?>
<td style="width:150px;"><span class="text"><?php echo xlt('Password'); ?>: </span></td><td style="width:250px;"><input type="password" style="width:120px;" name="stiltskin" class="form-control"><span class="mandatory"></span></td>
<?php } else { ?>
        <td><input type="hidden" value="124" name="stiltskin" /></td>
<?php } ?>
</tr>
<tr>
    <td style="width:150px;"></td><td style="width:220px;"></td>
    <td style="width:200px;"><span class='text'><?php echo xlt('Your Password'); ?>: </span></td>
    <td class='text' style="width:280px;"><input type='password' name=adminPass style="width:120px;"  value="" autocomplete='off' class="form-control"><font class="mandatory"></font></td>

</tr>
<tr>
<td><span class="text"<?php echo ($GLOBALS['disable_non_default_groups']) ? " style='display: none'" : ""; ?>><?php echo xlt('Groupname'); ?>: </span></td>
<td>
<select name="groupname" class="form-control"<?php echo ($GLOBALS['disable_non_default_groups']) ? " style='display:none'" : ""; ?>>
<?php
$res = sqlStatement("select distinct name from `groups`");
$result2 = array();
for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
    $result2[$iter] = $row;
}

foreach ($result2 as $iter) {
    print "<option value='" . attr($iter["name"]) . "'>" . text($iter["name"]) . "</option>\n";
}
?>
</select></td>
<td colspan="2"><span class="text"><?php echo xlt('Provider'); ?>: </span>
<input type='checkbox' name='authorized' value='1' onclick='authorized_clicked()' />
<span class='text'><?php echo xlt('Calendar'); ?>:
<input type='checkbox' name='calendar' disabled /></span>
<span class=text><?php echo xlt('Portal'); ?>:
<input type="checkbox" name="portal_user" /></span>
</td>
</tr>
<tr>
<td><span class="text"><?php echo xlt('First Name'); ?>: </span></td><td><input type="text" name='fname' id='fname' style="width:120px;" class="form-control"><span class="mandatory"></span></td>
<td><span class="text"><?php echo xlt('Middle Name'); ?>: </span></td><td><input type="text" name='mname' style="width:120px;" class="form-control"></td>
</tr>
<tr>
<td><span class="text"><?php echo xlt('Last Name'); ?>: </span></td><td><input type="text" name='lname' id='lname' style="width:120px;" class="form-control"><span class="mandatory"></span></td>
<td><span class="text"><?php echo xlt('Default Facility'); ?>: </span></td>
<td>
<select style="width:120px;" name=facility_id class="form-control">
<?php
$fres = $facilityService->getAllServiceLocations();
if ($fres) {
    for ($iter = 0; $iter < sizeof($fres); $iter++) {
        $result[$iter] = $fres[$iter];
    }

    foreach ($result as $iter) {
        ?>
    <option value="<?php echo attr($iter['id']); ?>"><?php echo text($iter['name']); ?></option>
        <?php
    }
}
?>
</select></td>
</tr>
<tr>
<td><span class="text"><?php echo xlt('Federal Tax ID'); ?>: </span></td><td><input type="text" name='federaltaxid' style="width:120px;" class="form-control"></td>
<td><span class="text"><?php echo xlt('DEA number'); ?>: </span></td><td><input type="text" name='federaldrugid' style="width:120px;" class="form-control"></td>
</tr>
<tr>
<td><span class="text"><?php echo xlt('UPIN'); ?>: </span></td><td><input type="text" name="upin" style="width:120px;" class="form-control"></td>
<td class='text'><?php echo xlt('See Authorizations'); ?>: </td>
<td><select name="see_auth" style="width:120px;" class="form-control">
<?php
foreach (array(1 => xl('None{{Authorization}}'), 2 => xl('Only Mine'), 3 => xl('All')) as $key => $value) {
    echo " <option value='" . attr($key) . "'";
    echo ">" . text($value) . "</option>\n";
}
?>
</select></td>

<tr>
<td><span class="text"><?php echo xlt('NPI'); ?>: </span></td><td><input type="text" name="npi" style="width:120px;" class="form-control"></td>
<td><span class="text"><?php echo xlt('Job Description'); ?>: </span></td><td><input type="text" name="specialty" style="width:120px;" class="form-control"></td>
</tr>

<tr>
    <td>
        <span class="text"><?php echo xlt('Provider Type'); ?>: </span>
    </td>
    <td>
        <?php echo generate_select_list("physician_type", "physician_type", '', '', xl('Select Type'), 'physician_type_class', '', '', ''); ?>
    </td>
</tr>
<tr>
  <td>
    <span class="text"><?php echo xlt('Main Menu Role'); ?>: </span>
  </td>
  <td>
    <?php
    $menuMain = new MainMenuRole($GLOBALS['kernel']->getEventDispatcher());
    echo $menuMain->displayMenuRoleSelector();
    ?>
  </td>
  <td>
    <span class="text"><?php echo xlt('Patient Menu Role'); ?>: </span>
  </td>
  <td>
    <?php
    $menuPatient = new PatientMenuRole();
    echo $menuPatient->displayMenuRoleSelector();
    ?>
  </td>
</tr>

<tr>
<td><span class="text"><?php echo xlt('Taxonomy'); ?>: </span></td>
<td><input type="text" name="taxonomy" style="width:120px;" class="form-control" value="207Q00000X"></td>
    <td><span class="text"><?php echo xlt('Supervisor'); ?>: </span></td>
    <td>
        <select name="supervisor_id" style="width:150px;" class="form-control">
            <option value=""><?php echo xlt("Select Supervisor") ?></option>
            <?php
            $userService = new UserService();
            $users = $userService->getActiveUsers();
            foreach ($users as $activeUser) {
                $p_id = (int)$activeUser['id'];
                if ($activeUser['authorized'] != 1) {
                    continue;
                }
                echo "<option value='" . attr($p_id) . "'";
                if ((int)($iter["supervisor_id"] ?? null) === $p_id) {
                    echo "selected";
                }
                echo ">" . text($activeUser['lname']) . ' ' .
                    text($activeUser['fname']) . ' ' . text($activeUser['mname']) . "</option>\n";
            }
            ?>
        </select>
    </td>
<tr>
<td><span class="text"><?php echo xlt('State License Number'); ?>: </span></td>
<td><input type="text" name="state_license_number" style="width:120px;" class="form-control"></td>
<td class='text'><?php echo xlt('NewCrop eRX Role'); ?>:</td>
<td>
    <?php echo generate_select_list("erxrole", "newcrop_erx_role", '', '', '--Select Role--', '', '', '', array('style' => 'width:120px')); ?>
</td>
</tr>
<tr>
<td><span class="text"><?php echo xlt('Weno Provider ID'); ?>: </span></td><td><input type="text" name="erxprid" style="width:120px;" class="form-control" value="<?php echo attr($iter["weno_prov_id"] ?? ''); ?>"></td>
<td><span class="text"><?php echo xlt('Google Email for Login'); ?>: </span></td><td><input type="text" name="google_signin_email" style="width:150px;" class="form-control" value="<?php echo attr($iter["google_signin_email"] ?? ''); ?>"></td>
</tr>
<?php if ($GLOBALS['inhouse_pharmacy']) { ?>
<tr>
 <td class="text"><?php echo xlt('Default Warehouse'); ?>: </td>
 <td class='text'>
    <?php
    echo generate_select_list(
        'default_warehouse',
        'warehouse',
        '',
        ''
    );
    ?>
 </td>
 <td class="text"><?php echo xlt('Invoice Refno Pool'); ?>: </td>
 <td class='text'>
    <?php
    echo generate_select_list(
        'irnpool',
        'irnpool',
        '',
        xl('Invoice reference number pool, if used')
    );
    ?>
 </td>
</tr>
<?php } ?>

<!-- facility and warehouse restrictions, optional -->
<?php if (!empty($GLOBALS['gbl_fac_warehouse_restrictions']) || !empty($GLOBALS['restrict_user_facility'])) { ?>
 <tr title="<?php echo xla('If nothing is selected here then all are permitted.'); ?>">
  <td class="text"><?php echo !empty($GLOBALS['gbl_fac_warehouse_restrictions']) ?
    xlt('Facility and warehouse permissions') : xlt('Facility permissions'); ?>:</td>
  <td colspan="3">
   <select name="schedule_facility[]" multiple style="width:490px;">
    <?php
    $user_id = 0; // in user_admin.php this is intval($_GET["id"]).
    $userFacilities = getUserFacilities($user_id, 'id', $GLOBALS['gbl_fac_warehouse_restrictions']);
    $ufid = array();
    foreach ($userFacilities as $uf) {
        $ufid[] = $uf['id'];
    }
    $fres = sqlStatement("select * from facility order by name");
    if ($fres) {
        while ($frow = sqlFetchArray($fres)) {
            // Get the warehouses that are linked to this user and facility.
            $whids = getUserFacWH($user_id, $frow['id']); // from calendar.inc
            // Generate an option for just the facility with no warehouse restriction.
            echo "    <option";
            if (empty($whids) && in_array($frow['id'], $ufid)) {
                echo ' selected';
            }
            echo " value='" . attr($frow['id']) . "'>" . text($frow['name']) . "</option>\n";
            // Then generate an option for each of the facility's warehouses.
            // Does not apply if the site does not use warehouse restrictions.
            if (!empty($GLOBALS['gbl_fac_warehouse_restrictions'])) {
                $lres = sqlStatement(
                    "SELECT option_id, title FROM list_options WHERE " .
                    "list_id = ? AND option_value = ? ORDER BY seq, title",
                    array('warehouse', $frow['id'])
                );
                while ($lrow = sqlFetchArray($lres)) {
                    echo "    <option";
                    if (in_array($lrow['option_id'], $whids)) {
                        echo ' selected';
                    }
                    echo " value='" . attr($frow['id']) . "/" . attr($lrow['option_id']) . "'>&nbsp;&nbsp;&nbsp;" .
                        text(xl_list_label($lrow['title'])) . "</option>\n";
                }
            }
        }
    }
    ?>
   </select>
  </td>
 </tr>
<?php } ?>

 <tr>
<td class='text'><?php echo xlt('Access Control'); ?>:</td>
 <td><select name="access_group[]" multiple style="width:120px;" class="form-control">
<?php
// List the access control groups
$is_super_user = AclMain::aclCheckCore('admin', 'super');
$list_acl_groups = AclExtended::aclGetGroupTitleList($is_super_user ? true : false);
$default_acl_group = 'Administrators';
foreach ($list_acl_groups as $value) {
    if ($is_super_user && $default_acl_group == $value) {
        // Modified 6-2009 by BM - Translate group name if applicable
        echo " <option value='" . attr($value) . "' selected>" . text(xl_gacl_group($value)) . "</option>\n";
    } else {
        // Modified 6-2009 by BM - Translate group name if applicable
        echo " <option value='" . attr($value) . "'>" . text(xl_gacl_group($value)) . "</option>\n";
    }
}
?>
  </select></td>
  <td><span class="text"><?php echo xlt('Additional Info'); ?>: </span></td>
  <td><textarea name=info style="width:120px;" cols='27' rows='4' wrap='auto' class="form-control"></textarea></td>

  </tr>
    <tr>
        <td><span class=text><?php echo xlt('Default Billing Facility'); ?>: </span></td>
        <td><select name="billing_facility_id" style="width:150px;" class="form-control">
                <?php
                $fres = $facilityService->getAllBillingLocations();
                if ($fres) {
                    $billResults = [];
                    for ($iter2 = 0; $iter2 < sizeof($fres); $iter2++) {
                        $billResults[$iter2] = $fres[$iter2];
                    }

                    foreach ($billResults as $iter2) {
                        ?>
                        <option value="<?php echo attr($iter2['id']); ?>"><?php echo text($iter2['name']); ?></option>
                        <?php
                    }
                }
                ?>
            </select>
        </td>
        <td></td>
    </tr>
    <tr>
        <td colspan="4">
            <?php
            // TODO: we eventually want to move to a responsive layout and not use tables here.  So we are going to give
            // module writers the ability to inject divs, tables, or whatever inside the cell instead of having them
            // generate additional rows / table columns which locks us into that format.
            $preRenderEvent = new UserEditRenderEvent('usergroup_admin_add.php');
            $GLOBALS['kernel']->getEventDispatcher()->dispatch($preRenderEvent, UserEditRenderEvent::EVENT_USER_EDIT_RENDER_AFTER);
            ?>
        </td>
    </tr>
  <tr height="25"><td colspan="4">&nbsp;</td></tr>

</table>

<br />
<input type="hidden" name="newauthPass">
</form>
</td>

</tr>

<tr<?php echo ($GLOBALS['disable_non_default_groups']) ? " style='display:none'" : ""; ?>>

<td valign='top'>
<form name='new_group' method='post' action="usergroup_admin.php"
 onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<br />
<input type='hidden' name='mode' value='new_group' />
<span class="bold"><?php echo xlt('New Group'); ?>:</span>
</td>
<td>
<span class="text"><?php echo xlt('Groupname'); ?>: </span><input type="text" name='groupname' size='10'>
&nbsp;&nbsp;&nbsp;
<span class="text"><?php echo xlt('Initial User'); ?>: </span>
<select name='rumple'>
<?php
$res = sqlStatement("select distinct username from users where username != ''");
for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
    $result[$iter] = $row;
}

foreach ($result as $iter) {
    print "<option value='" . attr($iter["username"]) . "'>" . text($iter["username"]) . "</option>\n";
}
?>
</select>
&nbsp;&nbsp;&nbsp;
<input type="submit" value="<?php echo xla('Save'); ?>" />
</form>
</td>

</tr>
<tr<?php echo ($GLOBALS['disable_non_default_groups']) ? " style='display:none'" : ""; ?>>

<td valign='top'>
<form name='new_group' method='post' action="usergroup_admin.php"
 onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<input type='hidden' name='mode' value='new_group' />
<span class="bold"><?php echo xlt('Add User To Group'); ?>:</span>
</td>
<td>
<span class="text">
<?php echo xlt('User'); ?>
: </span>
<select name='rumple'>
<?php
$res = sqlStatement("select distinct username from users where username != ''");
for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
    $result3[$iter] = $row;
}

foreach ($result3 as $iter) {
    print "<option value='" . attr($iter["username"]) . "'>" . text($iter["username"]) . "</option>\n";
}
?>
</select>
&nbsp;&nbsp;&nbsp;
<span class="text"><?php echo xlt('Groupname'); ?>: </span>
<select name='groupname'>
<?php
$res = sqlStatement("select distinct name from `groups`");
$result2 = array();
for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
    $result2[$iter] = $row;
}

foreach ($result2 as $iter) {
    print "<option value='" . attr($iter["name"]) . "'>" . text($iter["name"]) . "</option>\n";
}
?>
</select>
&nbsp;&nbsp;&nbsp;
<input type="submit" value="<?php echo xla('Add User To Group'); ?>" />
</form>
</td>
</tr>

</table>

<?php
if (empty($GLOBALS['disable_non_default_groups'])) {
    $res = sqlStatement("select * from `groups` order by name");
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $result5[$iter] = $row;
    }

    foreach ($result5 as $iter) {
        $grouplist[$iter["name"]] .= $iter["user"] .
        "(<a class='link_submit' href='usergroup_admin.php?mode=delete_group&id=" .
        attr_url($iter["id"]) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='top.restoreSession()'>" . xlt("Remove") . "</a>), ";
    }

    foreach ($grouplist as $groupname => $list) {
        print "<span class='font-weight-bold'>" . text($groupname) . "</span><br />\n<span class='text'>" .
        text(substr($list, 0, strlen($list) - 2)) . "</span><br />\n";
    }
}
?>

<script>
<?php
if ($alertmsg = trim($alertmsg)) {
    echo "alert('" . js_escape($alertmsg) . "');\n";
}
?>
$(function () {
    $("#cancel").click(function() {
          dlgclose();
     });

});
</script>
<table>

</table>

</div>

</body>
</html>
