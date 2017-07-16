<?php
/**
 * Authorization functions.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @author  ViCarePlus <visolve_emr@visolve.com>
 * @author  cfapress
 * @link    http://www.open-emr.org
 */

//----------THINGS WE ALWAYS DO

require_once("{$GLOBALS['srcdir']}/log.inc");
// added for the phpGACL group check -- JRM
require_once("{$GLOBALS['srcdir']}/acl.inc");
require_once("$srcdir/authentication/login_operations.php");

$incoming_site_id = '';

if (isset($_GET['auth']) && ($_GET['auth'] == "login") && isset($_POST['authUser']) &&
    isset($_POST['clearPass']) && isset($_POST['authProvider'])) {
    $clearPass=$_POST['clearPass'];
    // set the language
    if (!empty($_POST['languageChoice'])) {
        $_SESSION['language_choice'] = $_POST['languageChoice'];
    } else {
        $_SESSION['language_choice'] = 1;
    }

    // set language direction according to language choice. Later in globals.php we'll override main theme name if needed.
    $_SESSION['language_direction'] = getLanguageDir($_SESSION['language_choice']);

    if (!validate_user_password($_POST['authUser'], $clearPass, $_POST['authProvider']) ||  !verify_user_gacl_group($_POST['authUser'])) {
        $_SESSION['loginfailure'] = 1;
        authLoginScreen();
    }

//If password expiration option is enabled call authCheckExpired() to check whether login user password is expired or not

    if ($GLOBALS['password_expiration_days'] != 0) {
        if (authCheckExpired($_POST['authUser'])) {
            authLoginScreen();
        }
    }

    $ip=$_SERVER['REMOTE_ADDR'];
    $_SESSION['loginfailure'] = null;
    unset($_SESSION['loginfailure']);
    //store the very first initial timestamp for timeout errors
    $_SESSION["last_update"] = time();
} else if ((isset($_GET['auth'])) && ($_GET['auth'] == "logout")) {
    newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "success");
    authCloseSession();
    authLoginScreen(true);
} else {
    if (authCheckSession()) {
        if (isset($_SESSION['pid']) && empty($GLOBALS['DAEMON_FLAG'])) {
            require_once("{$GLOBALS['srcdir']}/patient.inc");
            /**
            $logpatient = getPatientData($_SESSION['pid'], "lname, fname, mname");
            newEvent("view", $_SESSION['authUser'], $_SESSION['authProvider'],
                "{$logpatient['lname']}, {$logpatient['fname']} {$logpatient['mname']} :: encounter " .
                $_SESSION['encounter']);
            **/
        }

        //LOG EVERYTHING
        //newEvent("view", $_SESSION['authUser'], $_SESSION['authProvider'], $_SERVER['REQUEST_URI']);
    } else {
        newEvent("login", $_POST['authUser'], $_POST['authProvider'], 0, "insufficient data sent");
        authLoginScreen();
    }
}

if (!isset($_SESSION["last_update"])) {
    authLoginScreen();
} else {
     //if page has not been updated in a given period of time, we call login screen
     //--Note can't perform nice logout if skip_timeout_reset is set since these are called
     //via ajax scripts where this output is not getting sent to browser.
     //--Note DAEMON_FLAG is ok because it is run from a frame in the browser.
    if (((time() - $_SESSION["last_update"]) > $timeout) && empty($_REQUEST['skip_timeout_reset'])) {
        newEvent("logout", $_SESSION['authUser'], $_SESSION['authProvider'], 0, "timeout");
        authCloseSession();
        authLoginScreen(true);
    } else {
        // Have a mechanism to skip the timeout reset mechanism if a skip_timeout_reset parameter exists. This
        //  can be used by scripts that continually request information from the server; for example the Messages
        //  and Reminders automated intermittent requests that happen in the Messages Center script and in
        //  the left navigation menu script.
        if (empty($GLOBALS['DAEMON_FLAG']) && empty($_REQUEST['skip_timeout_reset'])) {
            $_SESSION["last_update"] = time();
        }
    }
}



//----------THINGS WE DO IF WE STILL LIKE YOU

