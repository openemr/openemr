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
require_once("$srcdir/calendar.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Menu\MainMenuRole;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\UserService;
use OpenEMR\Events\User\UserEditRenderEvent;

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

<?php Header::setupHeader(['common','opener', 'erx', 'select2']); ?>

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
<title><?php echo xlt('Edit User'); ?>&nbsp;<?php echo $iter['fname'] . " " . $iter['lname']; ?></title>
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
<div class="d-flex justify-content-space">
    <div class="flex-grow-1 ">
        <h4 class="py-2"><?php echo xlt('Edit User'); ?>&nbsp;<?php echo $iter['fname'] . " " . $iter['lname']; ?></h4>
    </div>
    <div>
        <a href="usergroup_admin.php" class="btn btn-text btn-lg"><i class="fa fa-arrow-left"></i>&nbsp;<?php echo xlt("Back to User List"); ?></a>
    </div>
</div>
<form name="user_form" id="user_form" method="POST" action="usergroup_admin.php">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<input type=hidden name="pre_active" value="<?php echo attr($iter["active"]); ?>" >
<input type=hidden name="get_admin_id" value="<?php echo attr($GLOBALS['Emergency_Login_email']); ?>" >
<input type=hidden name="admin_id" value="<?php echo attr($GLOBALS['Emergency_Login_email_id']); ?>" >
<input type=hidden name="check_acl" value="">
<input type=hidden name="user_type" value="<?php echo attr($bg_name); ?>" >
<?php
// TODO: we eventually want to move to a responsive layout and not use tables here.  So we are going to give
// module writers the ability to inject divs, tables, or whatever inside the cell instead of having them
// generate additional rows / table columns which locks us into that format.
$preRenderEvent = new UserEditRenderEvent('user_admin.php', $_GET['id']);
$GLOBALS['kernel']->getEventDispatcher()->dispatch($preRenderEvent, UserEditRenderEvent::EVENT_USER_EDIT_RENDER_BEFORE);
?>
<div class="form-row">
    <div class="form-group col-sm-12 col-md-3">
        <label for="username"><?php echo xlt("Username");?></label>
        <input type="text" name="username" class="form-control" value="<?php echo attr($iter["username"]); ?>" readonly>
    </div>
    <div class="form-group col-sm-12 col-md-3">
        <label for="clearPass"><?php echo xlt("New Password"); ?></label>
        <input type='password' id="clearPass" name="clearPass" class="form-control" value="" placeholder="<?php echo xlt("New Password") ?>">
    </div>
    <div class="form-group col-sm-12 col-md-2 pt-3">
        <div class="custom-control custom-switch">
            <input type="checkbox" id="togglePass" name="togglePass" onclick="toggle_password()" class="custom-control-input">
            <label for="togglePass" class="custom-control-label"><?php echo xlt('Show Password'); ?></label>
        </div>
        <div class="custom-control custom-switch">
            <input type="checkbox" name="clear_2fa" id="clear_2fa" class="custom-control-input" value="">
            <label for="clear_2fa" class="custom-control-label" title="<?php echo xla('Remove multi-factor authentications for this person.'); ?>"><?php echo xlt('Clear 2FA'); ?></label>
        </div>
    </div>
    <div class="form-group col-sm-12 col-md-2 pt-3">
        <div class="custom-control custom-switch">
            <input type="checkbox" name="authorized" id="authorized" class="custom-control-input" onclick="authorized_clicked()" <?php echo ($iter["authorized"] ? "checked" : "");?>>
            <label for="authorized" class="custom-control-label"><?php echo xlt("Provider");?></label>
        </div>
        <div class="custom-control custom-switch">
            <input type="checkbox" name="calendar" id="calendar" class="custom-control-input" <?php echo ($iter["calendar"] ? "checked" : "");?> <?php echo (!$iter["calendar"]) ? "disabled" : ""; ?> >
            <label for="calendar" class="custom-control-label"><?php echo xlt("Calendar");?></label>
        </div>
    </div>
    <div class="form-group col-sm-12 col-md-2 pt-3">
        <div class="custom-control custom-switch">
            <input type="checkbox" name="portal_user" id="portal_user" class="custom-control-input" <?php echo ($iter["portal_user"] ? "checked" : "");?>>
            <label for="portal_user" class="custom-control-label"><?php echo xlt("Portal");?></label>
        </div>
        <div class="custom-control custom-switch">
            <input type="checkbox" name="active" id="active" class="custom-control-input" <?php echo ($iter["active"] ? "checked" : "");?>>
            <label for="active" class="custom-control-label"><?php echo xlt("Active");?></label>
        </div>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-sm-12 col-md-4">
        <label for="fname"><?php echo xlt("First Name"); ?></label>
        <input type="text" name="fname" id="fname" class="form-control" value="<?php echo attr($iter["fname"]); ?>" placeholder="First Name" required><span class="mandatory"></span>
    </div>
    <div class="form-group col-sm-12 col-md-4">
        <label for="fname"><?php echo xlt("Middle Name"); ?></label>
        <input type="text" name="mname" id="mname" class="form-control" value="<?php echo attr($iter["mname"]); ?>" placeholder="Middle Name">
    </div>
    <div class="form-group col-sm-12 col-md-4">
        <label for="fname"><?php echo xlt("Last Name"); ?></label>
        <input type="text" name="lname" id="lname" class="form-control" value="<?php echo attr($iter["lname"]); ?>" placeholder="Last Name" required><span class="mandatory"></span></td>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-sm-12 col-md-2">
        <label for="taxonomy"><?php echo xlt("Taxonomy"); ?></label>
        <input type="text" name="taxonomy" class="form-control" value="<?php echo attr($iter["taxonomy"]); ?>">
    </div>
    <div class="form-group col-sm-12 col-md-2">
        <label for="state_license_number"><?php echo xlt("State License Number"); ?></label>
        <input type="text" name="state_license_number" class="form-control" value="<?php echo attr($iter["state_license_number"]); ?>">
    </div>
    <div class="form-group col-sm-12 col-md-2 d-none" data-specificity="provider">
        <label for="taxid"><?php echo xlt("Federal Tax ID");?></label>
        <input type="text" name="taxid" class="form-control" value="<?php echo attr($iter["federaltaxid"]); ?>">
    </div>
    <div class="form-group col-sm-12 col-md-2 d-none" data-specificity="provider">
        <label for="drugid"><?php echo xlt("DEA Number"); ?></label>
        <input type="text" name="drugid" class="form-control" value="<?php echo attr($iter["federaldrugid"]); ?>">
    </div>
    <div class="form-group col-sm-12 col-md-2 d-none" data-specificity="provider">
        <label for="upin"><?php echo xlt("UPIN"); ?></label>
        <input type="text" name="upin" class="form-control" value="<?php echo attr($iter["upin"]); ?>">
    </div>
    <div class="form-group col-sm-12 col-md-2 d-none" data-specificity="provider">
        <label for="npi"><?php echo xlt("NPI"); ?></label>
        <input type="text" name="npi" class="form-control" value="<?php echo attr($iter["npi"]); ?>">
    </div>
</div>
<div class="form-row connectors">
    <?php if ($GLOBALS['erx_enable']) : ?>
    <div class="form-group col-sm-12 col-md-4">
        <label for="newcrop_erx_role"><?php echo xlt("NewCrop eRX Role"); ?></label>
        <?php echo generate_select_list("erxrole", "newcrop_erx_role", $iter['newcrop_user_role'], '', xl('Select Role'), '', '', '', []); ?>
    </div>
    <?php endif; ?>
    <?php if ($GLOBALS['weno_rx_enable']) : ?>
    <div class="form-group col-sm-12 col-md-4">
        <label for="erxprid"><?php echo xlt('Weno Provider ID'); ?></label>
        <input type="text" name="erxprid" class="form-control" value="<?php echo attr($iter["weno_prov_id"]); ?>">
    </div>
    <?php endif; ?>
    <?php if ($GLOBALS['google_signin_enabled']) : ?>
    <div class="form-group col-sm-12 col-md-4">
        <label for="google_signin_email"><?php echo xlt('Google Email for Login'); ?></label>
        <input type="text" name="google_signin_email" class="form-control" value="<?php echo attr($iter["google_signin_email"]); ?>">
    </div>
    <?php endif; ?>
</div>
<div class="form-row">
    <div class="form-group col-sm-12 col-md-4">
        <label for="facility_id"><?php echo xlt('Default Facility'); ?></label>
        <select name="facility_id" class="form-control">
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
        </select>
    </div>
    <div class="form-group col-sm-12 col-md-4">
        <label for="billing_facility_id"><?php echo xlt('Default Billing Facility'); ?></label>
        <select name="billing_facility_id" class="form-control">
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
        </select>
    </div>
    <?php if (!$GLOBALS['restrict_user_facility']) : ?>
    <div class="form-group col-sm-12 col-md-4">
        <label for="schedule_facility[]"><?php echo xlt('Schedule Facilities:');?></label>
        <select name="schedule_facility[]" multiple="multiple" class="form-control select2">
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
    </div>
    <?php endif; ?>
</div>
<div class="form-row">
    <div class="form-group col-sm-12 col-md-4">
        <label for="see_auth"><?php echo xlt('See Authorizations'); ?></label>
        <select name="see_auth" class="form-control" >
            <?php
            foreach (array(1 => xl('None{{Authorization}}'), 2 => xl('Only Mine'), 3 => xl('All')) as $key => $value) {
                echo " <option value='" . attr($key) . "'";
                if ($key == $iter['see_auth']) {
                    echo " selected";
                }

                echo ">" . text($value) . "</option>\n";
            }
            ?>
        </select>
    </div>
    <div class="form-group col-sm-12 col-md-4">
        <label for="supervisor_id"><?php echo xlt('Supervisor'); ?></label>
        <select name="supervisor_id" class="form-control">
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
    </div>
    <div class="form-group col-sm-12 col-md-4">
        <label for="physician_type"><?php echo xlt('Provider Type'); ?></label>
        <?php echo generate_select_list("physician_type", "physician_type", $iter['physician_type'], '', xl('Select Type'), 'physician_type_class', '', '', ''); ?>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-sm-12 col-md-4">
        <label for="main_menu_role"><?php echo xlt('Main Menu Role'); ?></label>
        <?php
        $menuMain = new MainMenuRole($GLOBALS['kernel']->getEventDispatcher());
        echo $menuMain->displayMenuRoleSelector($iter["main_menu_role"]);
        ?>
    </div>
    <div class="form-group col-sm-12 col-md-4">
        <label for="patient_menu_role"><?php echo xlt('Patient Menu Role'); ?></label>
        <?php
        $menuPatient = new PatientMenuRole();
        echo $menuPatient->displayMenuRoleSelector($iter["patient_menu_role"]);
        ?>
    </div>
    <div class="form-group col-sm-12 col-md-4">
        <label for="access_group_id"><?php echo xlt('Access Control'); ?></label>
        <select id="access_group_id" name="access_group[]" multiple="multiple" class="form-control select2">
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
        </select>
    </div>
</div>
<div class="form-row">
    <div class="form-group col-sm-12 col-md-3">
        <label for="job"><?php echo xlt('Job Description'); ?></label>
        <input type="text" name="job" class="form-control" value="<?php echo attr($iter["specialty"]); ?>">
    </div>
    <?php if (!empty($GLOBALS['inhouse_pharmacy'])) : ?>
    <div class="form-group col-sm-12 col-md-3">
        <label><?php echo xlt('Default Warehouse'); ?></label>
        <?php echo generate_select_list('default_warehouse', 'warehouse', $iter['default_warehouse'], '');?>
    </div>
    <div class="form-group col-sm-12 col-md-3">
        <label for="irnpool"><?php echo xlt('Invoice Refno Pool'); ?></label>
        <?php echo generate_select_list('irnpool', 'irnpool', $iter['irnpool'], xl('Invoice reference number pool, if used')); ?>
    </div>
    <?php endif; ?>
    <?php if (!empty($GLOBALS['gbl_fac_warehouse_restrictions']) || !empty($GLOBALS['restrict_user_facility'])) : ?>
    <div class="form-group col-sm-12 col-md-3">
        <label for="schedule_facility" title="<?php echo xla('If nothing is selected here then all are permitted.'); ?>"><?php echo !empty($GLOBALS['gbl_fac_warehouse_restrictions']) ? xlt('Facility and warehouse permissions') : xlt('Facility permissions'); ?></label>
        <select name="schedule_facility[]" multiple="multiple" class="form-control select2">
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
                $whids = getUserFacWH($_GET['id'], $frow['id']); // from calendar.inc.php
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
    </div>
    <?php endif; ?>
</div>
<div class="form-row">
    <div class="form-group col-sm-12 col-md-12">
        <label for="additional_info"><?php echo xlt('Additional Info'); ?></label>
        <textarea name="comments" wrap="auto" rows="" class="form-control"><?php echo text($iter["info"]); ?></textarea>
    </div>
</div>
<?php
// TODO: we eventually want to move to a responsive layout and not use tables here.  So we are going to give
// module writers the ability to inject divs, tables, or whatever inside the cell instead of having them
// generate additional rows / table columns which locks us into that format.
$postRenderEvent = new UserEditRenderEvent('user_admin.php', $_GET['id']);
$GLOBALS['kernel']->getEventDispatcher()->dispatch($postRenderEvent, UserEditRenderEvent::EVENT_USER_EDIT_RENDER_AFTER);
?>
<div class="d-flex">
    <div class="flex-grow-1 pr-2">
        <p>
            <?php echo xlt('You must enter your own password to change user passwords. Leave blank to keep password unchanged.'); ?>
            <?php if (!$is_super_user && $selected_user_is_superuser) : ?>
                <br><?php echo xlt('View mode - only administrator can edit another administrator user'); ?>
            <?php endif; ?>
            <!-- Display red alert if entered password matched one of last three passwords/Display red alert if user password is expired -->
            <div class="redtext" id="error_message">&nbsp;</div>
        </p>
    </div>
    <div>
        <?php if (empty($GLOBALS['gbl_ldap_enabled']) || empty($GLOBALS['gbl_ldap_exclusions'])) : ?>
            <label for="adminPass"><?php echo xlt('Your Password'); ?></label>
            <input type='password' name="adminPass" class="form-control" value="" autocomplete='off'>
        <?php endif; ?>
    </div>
    <div>
        <a class="btn btn-link btn-cancel" id='cancel' href='#'><span><?php echo xlt('Cancel');?></span></a>
        <a class="btn btn-secondary btn-save" name='form_save' id='form_save' href='#' onclick='return submitform()' <?php echo $disabled_save; ?>> <span><?php echo xlt('Save');?></span> </a>
    </div>
</div>
<input type="hidden" name="id" value="<?php echo attr($_GET["id"]); ?>">
<input type="hidden" name="mode" value="update">
<input type="hidden" name="privatemode" value="user_admin">
<input type="hidden" name="secure_pwd" value="<?php echo attr($GLOBALS['secure_password']); ?>">
</form>
<script>
$(function () {
    $("#cancel").click(function() {
        dlgclose();
    });
    renderProviderSpecificAttributes();
    $(".select2").select2();
});

let provSpec = document.querySelectorAll("[data-specificity=provider]");
let isProvider = document.getElementById("authorized");

isProvider.addEventListener('change', (e) => {
    renderProviderSpecificAttributes();
});

function renderProviderSpecificAttributes()
{
    provSpec.forEach((elm) => {
        if (isProvider.checked) {
            elm.classList.remove('d-none');
        } else {
            elm.classList.add('d-none');
        }
    });
}

</script>
</body>
</html>
