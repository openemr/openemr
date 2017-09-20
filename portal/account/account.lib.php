<?php
session_start();
if ($_SESSION['patient_portal_onsite_two'] && $_SESSION['pid']) {
    $ignoreAuth_onsite_portal_two = true;
}

require_once("../../interface/globals.php");
include_once("$srcdir/patient.inc");
require_once(dirname(__FILE__) . "/../lib/portal_mail.inc");
require_once("$srcdir/pnotes.inc");

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action == 'set_lang') {
    $_SESSION['language_choice'] = (int) $_REQUEST['value'];
    echo 'okay';
    exit();
}

if ($action == 'get_newpid') {
    $rtn = is_new($_REQUEST['dob'], $_REQUEST['last'], $_REQUEST['first']);
    if ((int) $rtn != 0) {
        echo xlt("This account already exists.") . "\r\n\r\n" . xlt("If you are having troubles logging into your account.") . "\r\n" . xlt("Please contact your provider.") . "\r\n" .
             xla("Reference this Account Id: ") . $rtn;
        exit();
    }
    $rtn = attr(get_newPid());
    echo "$rtn";

    exit();
}

if ($action == 'is_new') {
    $rtn = is_new($_REQUEST['dob'], $_REQUEST['last'], $_REQUEST['first']);
    echo "$rtn";

    exit();
}

if ($action == 'do_signup') {
    $rtn = do_credentials($_REQUEST['pid']);
    echo "$rtn";

    exit();
}

if ($action == 'new_insurance') {
    $pid = $_REQUEST['pid'];
    save_insurance($pid);

    exit();
}

if ($action == 'notify_admin') {
    $pid = $_REQUEST['pid'];
    $provider = $_REQUEST['provider'];
    $rtn = notify_admin($pid, $provider);
    echo "$rtn";

    exit();
}

if ($action == 'cleanup') {
    unset($_SESSION['patient_portal_onsite_two']);
    unset($_SESSION['authUser']);
    unset($_SESSION['pid']);
    unset($_SESSION['site_id']);
    unset($_SESSION['register']);
    echo 'gone';
    session_destroy(); // I know, makes little sense.
}

exit();

/* Library functions */

function notify_admin($pid, $provider)
{

    $note = xlt("New patient registration received from patient portal. Reminder to check for possible new appointment");
    $title = xlt("New Patient");
    $user = sqlQueryNoLog("SELECT users.username FROM users WHERE authorized = 1 And id = ?", array($provider));

    $rtn = addPnote($pid, $note, 1, 1, $title, $user['username'], '', 'New');

    return $rtn;
}

function is_new($dob, $lname = '', $fname = '')
{
    $last = '%' . trim($lname) . '%';
    $first = '%' . trim($fname) . '%';
    $dob = '%' . trim($dob) . '%';

    $row = sqlQuery(
        "select pid from patient_data Where patient_data.lname LIKE ? And patient_data.fname LIKE ? And patient_data.DOB LIKE ? order by date limit 0,1",
        array(
            $last,
            $first,
            $dob
        )
    );
    return $row['pid'] ? $row['pid'] : 0;
}

function save_insurance($pid)
{
    newInsuranceData(
        $pid = $pid,
        $type = "primary",
        $provider = "0",
        $policy_number = $_REQUEST['policy_number'],
        $group_number = $_REQUEST['group_number'],
        $plan_name = $_REQUEST['provider'] . ' ' . $_REQUEST['plan_name'],
        $subscriber_lname = "",
        $subscriber_mname = "",
        $subscriber_fname = "",
        $subscriber_relationship = "",
        $subscriber_ss = "",
        $subscriber_DOB = "",
        $subscriber_street = "",
        $subscriber_postal_code = "",
        $subscriber_city = "",
        $subscriber_state = "",
        $subscriber_country = "",
        $subscriber_phone = "",
        $subscriber_employer = "",
        $subscriber_employer_street = "",
        $subscriber_employer_city = "",
        $subscriber_employer_postal_code = "",
        $subscriber_employer_state = "",
        $subscriber_employer_country = "",
        $copay = $_REQUEST['copay'],
        $subscriber_sex = "",
        $effective_date = $_REQUEST['date'],
        $accept_assignment = "TRUE",
        $policy_type = ""
    );
    newInsuranceData($pid, "secondary");
    newInsuranceData($pid, "tertiary");
}

function get_newPid()
{
    $result = sqlQuery("select max(pid)+1 as pid from patient_data");
    $newpid = 1;
    if ($result['pid'] > 1) {
        $newpid = $result['pid'];
    }
    if ($newpid == null) {
        $newpid = 0;
    }
    return $newpid;
}