function authCheckSession()
{
    if (isset($_SESSION['authId'])) {
         // If active directory was used, check a different session variable (as there is no password in database).
        if ($GLOBALS['use_active_directory']) {
            if ($_SESSION['active_directory_auth']) {
                return true;
            } else {
                return false;
            }
        }

        $authDB = privQuery("select ".implode(",", array(TBL_USERS.".".COL_ID,
                                                        TBL_USERS.".".COL_UNM,
                                                        TBL_USERS_SECURE.".".COL_PWD,
                                                        TBL_USERS_SECURE.".".COL_ID))
                . " FROM ". implode(",", array(TBL_USERS,TBL_USERS_SECURE))
                . " WHERE ". TBL_USERS.".".COL_ID." = ? "
                . " AND ". TBL_USERS.".".COL_UNM . "=" . TBL_USERS_SECURE.".".COL_UNM
                . " AND ". TBL_USERS.".".COL_ACTIVE . "=1", array($_SESSION['authId']));
        if ($_SESSION['authUser'] == $authDB['username']
            && $_SESSION['authPass'] == $authDB['password'] ) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}

function authCloseSession()
{
  // Before destroying the session, save its site_id so that the next
  // login will default to that same site.
    global $incoming_site_id;
    $incoming_site_id = $_SESSION['site_id'];
    ob_start();
    session_unset();
    session_destroy();
    unset($_COOKIE[session_name()]);
}

function authLoginScreen($timed_out = false)
{
  // See comment in authCloseSession().
    global $incoming_site_id;
?>
<script>
 // Find the top level window for this instance of OpenEMR, set a flag indicating
 // session timeout has occurred, and reload the login page into it.  This is so
 // that beforeunload event handlers will not obstruct the process in this case.
 var w = window;
 while (w.opener) { // in case we are in a dialog window
  var wtmp = w;
  w = w.opener;
  wtmp.close();
 }
<?php if ($timed_out) { ?>
 w.top.timed_out = true;
<?php } ?>
 w.top.location.href = '<?php echo "{$GLOBALS['login_screen']}?error=1&site=$incoming_site_id"; ?>';
</script>
<?php
  exit;
}

// Check if the user's password has expired beyond the grace limit.
// If so, deactivate the user
function authCheckExpired($user)
{
    $result = sqlStatement("select pwd_expiration_date from users where username = ?", array($user));
    if ($row = sqlFetchArray($result)) {
        $pwd_expires = $row['pwd_expiration_date'];
    }

    $current_date = date("Y-m-d");
    if ($pwd_expires != "0000-00-00") {
        $grace_time1 = date("Y-m-d", strtotime($pwd_expires . "+".$GLOBALS['password_grace_time'] ."days"));
    }

    if (($grace_time1 != "") && strtotime($current_date) > strtotime($grace_time1)) {
        sqlStatement("update users set active=0 where username = ?", array($user));
        $_SESSION['loginfailure'] = 1;
        return true;
    }

    return false;
}

function getUserList($cols = '*', $limit = 'all', $start = '0')
{
    if ($limit = "all") {
        $rez = sqlStatement("select $cols from users where username != '' order by date DESC");
    } else {
        $rez = sqlStatement("select $cols from users where username != '' order by date DESC limit $limit, $start");
    }

    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $tbl[$iter] = $row;
    }

    return $tbl;
}

function getProviderList($cols = '*', $limit = 'all', $start = '0')
{
    if ($limit = "all") {
        $rez = sqlStatement("select $cols from groups order by date DESC");
    } else {
        $rez = sqlStatement("select $cols from groups order by date DESC limit $limit, $start");
    }

    for ($iter = 0; $row = sqlFetchArray($rez); $iter++) {
        $tbl[$iter] = $row;
    }

    return $tbl;
}

function addGroup($groupname)
{
    return sqlInsert("insert into groups (name) values (?)", array($groupname));
}

function delGroup($group_id)
{
    return sqlQuery("delete from groups where id = ? limit 0,1", array($group_id));
}

/***************************************************************
//pennfirm
//Function currently user by new post calendar code to determine
//if a given user is in a group with another user
//and if so to allow editing of that users events
//
//*************************************************************/

function validateGroupStatus($user_to_be_checked, $group_user)
{
    if (isset($user_to_be_checked) && isset($group_user)) {
        if ($user_to_be_checked == $group_user) {
            return true;
        } elseif ($_SESSION['authorizeduser'] == 1) {
            return true;
        }

        $query = "SELECT groups.name FROM users,groups WHERE users.username = ? " .
                 "AND users.username = groups.user group by groups.name";
        $result = sqlStatement($query, array($user_to_be_checked));

        $usertbcGroups = array();

        while ($row = sqlFetchArray($result)) {
            $usertbcGroups[] = $row[0];
        }

        $query = "SELECT groups.name FROM users,groups WHERE users.username =  ? " .
                 "AND users.username = groups.user group by groups.name";
        $result = sqlStatement($query, array($group_user));

        $usergGroups = array();

        while ($row = sqlFetchArray($result)) {
            $usergGroups[] = $row[0];
        }

        foreach ($usertbcGroups as $group) {
            if (in_array($group, $usergGroups)) {
                return true;
            }
        }
    }

    return false;
}


// Attempt to update the user's password, password history, and password expiration.
// Verify that the new password does not match the last three passwords used.
// Return true if successfull, false on failure
function UpdatePasswordHistory($userid, $pwd)
{
    $result = sqlStatement("select password, pwd_history1, pwd_history2 from users where id = ?", array($userid));
    if ($row = sqlFetchArray($result)) {
        $previous_pwd1=$row['password'];
        $previous_pwd2=$row['pwd_history1'];
        $previous_pwd3=$row['pwd_history2'];
    }

    if (($pwd != $previous_pwd1) && ($pwd != $previous_pwd2) && ($pwd != $previous_pwd3)) {
        sqlStatement("update users set pwd_history2=?, pwd_history1=?,password=? where id=?", array($previous_pwd2,$previous_pwd1,$pwd,$userid));
        if ($GLOBALS['password_expiration_days'] != 0) {
            $exp_days=$GLOBALS['password_expiration_days'];
            $exp_date = date('Y-m-d', strtotime("+$exp_days days"));
            sqlStatement("update users set pwd_expiration_date=? where id=?", array($exp_date,$userid));
        }

        return true;
    } else {
        return false;
    }
}
?>
