<?php

/**
 * This script assigns ACL 'Emergency login'.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Pflieger <daniel@mi-squared.com> <daniel@growlingflea.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Robert DOwn <robertdown@live.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Daniel Pflieger <daniel@mi-squared.com> <daniel@growlingflea.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2022-2023 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$sessionAllowWrite = true;
require_once("../globals.php");

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\UserService;
use OpenEMR\Events\User\UserUpdatedEvent;
use OpenEMR\Events\User\UserCreatedEvent;

if (!empty($_REQUEST)) {
    if (!CsrfUtils::verifyCsrfToken($_REQUEST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("User / Groups")]);
    exit;
}

if (!AclMain::aclCheckCore('admin', 'super')) {
    //block non-administrator user from create administrator
    if (!empty($_POST['access_group'])) {
        foreach ($_POST['access_group'] as $aro_group) {
            if (AclExtended::isGroupIncludeSuperuser($aro_group)) {
                die(xlt('Saving denied'));
            };
        }
    }
    if (($_POST['mode'] ?? '') === 'update') {
        //block non-administrator user from update administrator
        $user_service = new UserService();
        $user = $user_service->getUser($_POST['id']);
        $aro_groups = AclExtended::aclGetGroupTitles($user['username']);
        foreach ($aro_groups as $aro_group) {
            if (AclExtended::isGroupIncludeSuperuser($aro_group)) {
                die(xlt('Saving denied'));
            };
        }
    }
}

$alertmsg = '';
$bg_msg = '';
$set_active_msg = 0;
$show_message = 0;

/* Sending a mail to the admin when the breakglass user is activated only if $GLOBALS['Emergency_Login_email'] is set to 1 */
if (!empty($_POST['access_group']) && is_array($_POST['access_group'])) {
    $bg_count = count($_POST['access_group']);
    $mail_id = explode(".", $SMTP_HOST);
    for ($i = 0; $i < $bg_count; $i++) {
        if (($_POST['access_group'][$i] == "Emergency Login") && ($_POST['active'] == 'on') && ($_POST['pre_active'] == 0)) {
            if (($_POST['get_admin_id'] == 1) && ($_POST['admin_id'] != "")) {
                $res = sqlStatement("select username from users where id= ? ", array($_POST["id"]));
                $row = sqlFetchArray($res);
                $uname = $row['username'];
                $mail = new MyMailer();
                $mail->From = $GLOBALS["practice_return_email_path"];
                $mail->FromName = "Administrator OpenEMR";
                $text_body = "Hello Security Admin,\n\n The Emergency Login user " . $uname .
                    " was activated at " . date('l jS \of F Y h:i:s A') . " \n\nThanks,\nAdmin OpenEMR.";
                $mail->Body = $text_body;
                $mail->Subject = "Emergency Login User Activated";
                $mail->AddAddress($_POST['admin_id']);
                $mail->Send();
            }
        }
    }
}