function generatePassword($length = 8, $strength = 1)
{
    $consonants = 'bdghjmnpqrstvzacefiklowxy';
    $numbers = '0234561789';
    $specials = '@#$%';

    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length / 3; $i ++) {
        if ($alt == 1) {
            $password .= $consonants[(rand() % strlen($consonants))] . $numbers[(rand() % strlen($numbers))] . $specials[(rand() % strlen($specials))];
            $alt = 0;
        } else {
            $password .= $numbers[(rand() % strlen($numbers))] . $specials[(rand() % strlen($specials))] . $consonants[(rand() % strlen($consonants))];
            $alt = 1;
        }
    }

    return $password;
}

function validEmail($email)
{
    if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $email)) {
        return true;
    }

    return false;
}

function messageCreate($uname, $pass)
{
    $message = htmlspecialchars(xl("Patient Portal Web Address"), ENT_NOQUOTES) . ":<br>";

    if ($GLOBALS['portal_onsite_enable']) {
        $message .= "<a href='" . htmlspecialchars($GLOBALS['portal_onsite_address'], ENT_QUOTES) . "'>" .
            htmlspecialchars($GLOBALS['portal_onsite_address'], ENT_NOQUOTES) . "</a><br>";
    }

    if ($GLOBALS['portal_onsite_two_enable']) {
        $message .= "<a href='" . htmlspecialchars($GLOBALS['portal_onsite_two_address'], ENT_QUOTES) . "'>" .
            htmlspecialchars($GLOBALS['portal_onsite_two_address'], ENT_NOQUOTES) . "</a><br>";
    }

    $message .= "<br>";

    $message .= htmlspecialchars(xl("User Name"), ENT_NOQUOTES) . ": " . htmlspecialchars($uname, ENT_NOQUOTES) .
    "<br><br>" . htmlspecialchars(xl("Password"), ENT_NOQUOTES) . ": " . htmlspecialchars($pass, ENT_NOQUOTES) . "<br><br>";

    return $message;
}

function do_credentials($pid)
{
    global $srcdir;
    require_once("$srcdir/authentication/common_operations.php");

    $newpd = sqlQuery("SELECT * FROM `patient_data` WHERE `pid`=?", array(
        $pid
    ));

    $clear_pass = generatePassword();

    $uname = $newpd['fname'] . $newpd['id'];

    $res = sqlStatement("SELECT * FROM patient_access_onsite WHERE pid=?", array(
        $pid
    ));
    $query_parameters = array(
        $uname
    );
    $salt_clause = "";
    // For onsite portal create a blowfish based hash and salt.
    $new_salt = oemr_password_salt();
    $salt_clause = ",portal_salt=? ";
    array_push($query_parameters, oemr_password_hash($clear_pass, $new_salt), $new_salt);
    array_push($query_parameters, $pid);
    if (sqlNumRows($res)) {
        sqlStatement("UPDATE patient_access_onsite SET portal_username=?,portal_pwd=?,portal_pwd_status=0 " . $salt_clause . " WHERE pid=?", $query_parameters);
    } else {
        sqlStatement("INSERT INTO patient_access_onsite SET portal_username=?,portal_pwd=?,portal_pwd_status=0" . $salt_clause . " ,pid=?", $query_parameters);
    }

    if (! (validEmail($newpd['email']))) {
        $sent = false;
    }

    $message = messageCreate($uname, $clear_pass);

    $mail = new MyMailer();
    $pt_name = $newpd['fname'] . ' ' . $newpd['lname'];
    $pt_email = $newpd['email'];
    $email_subject = xl('Access Your Patient Portal');
    $email_sender = $GLOBALS['patient_reminder_sender_email'];
    $mail->AddReplyTo($email_sender, $email_sender);
    $mail->SetFrom($email_sender, $email_sender);
    $mail->AddAddress($pt_email, $pt_name);
    $mail->Subject = $email_subject;
    $mail->MsgHTML("<html><body><div class='wrapper'>" . $message . "</div></body></html>");
    $mail->IsHTML(true);
    $mail->AltBody = $message;

    if ($mail->Send()) {
        $sent = true;
    } else {
        $email_status = $mail->ErrorInfo;
        error_log("EMAIL ERROR: " . $email_status, 0);
        $sent = false;
    }
    if ($sent) {
        $sent = "User : " . $uname . " Password : " . $clear_pass;
    }
    return $sent;
}
