<?php

/**
 * Edit user.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Daniel Pflieger <daniel@mi-squared.com> <daniel@growlingflea.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Daniel Pflieger <daniel@mi-squared.com> <daniel@growlingflea.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
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
use OpenEMR\Menu\MainMenuRole;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\UserService;

if (!empty($_GET)) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$facilityService = new FacilityService();

if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Edit User")]);
    exit;
}

if (!$_GET["id"]) {
    exit();
}

$res = sqlStatement("select * from users where id=?", array($_GET["id"]));
for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                $result[$iter] = $row;
}

$iter = $result[0];
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
$collectthis = collectValidationPageRules("/interface/usergroup/user_admin.php");
if (empty($collectthis)) {
    $collectthis = "undefined";
} else {
    $collectthis = json_sanitize($collectthis["user_form"]["rules"]);
}
?>

<script>

/*
 * validation on the form with new client side validation (using validate.js).
 * this enable to add new rules for this form in the pageValidation list.
 * */
var collectvalidation = <?php echo $collectthis; ?>;

    function checkChange()
{
  alert(<?php echo xlj('If you change e-RX Role for ePrescription, it may affect the ePrescription workflow. If you face any difficulty, contact your ePrescription vendor.'); ?>);
}
function submitform() {

    var valid = submitme(1, undefined, 'user_form', collectvalidation);
    if (!valid) return;

    top.restoreSession();
    var flag=0;
<?php if (empty($GLOBALS['gbl_ldap_enabled']) || empty($GLOBALS['gbl_ldap_exclusions'])) { ?>
    if(document.forms[0].clearPass.value!="")
    {
        //Checking for the strong password if the 'secure password' feature is enabled
        if(document.forms[0].secure_pwd.value === 1)
        {
                    var pwdresult = passwordvalidate(document.forms[0].clearPass.value);
                    if(pwdresult == 0) {
                            flag=1;
                            alert(<?php echo xlj('The password must be at least eight characters, and should'); ?> +
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
                            ' healthCare@09');
                            return false;
                    }
        }

    }//If pwd null ends here
<?php } ?>
  if (document.forms[0].access_group_id) {
    var sel = getSelected(document.forms[0].access_group_id.options);
    for (var item in sel) {
      if (sel[item].value == "Emergency Login") {
        document.forms[0].check_acl.value = 1;
      }
    }
  }

        <?php if ($GLOBALS['erx_enable']) { ?>
    alertMsg='';
    f=document.forms[0];
    for(i=0;i<f.length;i++){
      if(f[i].type=='text' && f[i].value)
      {
        if(f[i].name == 'fname' || f[i].name == 'mname' || f[i].name == 'lname')
        {
          alertMsg += checkLength(f[i].name,f[i].value,35);
          alertMsg += checkUsername(f[i].name,f[i].value);
        }
        else if(f[i].name == 'taxid')
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
          alertMsg += checkLength(f[i].name,f[i].value,10);
          alertMsg += checkTaxNpiDea(f[i].name,f[i].value);
        }
        else if(f[i].name == 'drugid')
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
    <?php } ?>

    if (flag === 0) {
        let post_url = $("#user_form").attr("action");
        let request_method = $("#user_form").attr("method");
        let form_data = $("#user_form").serialize();
        // submit form
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
}
//Getting the list of selected item in ACL
function getSelected(opt) {
         var selected = new Array();
            var index = 0;
            for (var intLoop = 0; intLoop < opt.length; intLoop++) {
               if ((opt[intLoop].selected) ||
                   (opt[intLoop].checked)) {
                  index = selected.length;
                  selected[index] = new Object;
                  selected[index].value = opt[intLoop].value;
                  selected[index].index = intLoop;
               }
            }
            return selected;
         }

function authorized_clicked() {
 var f = document.forms[0];
 f.calendar.disabled = !f.authorized.checked;
 f.calendar.checked  =  f.authorized.checked;
}

function toggle_password() {
  var x = document.getElementById("clearPass");
  if (x.type === "password") {
    x.type = "text";
  } else {
    x.type = "password";
  }
}
</script>
<style>
  .physician_type_class{
    width: 150px !important;
  }
  #main_menu_role {
    width: 120px !important;
  }
</style>
</head>
<body class="body_top">

<div class="container">
    <?php
    /*  Get the list ACL for the user */
    $is_super_user = AclMain::aclCheckCore('admin', 'super');
    $acl_name = AclExtended::aclGetGroupTitles($iter["username"]);
    $bg_name = '';
    if (is_countable($acl_name)) {
        $bg_count = count($acl_name);
        $selected_user_is_superuser = false;
        for ($i = 0; $i < $bg_count; $i++) {
            if ($acl_name[$i] == "Emergency Login") {
                $bg_name = $acl_name[$i];
            }
            //check if user member on group with superuser rule
            if (AclExtended::isGroupIncludeSuperuser($acl_name[$i])) {
                $selected_user_is_superuser = true;
            }
        }
    }
    $disabled_save = !$is_super_user && $selected_user_is_superuser ? 'disabled' : '';
    ?>
<table><tr><td>
<span class="title"><?php echo xlt('Edit User'); ?></span>&nbsp;
</td><td>
    <a class="btn btn-secondary btn-save" name='form_save' id='form_save' href='#' onclick='return submitform()' <?php echo $disabled_save; ?>> <span><?php echo xlt('Save');?></span> </a>
    <a class="btn btn-link btn-cancel" id='cancel' href='#'><span><?php echo xlt('Cancel');?></span></a>
</td></tr>
</table>
<br />
<FORM NAME="user_form" id="user_form" METHOD="POST" ACTION="usergroup_admin.php">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<input type=hidden name="pre_active" value="<?php echo attr($iter["active"]); ?>" >
<input type=hidden name="get_admin_id" value="<?php echo attr($GLOBALS['Emergency_Login_email']); ?>" >
<input type=hidden name="admin_id" value="<?php echo attr($GLOBALS['Emergency_Login_email_id']); ?>" >
<input type=hidden name="check_acl" value="">
<input type=hidden name="user_type" value="<?php echo attr($bg_name); ?>" >

<TABLE border=0 cellpadding=0 cellspacing=0>
<TR>
    <TD style="width:180px;"><span class=text><?php echo xlt('Username'); ?>: </span></TD>
    <TD style="width:270px;"><input type="text" name=username style="width:150px;" class="form-control" value="<?php echo attr($iter["username"]); ?>" disabled></td>
<?php if (empty($GLOBALS['gbl_ldap_enabled']) || empty($GLOBALS['gbl_ldap_exclusions'])) { ?>
        <TD style="width:200px;"><span class=text>*<?php echo xlt('Your Password'); ?>*: </span></TD>
        <TD class='text' style="width:280px;"><input type='password' name=adminPass style="width:150px;"  class="form-control" value="" autocomplete='off'><font class="mandatory"></font></TD>
<?php } ?>
</TR>
<?php if (empty($GLOBALS['gbl_ldap_enabled']) || empty($GLOBALS['gbl_ldap_exclusions'])) { ?>
<TR>
    <TD style="width:180px;"><span class=text></span></TD>
    <TD style="width:270px;"></td>
    <TD style="width:200px;"><span class=text><?php echo xlt('User\'s New Password'); ?>: </span></TD>
    <TD class='text' style="width:280px;">
        <input type='password' id=clearPass name=clearPass style="width:150px;"  class="form-control" value="">
        <input type="checkbox" id="togglePass" name="togglePass" onclick="toggle_password()" style="margin: .5rem 0 1rem;">
        <label for="togglePass"><?php echo xlt('Show Password'); ?></label>
        <font class="mandatory"></font>
    </td>
</TR>
<?php } ?>

<TR height="30" style="valign:middle;">
<td class='text'>
<?php echo xlt('Clear 2FA'); ?>:
</td>
<td title='<?php echo xla('Remove multi-factor authentications for this person.'); ?>'>
<input type="checkbox" name="clear_2fa" value='1' />
</td>
<td colspan="2"><span class=text><?php echo xlt('Provider'); ?>:
<input type="checkbox" name="authorized" onclick="authorized_clicked()"<?php
if ($iter["authorized"]) {
    echo " checked";
} ?> /></span>
<span class='text'><?php echo xlt('Calendar'); ?>:
<input type="checkbox" name="calendar"<?php
if ($iter["calendar"]) {
    echo " checked";
}
if (!$iter["authorized"]) {
    echo " disabled";
} ?> /></span>
<span class=text><?php echo xlt('Portal'); ?>:
<input type="checkbox" name="portal_user" <?php
if ($iter["portal_user"]) {
    echo " checked";
} ?> /></span>
<span class='text'><?php echo xlt('Active'); ?>:
    <input type="checkbox" name="active"<?php echo ($iter["active"]) ? " checked" : ""; ?>/></span>
</TD>
</TR>

<TR>
<TD><span class=text><?php echo xlt('First Name'); ?>: </span></TD>
<TD><input type="text" name=fname id=fname style="width:150px;" class="form-control" value="<?php echo attr($iter["fname"]); ?>"><span class="mandatory"></span></td>
<td><span class=text><?php echo xlt('Middle Name'); ?>: </span></TD><td><input type="text" name=mname style="width:150px;"  value="<?php echo attr($iter["mname"]); ?>"></td>
</TR>

<TR>
<td><span class=text><?php echo xlt('Last Name'); ?>: </span></td><td><input type="text" name=lname id=lname style="width:150px;"  class="form-control" value="<?php echo attr($iter["lname"]); ?>"><span class="mandatory"></span></td>
<td><span class=text><?php echo xlt('Default Facility'); ?>: </span></td><td><select name=facility_id style="width:150px;" class="form-control">
<?php
$fres = $facilityService->getAllServiceLocations();
if ($fres) {
    for ($iter2 = 0; $iter2 < sizeof($fres); $iter2++) {
                $result[$iter2] = $fres[$iter2];
    }

    foreach ($result as $iter2) {
        ?>
          <option value="<?php echo attr($iter2['id']); ?>" <?php if ($iter['facility_id'] == $iter2['id']) {
                echo "selected";
                         } ?>><?php echo text($iter2['name']); ?></option>
        <?php
    }
}
?>
</select></td>
</tr>

<?php if ($GLOBALS['restrict_user_facility']) { ?>
<tr>
 <td colspan=2>&nbsp;</td>
 <td><span class=text><?php echo xlt('Schedule Facilities:');?></td>
 <td>
  <select name="schedule_facility[]" multiple style="width:150px;" class="form-control">
    <?php
    $userFacilities = getUserFacilities($_GET['id']);
    $ufid = array();
    foreach ($userFacilities as $uf) {
        $ufid[] = $uf['id'];
    }

    $fres = $facilityService->getAllServiceLocations();
    if ($fres) {
        foreach ($fres as $frow) :
            ?>
   <option <?php echo in_array($frow['id'], $ufid) || $frow['id'] == $iter['facility_id'] ? "selected" : null ?>
           class="form-control" value="<?php echo attr($frow['id']); ?>"><?php echo text($frow['name']) ?></option>
            <?php
        endforeach;
    }
    ?>
  </select>
 </td>
</tr>
<?php } ?>

<TR>
<TD><span class=text><?php echo xlt('Federal Tax ID'); ?>: </span></TD><TD><input type=text name=taxid style="width:150px;"  class="form-control" value="<?php echo attr($iter["federaltaxid"]); ?>"></td>
<TD><span class=text><?php echo xlt('DEA Number'); ?>: </span></TD><TD><input type=text name=drugid style="width:150px;"  class="form-control" value="<?php echo attr($iter["federaldrugid"]); ?>"></td>
</TR>

<tr>
<td><span class="text"><?php echo xlt('UPIN'); ?>: </span></td><td><input type="text" name="upin" style="width:150px;" class="form-control" value="<?php echo attr($iter["upin"]); ?>"></td>
<td class='text'><?php echo xlt('See Authorizations'); ?>: </td>
<td><select name="see_auth" style="width:150px;" class="form-control" >
<?php
foreach (array(1 => xl('None{{Authorization}}'), 2 => xl('Only Mine'), 3 => xl('All')) as $key => $value) {
    echo " <option value='" . attr($key) . "'";
    if ($key == $iter['see_auth']) {
        echo " selected";
    }

    echo ">" . text($value) . "</option>\n";
}
?>
</select></td>
</tr>

<tr>
<td><span class="text"><?php echo xlt('NPI'); ?>: </span></td><td><input type="text" name="npi" style="width:150px;" class="form-control" value="<?php echo attr($iter["npi"]); ?>"></td>
<td><span class="text"><?php echo xlt('Job Description'); ?>: </span></td><td><input type="text" name="job" style="width:150px;" class="form-control" value="<?php echo attr($iter["specialty"]); ?>"></td>
</tr>

<tr>
<td><span class="text"><?php echo xlt('Taxonomy'); ?>: </span></td>
<td><input type="text" name="taxonomy" style="width:150px;" class="form-control" value="<?php echo attr($iter["taxonomy"]); ?>"></td>
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
            if ((int)$iter["supervisor_id"] === $p_id) {
                echo " selected";
            }
            echo ">" . text($activeUser['lname']) . ' ' .
                text($activeUser['fname']) . ' ' . text($activeUser['mname']) . "</option>\n";
        }
        ?>
    </select>