/* To refresh and save variables in mail frame */
if (isset($_POST["privatemode"]) && $_POST["privatemode"] == "user_admin") {
    if ($_POST["mode"] == "update") {
        $user_data = sqlFetchArray(sqlStatement("select * from users where id= ? ", array($_POST["id"])));

        if (isset($_POST["username"])) {
            sqlStatement("update users set username=? where id= ? ", array(trim($_POST["username"]), $_POST["id"]));
            sqlStatement("update `groups` set user=? where user= ?", array(trim($_POST["username"]), $user_data["username"]));
        }

        if ($_POST["taxid"]) {
            sqlStatement("update users set federaltaxid=? where id= ? ", array($_POST["taxid"], $_POST["id"]));
        }

        if ($_POST["state_license_number"]) {
            sqlStatement("update users set state_license_number=? where id= ? ", array($_POST["state_license_number"], $_POST["id"]));
        }

        if ($_POST["drugid"]) {
            sqlStatement("update users set federaldrugid=? where id= ? ", array($_POST["drugid"], $_POST["id"]));
        }

        if ($_POST["upin"]) {
            sqlStatement("update users set upin=? where id= ? ", array($_POST["upin"], $_POST["id"]));
        }

        if ($_POST["npi"]) {
            sqlStatement("update users set npi=? where id= ? ", array($_POST["npi"], $_POST["id"]));
        }

        if ($_POST["taxonomy"]) {
            sqlStatement("update users set taxonomy = ? where id= ? ", array($_POST["taxonomy"], $_POST["id"]));
        }

        if ($_POST["lname"]) {
            sqlStatement("update users set lname=? where id= ? ", array($_POST["lname"], $_POST["id"]));
        }

        if ($_POST["suffix"]) {
            sqlStatement("update users set suffix=? where id= ? ", array($_POST["suffix"], $_POST["id"]));
        }

        if ($_POST["valedictory"]) {
            sqlStatement("update users set valedictory=? where id= ? ", array($_POST["valedictory"], $_POST["id"]));
        }

        if ($_POST["job"]) {
            sqlStatement("update users set specialty=? where id= ? ", array($_POST["job"], $_POST["id"]));
        }

        if ($_POST["mname"]) {
            sqlStatement("update users set mname=? where id= ? ", array($_POST["mname"], $_POST["id"]));
        }

        if ($_POST["facility_id"]) {
            sqlStatement("update users set facility_id = ? where id = ? ", array($_POST["facility_id"], $_POST["id"]));
            //(CHEMED) Update facility name when changing the id
            sqlStatement("UPDATE users, facility SET users.facility = facility.name WHERE facility.id = ? AND users.id = ?", array($_POST["facility_id"], $_POST["id"]));
            //END (CHEMED)
        }

        if ($_POST["billing_facility_id"]) {
            sqlStatement("update users set billing_facility_id = ? where id = ? ", array($_POST["billing_facility_id"], $_POST["id"]));
            //(CHEMED) Update facility name when changing the id
            sqlStatement("UPDATE users, facility SET users.billing_facility = facility.name WHERE facility.id = ? AND users.id = ?", array($_POST["billing_facility_id"], $_POST["id"]));
            //END (CHEMED)
        }

        if (!empty($GLOBALS['gbl_fac_warehouse_restrictions']) || !empty($GLOBALS['restrict_user_facility'])) {
            if (empty($_POST["schedule_facility"])) {
                $_POST["schedule_facility"] = array();
            }
            $tmpres = sqlStatement(
                "SELECT * FROM users_facility WHERE " .
                "tablename = ? AND table_id = ?",
                array('users', $_POST["id"])
            );
            // $olduf will become an array of entries to delete.
            $olduf = array();
            while ($tmprow = sqlFetchArray($tmpres)) {
                $olduf[$tmprow['facility_id'] . '/' . $tmprow['warehouse_id']] = true;
            }
            // Now process the selection of facilities and warehouses.
            foreach ($_POST["schedule_facility"] as $tqvar) {
                if (($i = strpos($tqvar, '/')) !== false) {
                    $facid = substr($tqvar, 0, $i);
                    $whid = substr($tqvar, $i + 1);
                    // If there was also a facility-only selection for this warehouse then remove it.
                    if (isset($olduf["$facid/"])) {
                        $olduf["$facid/"] = true;
                    }
                } else {
                    $facid = $tqvar;
                    $whid = '';
                }
                if (!isset($olduf["$facid/$whid"])) {
                    sqlStatement(
                        "INSERT INTO users_facility SET tablename = ?, table_id = ?, " .
                        "facility_id = ?, warehouse_id = ?",
                        array('users', $_POST["id"], $facid, $whid)
                    );
                }
                $olduf["$facid/$whid"] = false;
            }
            // Now delete whatever is left over for this user.
            foreach ($olduf as $key => $value) {
                if ($value && ($i = strpos($key, '/')) !== false) {
                    $facid = substr($key, 0, $i);
                    $whid = substr($key, $i + 1);
                    sqlStatement(
                        "DELETE FROM users_facility WHERE " .
                        "tablename = ? AND table_id = ? AND facility_id = ? AND warehouse_id = ?",
                        array('users', $_POST["id"], $facid, $whid)
                        // At one time binding here screwed up by matching all warehouse_id values
                        // when it's an empty string, and so the code below was used.
                        /**********************************************
                        "tablename = 'users' AND table_id = '" . add_escape_custom($_POST["id"]) . "'" .
                        " AND facility_id = '" . add_escape_custom($facid) . "'" .
                        " AND warehouse_id = '" . add_escape_custom($whid) . "'"
                        **********************************************/
                    );
                }
            }
        }

        if ($_POST["fname"]) {
            sqlStatement("update users set fname=? where id= ? ", array($_POST["fname"], $_POST["id"]));
        }

        if (isset($_POST['default_warehouse'])) {
            sqlStatement("UPDATE users SET default_warehouse = ? WHERE id = ?", array($_POST['default_warehouse'], $_POST["id"]));
        }

        if (isset($_POST['irnpool'])) {
            sqlStatement("UPDATE users SET irnpool = ? WHERE id = ?", array($_POST['irnpool'], $_POST["id"]));
        }

        if (!empty($_POST['clear_2fa'])) {
            sqlStatement("DELETE FROM login_mfa_registrations WHERE user_id = ?", array($_POST['id']));
        }

        if ($_POST["adminPass"] && $_POST["clearPass"]) {
            $authUtilsUpdatePassword = new AuthUtils();
            $success = $authUtilsUpdatePassword->updatePassword($_SESSION['authUserID'], $_POST['id'], $_POST['adminPass'], $_POST['clearPass']);
            if (!$success) {
                error_log(errorLogEscape($authUtilsUpdatePassword->getErrorMessage()));
                $alertmsg .= $authUtilsUpdatePassword->getErrorMessage();
            }
        }

        $tqvar  = (!empty($_POST["authorized"])) ? 1 : 0;
        $actvar = (!empty($_POST["active"]))     ? 1 : 0;
        $calvar = (!empty($_POST["calendar"]))   ? 1 : 0;
        $portalvar = (!empty($_POST["portal_user"])) ? 1 : 0;

        sqlStatement("UPDATE users SET authorized = ?, active = ?, " .
        "calendar = ?, portal_user = ?, see_auth = ? WHERE " .
        "id = ? ", array($tqvar, $actvar, $calvar, $portalvar, $_POST['see_auth'], $_POST["id"]));
        //Display message when Emergency Login user was activated
        if (is_countable($_POST['access_group'])) {
            $bg_count = count($_POST['access_group']);
            for ($i = 0; $i < $bg_count; $i++) {
                if (($_POST['access_group'][$i] == "Emergency Login") && ($_POST['pre_active'] == 0) && ($actvar == 1)) {
                    $show_message = 1;
                }
            }

            if (($_POST['access_group'])) {
                for ($i = 0; $i < $bg_count; $i++) {
                    if (($_POST['access_group'][$i] == "Emergency Login") && ($_POST['user_type']) == "" && ($_POST['check_acl'] == 1) && ($_POST['active']) != "") {
                        $set_active_msg = 1;
                    }
                }
            }
        }

        if (isset($_POST["comments"])) {
            sqlStatement("update users set info = ? where id = ? ", array($_POST["comments"], $_POST["id"]));
        }

        $erxrole = $_POST['erxrole'] ?? '';
        sqlStatement("update users set newcrop_user_role = ? where id = ? ", array($erxrole, $_POST["id"]));

        if (isset($_POST["physician_type"])) {
            sqlStatement("update users set physician_type = ? where id = ? ", array($_POST["physician_type"], $_POST["id"]));
        }

        if (isset($_POST["main_menu_role"])) {
              $mainMenuRole = filter_input(INPUT_POST, 'main_menu_role');
              sqlStatement("update `users` set `main_menu_role` = ? where `id` = ? ", array($mainMenuRole, $_POST["id"]));
        }

        if (isset($_POST["patient_menu_role"])) {
            $patientMenuRole = filter_input(INPUT_POST, 'patient_menu_role');
            sqlStatement("update `users` set `patient_menu_role` = ? where `id` = ? ", array($patientMenuRole, $_POST["id"]));
        }

        if (isset($_POST["erxprid"])) {
            sqlStatement("update users set weno_prov_id = ? where id = ? ", array($_POST["erxprid"], $_POST["id"]));
        }

        if (isset($_POST["supervisor_id"])) {
            sqlStatement("update users set supervisor_id = ? where id = ? ", array((int)$_POST["supervisor_id"], $_POST["id"]));
        }
        if (isset($_POST["google_signin_email"])) {
            if (empty($_POST["google_signin_email"])) {
                $googleSigninEmail = null;
            } else {
                $googleSigninEmail = $_POST["google_signin_email"];
            }
            sqlStatement("update users set google_signin_email = ? where id = ? ", array($googleSigninEmail, $_POST["id"]));
        }

        // Set the access control group of user
        $user_data = sqlFetchArray(sqlStatement("select username from users where id= ?", array($_POST["id"])));
        AclExtended::setUserAro(
            $_POST['access_group'],
            $user_data["username"],
            (isset($_POST['fname']) ? $_POST['fname'] : ''),
            (isset($_POST['mname']) ? $_POST['mname'] : ''),
            (isset($_POST['lname']) ? $_POST['lname'] : '')
        );

        // TODO: why are we sending $user_data here when its overwritten with just the 'username' of the user updated
        // instead of the entire user data?  This makes the pre event data not very useful w/o doing a database hit...
        $userUpdatedEvent = new UserUpdatedEvent($user_data, $_POST);
        $GLOBALS["kernel"]->getEventDispatcher()->dispatch($userUpdatedEvent, UserUpdatedEvent::EVENT_HANDLE, 10);
    }
}

