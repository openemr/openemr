<?php

/**
 * This file contains functions that service ajax requests for
 * ACL(php-gacl) administration within OpenEMR. All returns are
 * done via xml.
 *
 *  Important - Ensure that display_errors=Off in php.ini settings.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2007-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");
require_once("$srcdir/user.inc.php");

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Services\UserService;


header("Content-type: text/xml");
header("Cache-Control: no-cache");

//initiate error array
$error = array();

//verify csrf
if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    echo error_xml(xl('Authentication Error'));
    CsrfUtils::csrfNotVerified(false);
}

//ensure user has proper access
if (!AclMain::aclCheckCore('admin', 'acl')) {
    echo error_xml(xl('ACL Administration Not Authorized'));
    CsrfUtils::csrfNotVerified(false);
}

//Display red alert if Emergency Login ACL is activated for a user.
if ($_POST["action"] == "add") {
    if (!empty($_POST["selection"]) && is_array($_POST["selection"]) && in_array("Emergency Login", $_POST["selection"])) {
        array_push($error, (xl('Emergency Login ACL is chosen. The user is still in active state, please de-activate the user and activate the same when required during emergency situations. Visit Administration->Users for activation or de-activation.') ));
    }
}

//PROCESS USERNAME REQUESTS
if ($_POST["control"] == "username") {
    if ($_POST["action"] == "list") {
        //return username list with alert if user is not joined to group
        echo username_listings_xml($error);
    }
}


//PROCESS MEMBERSHIP REQUESTS
if ($_POST["control"] == "membership") {
    if ($_POST["action"] == "list") {
        //return membership data
        echo user_group_listings_xml($_POST["name"], $error);
    }

    if ($_POST["action"] == "add") {
        if ($_POST["selection"][0] == "null") {
            //no selection, return soft error, and just return membership data
            array_push($error, (xl('No group was selected') . "!"));
            echo user_group_listings_xml($_POST["name"], $error);
            exit;
        }

        //add the group, then log it, then return updated membership data
        AclExtended::addUserAros($_POST["name"], $_POST["selection"]);
        EventAuditLogger::instance()->newEvent("security-administration-update", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "Added " . $_POST["name"] . " to following access group(s): " . implode(', ', $_POST["selection"]));
        echo user_group_listings_xml($_POST["name"], $error);
    }

    if ($_POST["action"] == "remove") {
        if ($_POST["selection"][0] == "null") {
            //no selection, return soft error, and just return membership data
            array_push($error, (xl('No group was selected') . "!"));
            echo user_group_listings_xml($_POST["name"], $error);
            exit;
        }

        // check if user is protected. If so, then state message unable to remove from admin group.
        $userNametoID = (new UserService())->getIdByUsername($_POST["name"]);
        if (checkUserSetting("gacl_protect", "1", $userNametoID) || ($_POST["name"] == "admin")) {
             $gacl_protect = true;
        } else {
             $gacl_protect = false;
        }

        if ($gacl_protect && in_array("Administrators", $_POST["selection"])) {
            //unable to remove admin user from administrators group, process remove,
            // send soft error, then return data
            array_push($error, (xl('Not allowed to remove this user from the Administrators group') . "!"));
            AclExtended::removeUserAros($_POST["name"], $_POST["selection"]);
            echo user_group_listings_xml($_POST["name"], $error);
            exit;
        }

        //remove the group(s), then log it, then return updated membership data
        AclExtended::removeUserAros($_POST["name"], $_POST["selection"]);
        EventAuditLogger::instance()->newEvent("security-administration-update", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "Removed " . $_POST["name"] . " from following access group(s): " . implode(', ', $_POST["selection"]));
        echo user_group_listings_xml($_POST["name"], $error);
    }
}


//PROCESS ACL REQUESTS
if ($_POST["control"] == "acl") {
    if ($_POST["action"] == "list") {
        //return acl titles with return values
        echo AclExtended::aclListingsXml($error);
    }

    if ($_POST["action"] == "add") {
        //validate form data
        $form_error = false;
        if (empty($_POST["title"])) {
            $form_error = true;
            array_push($error, ("title_" . xl('Need to enter title') . "!"));
        } elseif (!ctype_alpha(str_replace(' ', '', $_POST["title"]))) {
            $form_error = true;
            array_push($error, ("title_" . xl('Please only use alphabetic characters') . "!"));
        } elseif (AclExtended::aclExist($_POST["title"], false, $_POST["return_value"])) {
            $form_error = true;
            array_push($error, ("title_" . xl('Already used, choose another title') . "!"));
        }

        if (empty($_POST["identifier"])) {
            $form_error = true;
            array_push($error, ("identifier_" . xl('Need to enter identifier') . "!"));
        } elseif (!ctype_alpha($_POST["identifier"])) {
            $form_error = true;
            array_push($error, ("identifier_" . xl('Please only use alphabetic characters with no spaces') . "!"));
        } elseif (AclExtended::aclExist(false, $_POST["identifier"], $_POST["return_value"])) {
            $form_error = true;
            array_push($error, ("identifier_" . xl('Already used, choose another identifier') . "!"));
        }

        if (empty($_POST["return_value"])) {
            $form_error = true;
            array_push($error, ("return_" . xl('Need to enter a Return Value') . "!"));
        }

        if (empty($_POST["description"])) {
            $form_error = true;
            array_push($error, ("description_" . xl('Need to enter a description') . "!"));
        } elseif (!ctype_alpha(str_replace(' ', '', $_POST["description"]))) {
            $form_error = true;
            array_push($error, ("description_" . xl('Please only use alphabetic characters') . "!"));
        }

        //process if data is valid
        if (!$form_error) {
            AclExtended::aclAdd($_POST["title"], $_POST["identifier"], $_POST["return_value"], $_POST["description"]);
            echo "<?xml version=\"1.0\"?>\n" .
             "<response>\n" .
             "\t<success>SUCCESS</success>\n" .
             "</response>\n";
        } else { //$form_error = true, so return errors
            echo error_xml($error);
        }
    }

    if ($_POST["action"] == "remove") {
        //validate form data
        $form_error = false;
        if (empty($_POST["title"])) {
            $form_error = true;
            array_push($error, ("aclTitle_" . xl('Need to enter title') . "!"));
        }

        if ($_POST["title"] == "Administrators") {
            $form_error = true;
            array_push($error, ("aclTitle_" . xl('Not allowed to delete the Administrators group') . "!"));
        }

        //process if data is valid
        if (!$form_error) {
            AclExtended::aclRemove($_POST["title"], $_POST["return_value"]);
            echo "<?xml version=\"1.0\"?>\n" .
             "<response>\n" .
             "\t<success>SUCCESS</success>\n" .
             "</response>\n";
        } else { //$form_error = true, so return errors
            echo error_xml($error);
        }
    }

    if ($_POST["action"] == "returns") {
        //simply return all the possible acl return_values
        echo AclExtended::returnValuesXml($error);
    }
}


//PROCESS ACO REQUESTS
if ($_POST["control"] == "aco") {
    if ($_POST["action"] == "list") {
        //send acl data
        echo AclExtended::acoListingsXml($_POST["name"], $_POST["return_value"], $error);
    }

    if ($_POST["action"] == "add") {
        if ($_POST["selection"][0] == "null") {
            //no selection, return soft error, and just return data
            array_push($error, (xl('Nothing was selected') . "!"));
            echo AclExtended::acoListingsXml($_POST["name"], $_POST["return_value"], $error);
            exit;
        }

        //add the aco, then return updated membership data
        AclExtended::aclAddAcos($_POST["name"], $_POST["return_value"], $_POST["selection"]);
        echo AclExtended::acoListingsXml($_POST["name"], $_POST["return_value"], $error);
    }

    if ($_POST["action"] == "remove") {
        if ($_POST["selection"][0] == "null") {
            //no selection, return soft error, and just return data
            array_push($error, (xl('Nothing was selected') . "!"));
            echo AclExtended::acoListingsXml($_POST["name"], $_POST["return_value"], $error);
            exit;
        }

        if ($_POST["name"] == "Administrators") {
            //will not allow removal of acos from Administrators ACL
            array_push($error, (xl('Not allowed to inactivate anything from the Administrators ACL') . "!"));
            echo AclExtended::acoListingsXml($_POST["name"], $_POST["return_value"], $error);
            exit;
        }

        //remove the acos, then return updated data
        AclExtended::aclRemoveAcos($_POST["name"], $_POST["return_value"], $_POST["selection"]);
        echo AclExtended::acoListingsXml($_POST["name"], $_POST["return_value"], $error);
    }
}


//
// Returns username listings via xml message.
// It will also include alert if user is not joined
// to a group yet
//   $err = error strings (array)
//
function username_listings_xml($err)
{
    $message = "<?xml version=\"1.0\"?>\n" .
    "<response>\n";
    $res = sqlStatement("select * from users where username != '' and active = 1 order by username");
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $result4[$iter] = $row;
    }

    foreach ($result4 as $iter) {
        // Skip this user if logged-in user does not have all of its permissions.
        // Note that a superuser now has all permissions.
        if (!AclExtended::iHavePermissionsOf($iter['username'])) {
            continue;
        }

        $message .= "\t<user>\n" .
          "\t\t<username>" . $iter["username"] . "</username>\n";
        $username_acl_groups = AclExtended::aclGetGroupTitles($iter["username"]);
        if (!$username_acl_groups) {
            //not joined to any group, so send alert
            $message .= "\t\t<alert>no membership</alert>\n";
        }

        $message .= "\t</user>\n";
    }

    if (isset($err)) {
        foreach ($err as $value) {
            $message .= "\t<error>" . $value . "</error>\n";
        }
    }

    $message .= "</response>\n";
    return $message;
}

//
// Returns user group listings(active and inactive lists)
// via xml message.
//   $username = username
//   $err = error strings (array)
//
function user_group_listings_xml($username, $err)
{
    $list_acl_groups = AclExtended::aclGetGroupTitleList();
    $username_acl_groups = AclExtended::aclGetGroupTitles($username);
    //note aclGetGroupTitles() returns a 0 if user in no groups

    $message = "<?xml version=\"1.0\"?>\n" .
    "<response>\n" .
    "\t<inactive>\n";
    foreach ($list_acl_groups as $value) {
        if ((!$username_acl_groups) || (!(in_array($value, $username_acl_groups)))) {
            $message .= "\t\t<group>\n";
            $message .= "\t\t\t<value>" . $value . "</value>\n";

            // Modified 6-2009 by BM - Translate gacl group name if applicable
            $message .= "\t\t\t<label>" . xl_gacl_group($value) . "</label>\n";

            $message .= "\t\t</group>\n";
        }
    }

    $message .= "\t</inactive>\n" .
    "\t<active>\n";
    if ($username_acl_groups) {
        foreach ($username_acl_groups as $value) {
            $message .= "\t\t<group>\n";
            $message .= "\t\t\t<value>" . $value . "</value>\n";

            // Modified 6-2009 by BM - Translate gacl group name if applicable
            $message .= "\t\t\t<label>" . xl_gacl_group($value) . "</label>\n";

            $message .= "\t\t</group>\n";
        }
    }

    $message .= "\t</active>\n";
    if (isset($err)) {
        foreach ($err as $value) {
            $message .= "\t<error>" . $value . "</error>\n";
        }
    }

    $message .= "</response>\n";
    return $message;
}

//
// Returns error string(s) via xml
//   $err = error (string or array)
//
function error_xml($err)
{
    $message = "<?xml version=\"1.0\"?>\n" .
    "<response>\n";
    if (is_array($err)) {
        foreach ($err as $value) {
            $message .= "\t<error>" . $value . "</error>\n";
        }
    } else {
        $message .= "\t<error>" . $err . "</error>\n";
    }

    $message .= "</response>\n";
    return $message;
}