</td>
</tr>

<tr>
<td><span class="text"><?php echo xlt('State License Number'); ?>: </span></td>
<td><input type="text" name="state_license_number" style="width:150px;" class="form-control" value="<?php echo attr($iter["state_license_number"]); ?>"></td>
<td class='text'><?php echo xlt('NewCrop eRX Role'); ?>:</td>
<td>
    <?php echo generate_select_list("erxrole", "newcrop_erx_role", $iter['newcrop_user_role'], '', xl('Select Role'), '', '', '', array('style' => 'width:150px')); ?>
</td>
</tr>
<tr>
<td><span class="text"><?php echo xlt('Weno Provider ID'); ?>: </span></td><td><input type="text" name="erxprid" style="width:150px;" class="form-control" value="<?php echo attr($iter["weno_prov_id"]); ?>"></td>
<td><span class="text"><?php echo xlt('Google Email for Login'); ?>: </span></td><td><input type="text" name="google_signin_email" style="width:150px;" class="form-control" value="<?php echo attr($iter["google_signin_email"]); ?>"></td>
</tr>

<tr>
  <td><span class="text"><?php echo xlt('Provider Type'); ?>: </span></td>
  <td><?php echo generate_select_list("physician_type", "physician_type", $iter['physician_type'], '', xl('Select Type'), 'physician_type_class', '', '', ''); ?></td>