/* To refresh and save variables in mail frame  - Arb*/
if (isset($_POST["mode"])) {
    if ($_POST["mode"] == "new_user") {
        if (empty($_POST["authorized"]) || $_POST["authorized"] != "1") {
            $_POST["authorized"] = 0;
        }

        $calvar = (!empty($_POST["calendar"])) ? 1 : 0;
        $portalvar = (!empty($_POST["portal_user"])) ? 1 : 0;

        $res = sqlQuery("select username from users where username = ?", [trim($_POST['rumple'])]);
        $doit = true;
        if (!empty($res['username'])) {
            $doit = false;
        }

        if ($doit == true) {
            // google_signin_email has unique key constraint, needs to be handled differently
            $googleSigninEmail = "NULL";
            if (isset($_POST["google_signin_email"])) {
                if (empty($_POST["google_signin_email"])) {
                    $googleSigninEmail = "NULL";
                } else {
                    $googleSigninEmail = "'" . add_escape_custom(trim($_POST["google_signin_email"])) . "'";
                }
            }
            $insertUserSQL =
            "insert into users set " .
            "username = '"         . add_escape_custom(trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))) .
            "', password = '"      . 'NoLongerUsed'                  .
            "', fname = '"         . add_escape_custom(trim((isset($_POST['fname']) ? $_POST['fname'] : ''))) .
            "', mname = '"         . add_escape_custom(trim((isset($_POST['mname']) ? $_POST['mname'] : ''))) .
            "', lname = '"         . add_escape_custom(trim((isset($_POST['lname']) ? $_POST['lname'] : ''))) .
            "', suffix = '"         . add_escape_custom(trim((isset($_POST['suffix']) ? $_POST['suffix'] : ''))) .
            "', google_signin_email = " . $googleSigninEmail .
            ", valedictory = '"         . add_escape_custom(trim((isset($_POST['valedictory']) ? $_POST['valedictory'] : ''))) .
            "', federaltaxid = '"  . add_escape_custom(trim((isset($_POST['federaltaxid']) ? $_POST['federaltaxid'] : ''))) .
            "', state_license_number = '"  . add_escape_custom(trim((isset($_POST['state_license_number']) ? $_POST['state_license_number'] : ''))) .
            "', newcrop_user_role = '"  . add_escape_custom(trim((isset($_POST['erxrole']) ? $_POST['erxrole'] : ''))) .
            "', physician_type = '"  . add_escape_custom(trim((isset($_POST['physician_type']) ? $_POST['physician_type'] : ''))) .
            "', main_menu_role = '"  . add_escape_custom(trim((isset($_POST['main_menu_role']) ? $_POST['main_menu_role'] : ''))) .
            "', patient_menu_role = '"  . add_escape_custom(trim((isset($_POST['patient_menu_role']) ? $_POST['patient_menu_role'] : ''))) .
            "', weno_prov_id = '"  . add_escape_custom(trim((isset($_POST['erxprid']) ? $_POST['erxprid'] : ''))) .
            "', authorized = '"    . add_escape_custom(trim((isset($_POST['authorized']) ? $_POST['authorized'] : ''))) .
            "', info = '"          . add_escape_custom(trim((isset($_POST['info']) ? $_POST['info'] : ''))) .
            "', federaldrugid = '" . add_escape_custom(trim((isset($_POST['federaldrugid']) ? $_POST['federaldrugid'] : ''))) .
            "', upin = '"          . add_escape_custom(trim((isset($_POST['upin']) ? $_POST['upin'] : ''))) .
            "', npi  = '"          . add_escape_custom(trim((isset($_POST['npi']) ? $_POST['npi'] : ''))) .
            "', taxonomy = '"      . add_escape_custom(trim((isset($_POST['taxonomy']) ? $_POST['taxonomy'] : ''))) .
            "', facility_id = '"   . add_escape_custom(trim((isset($_POST['facility_id']) ? $_POST['facility_id'] : ''))) .
            "', billing_facility_id = '"   . add_escape_custom(trim((isset($_POST['billing_facility_id']) ? $_POST['billing_facility_id'] : ''))) .
            "', specialty = '"     . add_escape_custom(trim((isset($_POST['specialty']) ? $_POST['specialty'] : ''))) .
            "', see_auth = '"      . add_escape_custom(trim((isset($_POST['see_auth']) ? $_POST['see_auth'] : ''))) .
            "', default_warehouse = '" . add_escape_custom(trim((isset($_POST['default_warehouse']) ? $_POST['default_warehouse'] : ''))) .
            "', irnpool = '"       . add_escape_custom(trim((isset($_POST['irnpool']) ? $_POST['irnpool'] : ''))) .
            "', calendar = '"      . add_escape_custom($calvar) .
            "', portal_user = '"   . add_escape_custom($portalvar) .
            "', supervisor_id = '" . add_escape_custom((isset($_POST['supervisor_id']) ? (int)$_POST['supervisor_id'] : 0)) .
            "'";

            $authUtilsNewPassword = new AuthUtils();
            $success = $authUtilsNewPassword->updatePassword(
                $_SESSION['authUserID'],
                0,
                $_POST['adminPass'],
                $_POST['stiltskin'],
                true,
                $insertUserSQL,
                trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))
            );
            if (!empty($authUtilsNewPassword->getErrorMessage())) {
                $alertmsg .= $authUtilsNewPassword->getErrorMessage();
            }
            if ($success) {
                // generate our uuid
                $uuid = UuidRegistry::getRegistryForTable('users')->createUuid();
                //set the facility name from the selected facility_id
                sqlStatement(
                    "UPDATE users, facility SET users.facility = facility.name, users.uuid =? WHERE facility.id = ? AND users.username = ?",
                    array(
                        $uuid,
                        trim((isset($_POST['facility_id']) ? $_POST['facility_id'] : '')),
                        trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))
                    )
                );

                //set the billing facility name from the selected billing_facility_id
                sqlStatement(
                    "UPDATE users, facility SET users.billing_facility = facility.name, users.uuid =? WHERE facility.id = ? AND users.username = ?",
                    array(
                        $uuid,
                        trim((isset($_POST['billing_facility_id']) ? $_POST['billing_facility_id'] : '')),
                        trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))
                    )
                );

                sqlStatement(
                    "insert into `groups` set name = ?, user = ?",
                    array(
                        trim((isset($_POST['groupname']) ? $_POST['groupname'] : '')),
                        trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))
                    )
                );

                if (trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))) {
                              // Set the access control group of user
                              AclExtended::setUserAro(
                                  $_POST['access_group'],
                                  trim((isset($_POST['rumple']) ? $_POST['rumple'] : '')),
                                  trim((isset($_POST['fname']) ? $_POST['fname'] : '')),
                                  trim((isset($_POST['mname']) ? $_POST['mname'] : '')),
                                  trim((isset($_POST['lname']) ? $_POST['lname'] : ''))
                              );
                }
            }
        } else {
            $alertmsg .= xl('User') . ' ' . trim((isset($_POST['rumple']) ? $_POST['rumple'] : '')) . ' ' . xl('already exists.');
        }

        if ($_POST['access_group']) {
            $bg_count = count($_POST['access_group']);
            for ($i = 0; $i < $bg_count; $i++) {
                if ($_POST['access_group'][$i] == "Emergency Login") {
                      $set_active_msg = 1;
                }
            }
        }

        // this event should only fire if we actually succeeded in creating the user...
        if ($success) {
            // let's make sure we send on our uuid alongside the id of the user
            $submittedData = $_POST;
            $submittedData['uuid'] = $uuid ?? null;
            $submittedData['username'] = $submittedData['rumple'] ?? null;
            $userCreatedEvent = new UserCreatedEvent($submittedData);
            unset($submittedData); // clear things out in case we have any sensitive data here
            $GLOBALS["kernel"]->getEventDispatcher()->dispatch($userCreatedEvent, UserCreatedEvent::EVENT_HANDLE, 10);
        }
    } elseif ($_POST["mode"] == "new_group") {
        $res = sqlStatement("select distinct name, user from `groups`");
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result[$iter] = $row;
        }

        $doit = 1;
        foreach ($result as $iter) {
            if ($doit == 1 && $iter["name"] == (trim((isset($_POST['groupname']) ? $_POST['groupname'] : ''))) && $iter["user"] == (trim((isset($_POST['rumple']) ? $_POST['rumple'] : '')))) {
                $doit--;
            }
        }

        if ($doit == 1) {
            sqlStatement(
                "insert into `groups` set name = ?, user = ?",
                array(
                    trim((isset($_POST['groupname']) ? $_POST['groupname'] : '')),
                    trim((isset($_POST['rumple']) ? $_POST['rumple'] : ''))
                )
            );
        } else {
            $alertmsg .= "User " . trim((isset($_POST['rumple']) ? $_POST['rumple'] : '')) .
            " is already a member of group " . trim((isset($_POST['groupname']) ? $_POST['groupname'] : '')) . ". ";
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
      sqlStatement("delete from `groups` where user = '" . $iter["username"] . "'");
    }
    sqlStatement("delete from users where id = '" . $_GET["id"] . "'");
  }
  *******************************************************************/

    if ($_GET["mode"] == "delete_group") {
        $res = sqlStatement("select distinct user from `groups` where id = ?", array($_GET["id"]));
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $result[$iter] = $row;
        }

        foreach ($result as $iter) {
            $un = $iter["user"];
        }

        $res = sqlStatement("select name, user from `groups` where user = ? " .
        "and id != ?", array($un, $_GET["id"]));

        // Remove the user only if they are also in some other group.  I.e. every
        // user must be a member of at least one group.
        if (sqlFetchArray($res) != false) {
              sqlStatement("delete from `groups` where id = ?", array($_GET["id"]));
        } else {
              $alertmsg .= "You must add this user to some other group before " .
                "removing them from this group. ";
        }
    }
}
// added for form submits from usergroup_admin_add and user_admin.php
// sjp 12/29/17
if (isset($_REQUEST["mode"])) {
    exit(text(trim($alertmsg)));
}