</tr>
<tr>
  <td>
    <span class="text"><?php echo xlt('Main Menu Role'); ?>: </span>
  </td>
  <td>
    <?php
    $menuMain = new MainMenuRole($GLOBALS['kernel']->getEventDispatcher());
    echo $menuMain->displayMenuRoleSelector($iter["main_menu_role"]);
    ?>
  </td>
  <td>
    <span class="text"><?php echo xlt('Patient Menu Role'); ?>: </span>
  </td>
  <td>
    <?php
    $menuPatient = new PatientMenuRole();
    echo $menuPatient->displayMenuRoleSelector($iter["patient_menu_role"]);
    ?>
  </td>

</tr>
<?php if (!empty($GLOBALS['inhouse_pharmacy'])) { ?>
<tr>
 <td class="text"><?php echo xlt('Default Warehouse'); ?>: </td>
 <td class='text'>
    <?php
    echo generate_select_list(
        'default_warehouse',
        'warehouse',
        $iter['default_warehouse'],
        ''
    );
    ?>
 </td>

    <?php if (!empty($GLOBALS['inhouse_pharmacy'])) { ?>
 <td class="text"><?php echo xlt('Invoice Refno Pool'); ?>: </td>
 <td class='text'>
        <?php
        echo generate_select_list(
            'irnpool',
            'irnpool',
            $iter['irnpool'],
            xl('Invoice reference number pool, if used')
        );
        ?>
 </td>
    <?php } else { ?>
  <td class="text" colspan="2">&nbsp;</td>
    <?php } ?>

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
    $userFacilities = getUserFacilities($_GET['id'], 'id', $GLOBALS['gbl_fac_warehouse_restrictions']);
    $ufid = array();
    foreach ($userFacilities as $uf) {
        $ufid[] = $uf['id'];
    }
    $fres = sqlStatement("select * from facility order by name");
    if ($fres) {
        while ($frow = sqlFetchArray($fres)) {
            // Get the warehouses that are linked to this user and facility.
            $whids = getUserFacWH($_GET['id'], $frow['id']); // from calendar.inc
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
 <td><select id="access_group_id" name="access_group[]" multiple style="width:150px;" class="form-control">
<?php
// Collect the access control group of user
$list_acl_groups = AclExtended::aclGetGroupTitleList($is_super_user || $selected_user_is_superuser);
$username_acl_groups = AclExtended::aclGetGroupTitles($iter["username"]);
foreach ($list_acl_groups as $value) {
    // Disable groups that have any permissions that the logged-in user does not have.
    $tmp = AclExtended::iHaveGroupPermissions($value) ? '' : 'disabled ';
    if ($username_acl_groups && in_array($value, $username_acl_groups)) {
        $tmp .= 'selected ';
    }
    echo " <option value='" . attr($value) . "' $tmp>" . text(xl_gacl_group($value)) . "</option>\n";
}
?>
  </select></td>
  <td><span class=text><?php echo xlt('Additional Info'); ?>:</span></td>
  <td><textarea style="width:150px;" name="comments" wrap=auto rows=4 cols=25 class="form-control"><?php echo text($iter["info"]); ?></textarea></td>

  </tr>
    <tr>
        <td><span class=text><?php echo xlt('Default Billing Facility'); ?>: </span></td><td><select name="billing_facility_id" style="width:150px;" class="form-control">
            <?php
            $fres = $facilityService->getAllBillingLocations();
            if ($fres) {
                $billResults = [];
                for ($iter2 = 0; $iter2 < sizeof($fres); $iter2++) {
                    $billResults[$iter2] = $fres[$iter2];
                }

                foreach ($billResults as $iter2) {
                    ?>
                    <option value="<?php echo attr($iter2['id']); ?>" <?php if ($iter['billing_facility_id'] == $iter2['id']) {
                        echo "selected";
                                   } ?>><?php echo text($iter2['name']); ?></option>
                    <?php
                }
            }
            ?>
        </select></td>
        <td>

        </td>
    </tr>
  <tr height="20" valign="bottom">
  <td colspan="4" class="text">
      <p>*<?php echo xlt('You must enter your own password to change user passwords. Leave blank to keep password unchanged.'); ?></p>
    <?php
    if (!$is_super_user && $selected_user_is_superuser) {
        echo '<p class="redtext">*' . xlt('View mode - only administrator can edit another administrator user') . '.</p>';
    }
    ?>
<!--
Display red alert if entered password matched one of last three passwords/Display red alert if user password is expired
-->
  <div class="redtext" id="error_message">&nbsp;</div>
  </td>
  </tr>

</table>

<INPUT TYPE="HIDDEN" NAME="id" VALUE="<?php echo attr($_GET["id"]); ?>">
<INPUT TYPE="HIDDEN" NAME="mode" VALUE="update">
<INPUT TYPE="HIDDEN" NAME="privatemode" VALUE="user_admin">

<INPUT TYPE="HIDDEN" NAME="secure_pwd" VALUE="<?php echo attr($GLOBALS['secure_password']); ?>">
</FORM>
<script>
$(function () {
    $("#cancel").click(function() {
          dlgclose();
     });

});
</script>

<div class="container">

</BODY>

</HTML>