$form_inactive = !empty($_POST['form_inactive']);

?>
<html>
<head>
<title><?php echo xlt('User / Groups');?></title>

<?php Header::setupHeader(['common']); ?>

<script>

$(function () {

    tabbify();

    $(".medium_modal").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 'modal-mlg', 450, '', '', {
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

});

function authorized_clicked() {
 var f = document.forms[0];
 f.calendar.disabled = !f.authorized.checked;
 f.calendar.checked  =  f.authorized.checked;
}

function resetCounter(username) {
    top.restoreSession();
    request = new FormData;
    request.append("function", "resetUsernameCounter");
    request.append("username", username);
    request.append("csrf_token_form", <?php echo js_escape(CsrfUtils::collectCsrfToken('counter')); ?>);
    fetch("<?php echo $GLOBALS["webroot"]; ?>/library/ajax/login_counter_ip_tracker.php", {
        method: 'POST',
        credentials: 'same-origin',
        body: request
    });
    let loginCounterElement = document.getElementById('login-counter-' + username);
    loginCounterElement.innerHTML = "0";
}

</script>

</head>
<body class="body_top">

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="page-title">
                <h2><?php echo xlt('User / Groups');?></h2>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="btn-group">
                <a href="usergroup_admin_add.php" class="medium_modal btn btn-secondary btn-add"><?php echo xlt('Add User'); ?></a>
                <a href="facility_user.php" class="btn btn-secondary btn-show"><?php echo xlt('View Facility Specific User Information'); ?></a>
            </div>
            <form name='userlist' method='post' style="display: inline;" class="form-inline" class="float-right" action='usergroup_admin.php' onsubmit='return top.restoreSession()'>
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <div class="checkbox">
                    <label for="form_inactive">
                        <input type='checkbox' class="form-control" id="form_inactive" name='form_inactive' value='1' onclick='submit()' <?php echo ($form_inactive) ? 'checked ' : ''; ?>>
                        <?php echo xlt('Include inactive users'); ?>
                    </label>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <?php
            if ($set_active_msg == 1) {
                echo "<div class='alert alert-danger'>" . xlt('Emergency Login ACL is chosen. The user is still in active state, please de-activate the user and activate the same when required during emergency situations. Visit Administration->Users for activation or de-activation.') . "</div><br />";
            }

            if ($show_message == 1) {
                echo "<div class='alert alert-danger'>" . xlt('The following Emergency Login User is activated:') . " " . "<b>" . text($_GET['fname']) . "</b>" . "</div><br />";
                echo "<div class='alert alert-danger'>" . xlt('Emergency Login activation email will be circulated only if following settings in the interface/globals.php file are configured:') . " \$GLOBALS['Emergency_Login_email'], \$GLOBALS['Emergency_Login_email_id']</div>";
            }

            ?>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th><?php echo xlt('Username'); ?></th>
                            <th><?php echo xlt('Real Name'); ?></th>
                            <th><?php echo xlt('Additional Info'); ?></th>
                            <th><?php echo xlt('Authorized'); ?></th>
                            <th><?php echo xlt('MFA'); ?></th>
                            <?php
                            $checkPassExp = false;
                            if (($GLOBALS['password_expiration_days'] != 0) && (check_integer($GLOBALS['password_expiration_days'])) && (check_integer($GLOBALS['password_grace_time']))) {
                                $checkPassExp = true;
                                echo '<th>' . xlt('Password Expiration') . '</th>';
                            }
                            ?>
                            <th><?php echo xlt('Failed Login Counter'); ?></th>
                        </tr>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM users WHERE username != '' ";
                        if (!$form_inactive) {
                            $query .= "AND active = '1' ";
                        }

                        $query .= "ORDER BY username";
                        $res = sqlStatement($query);
                        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                            $result4[$iter] = $row;
                        }

                        foreach ($result4 as $iter) {
                            // Skip this user if logged-in user does not have all of its permissions.
                            // Note that a superuser now has all permissions.
                            if (!AclExtended::iHavePermissionsOf($iter['username'])) {
                                continue;
                            }

                            if ($iter["authorized"]) {
                                $iter["authorized"] = xl('yes');
                            } else {
                                $iter["authorized"] = xl('no');
                            }

                            $mfa = sqlQuery(
                                "SELECT `method` FROM `login_mfa_registrations` " .
                                "WHERE `user_id` = ? AND (`method` = 'TOTP' OR `method` = 'U2F')",
                                [$iter['id']]
                            );
                            if (!empty($mfa['method'])) {
                                $isMfa = xl('yes');
                            } else {
                                $isMfa = xl('no');
                            }

                            if ($checkPassExp && !empty($iter["active"])) {
                                $current_date = date("Y-m-d");
                                $userSecure = privQuery("SELECT `last_update_password` FROM `users_secure` WHERE `id` = ?", [$iter['id']]);
                                $pwd_expires = date("Y-m-d", strtotime($userSecure['last_update_password'] . "+" . $GLOBALS['password_expiration_days'] . " days"));
                                $grace_time = date("Y-m-d", strtotime($pwd_expires . "+" . $GLOBALS['password_grace_time'] . " days"));
                            }

                            print "<tr>
                                <td><a href='user_admin.php?id=" . attr_url($iter["id"]) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) .
                                "' class='medium_modal' onclick='top.restoreSession()'>" . text($iter["username"]) . "</a>" . "</td>
                                <td>" . text($iter["fname"]) . ' ' . text($iter["lname"]) . "&nbsp;</td>
                                <td>" . text($iter["info"]) . "&nbsp;</td>
                                <td align='left'><span>" . text($iter["authorized"]) . "</td>
                                <td align='left'><span>" . text($isMfa) . "</td>";
                            if ($checkPassExp) {
                                if (AuthUtils::useActiveDirectory($iter["username"]) || empty($iter["active"])) {
                                    // LDAP bypasses expired password mechanism
                                    echo '<td>';
                                    echo xlt('Not Applicable');
                                } elseif (strtotime($current_date) > strtotime($grace_time)) {
                                    echo '<td class="bg-danger text-light">';
                                    echo xlt('Expired');
                                } elseif (strtotime($current_date) > strtotime($pwd_expires)) {
                                    echo '<td class="bg-warning text-dark">';
                                    echo xlt('Grace Period');
                                } else {
                                    echo '<td>';
                                    echo text(oeFormatShortDate($pwd_expires));
                                }
                                echo '</td>';
                            }
                            if (empty($iter["active"])) {
                                echo '<td>';
                                echo xlt('Not Applicable');
                            } else {
                                echo '<td id="login-counter-' . attr($iter["username"]) .  '">';
                                $queryCounter = privQuery("SELECT `login_fail_counter`, `last_login_fail`, TIMESTAMPDIFF(SECOND, `last_login_fail`, NOW()) as `seconds_last_login_fail` FROM `users_secure` WHERE BINARY `username` = ?", [$iter["username"]]);
                                if (!empty($queryCounter['login_fail_counter'])) {
                                    echo text($queryCounter['login_fail_counter']);
                                    if (!empty($queryCounter['last_login_fail'])) {
                                        echo ' (' . xlt('last on') . ' ' . text(oeFormatDateTime($queryCounter['last_login_fail'])) . ')';
                                    }
                                    echo ' ' . '<button type="button" class="btn btn-sm btn-danger ml-1" onclick="resetCounter(' . attr_js($iter["username"]) . ')">' . xlt("Reset Counter") . '</button>';
                                    $autoBlocked = false;
                                    $autoBlockEnd = null;
                                    if ((int)$GLOBALS['password_max_failed_logins'] != 0 && ($queryCounter['login_fail_counter'] > (int)$GLOBALS['password_max_failed_logins'])) {
                                        if ((int)$GLOBALS['time_reset_password_max_failed_logins'] != 0) {
                                            if ($queryCounter['seconds_last_login_fail'] < (int)$GLOBALS['time_reset_password_max_failed_logins']) {
                                                $autoBlocked = true;
                                                $autoBlockEnd = date('Y-m-d H:i:s', (time() + ((int)$GLOBALS['time_reset_password_max_failed_logins'] - $queryCounter['seconds_last_login_fail'])));
                                            }
                                        } else {
                                            $autoBlocked = true;
                                        }
                                    }
                                    if ($autoBlocked) {
                                        echo '<br>' . xlt("Currently Autoblocked");
                                        if (!empty($autoBlockEnd)) {
                                            echo ' (' . xlt("Autoblock ends on") . ' ' . text(oeFormatDateTime($autoBlockEnd)) . ')';
                                        }
                                    }
                                } else {
                                    echo '0';
                                }
                            }
                            echo '</td>';
                            print "</tr>\n";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
            if (empty($GLOBALS['disable_non_default_groups'])) {
                $res = sqlStatement("select * from `groups` order by name");
                for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                    $result5[$iter] = $row;
                }

                foreach ($result5 as $iter) {
                    $grouplist[$iter["name"]] .= text($iter["user"]) .
                        "(<a class='link_submit' href='usergroup_admin.php?mode=delete_group&id=" .
                        attr_url($iter["id"]) . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='top.restoreSession()'>" . xlt('Remove') . "</a>), ";
                }

                foreach ($grouplist as $groupname => $list) {
                    print "<span class='bold'>" . text($groupname) . "</span><br />\n<span>" .
                        substr($list, 0, strlen($list) - 2) . "</span><br />\n";
                }
            }
            ?>
        </div>
    </div>
</div>
<script>
<?php
if ($alertmsg = trim($alertmsg)) {
    echo "alert(" . js_escape($alertmsg) . ");\n";
}
?>
</script>
</body>
</html>
