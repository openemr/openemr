<?php

/**
 * create_portallogin.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Paul Simon <paul@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\Core\Header;

$trustedEmail = sqlQueryNoLog("SELECT email_direct, email FROM `patient_data` WHERE `pid`=?", array($pid));
$row = sqlQuery("SELECT pd.*,pao.portal_username, pao.portal_login_username,pao.portal_pwd,pao.portal_pwd_status FROM patient_data AS pd LEFT OUTER JOIN patient_access_onsite AS pao ON pd.pid=pao.pid WHERE pd.pid=?", array($pid));

$trustedEmail['email_direct'] = !empty(trim($trustedEmail['email_direct'])) ? text(trim($trustedEmail['email_direct'])) : text(trim($trustedEmail['email']));
$trustedUserName = $trustedEmail['email_direct'];
// check for duplicate username
$dup_check = sqlQueryNoLog("SELECT * FROM patient_access_onsite WHERE pid != ? AND portal_login_username = ?", array($pid, $trustedUserName));
// make unique if needed
if (!empty($dup_check)) {
    if (strpos($trustedUserName, '@')) {
        $trustedUserName = str_replace("@", "$pid@", $trustedUserName);
    } else {
        // account name will be used and is unique
        $trustedUserName = '';
    }
}

function validEmail($email)
{
    if (preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i", $email)) {
        return true;
    }

    return false;
}

function messageCreate($uname, $luname, $pass)
{
    global $trustedEmail;

    $message = xlt("Patient Portal Web Address") . ":<br />";
    if ($GLOBALS['portal_onsite_two_enable']) {
        $message .= "<a href='" . attr($GLOBALS['portal_onsite_two_address']) . "' target='_blank'>" .
            text($GLOBALS['portal_onsite_two_address']) . "</a><br />";
    }
    $message .= "<br />";
    $sub = '';
    if ($GLOBALS['enforce_signin_email']) {
        $sub = xlt("Login Trusted Email") . ":" .
            (!empty(trim($trustedEmail['email_direct'])) ? text(trim($trustedEmail['email_direct'])) : xlt("Is Required. Contact Provider."));
        $sub .= "<br /><br />";
    }
    $message .= xlt("Portal Account Name") . ": " . text($uname) . "<br /><br /><strong>" .
        xlt("Login User Name") . ":</strong> " . text($luname) . "<br /><strong>" .
        xlt("Password") . ":</strong> " .
        text($pass) . "<br /><br />" . $sub;
    return $message;
}

function emailLogin($patient_id, $message)
{
    $patientData = sqlQuery("SELECT * FROM `patient_data` WHERE `pid`=?", array($patient_id));
    if ($patientData['hipaa_allowemail'] != "YES" || empty($patientData['email']) || empty($GLOBALS['patient_reminder_sender_email'])) {
        return false;
    }

    if (!(validEmail($patientData['email']))) {
        return false;
    }

    if (!(validEmail($GLOBALS['patient_reminder_sender_email']))) {
        return false;
    }
    $message .= "<strong>" . xlt("You may be required to change your password during first login.") . "</strong><br />";
    $message .= xlt("This is required for your security as well as ours.") . "<br />";
    $message .= xlt("Afterwards however, you may change your portal credentials anytime from portal menu.") . ":<br /><br />";
    $message .= xlt("Thank you for allowing us to serve you.") . ":<br />";

    $mail = new MyMailer();
    $pt_name = $patientData['fname'] . ' ' . $patientData['lname'];
    $pt_email = $patientData['email'];
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
        return true;
    } else {
        $email_status = $mail->ErrorInfo;
        error_log("EMAIL ERROR: " . errorLogEscape($email_status), 0);
        return false;
    }
}

function displayLogin($patient_id, $message, $emailFlag)
{
    $patientData = sqlQuery("SELECT * FROM `patient_data` WHERE `pid`=?", array($patient_id));
    if ($emailFlag) {
        $message = "<br /><br />" .
            xlt("Email was sent to following address") . ": " .
            text($patientData['email']) . "<br /><br />" .
            $message;
    }

    return $message;
}

if (isset($_POST['form_save']) && $_POST['form_save'] == 'submit') {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $clear_pass = $_POST['pwd'];

    $res = sqlStatement("SELECT * FROM patient_access_onsite WHERE pid=?", array($pid));
    $query_parameters = array($_POST['uname'],$_POST['login_uname']);
    $hash = (new AuthHash('auth'))->passwordHash($clear_pass);
    if (empty($hash)) {
        // Something is seriously wrong
        error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
        die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
    }
    array_push($query_parameters, $hash);

    array_push($query_parameters, $pid);
    if (sqlNumRows($res)) {
        sqlStatementNoLog("UPDATE patient_access_onsite SET portal_username=?,portal_login_username=?,portal_pwd=?,portal_pwd_status=0 WHERE pid=?", $query_parameters);
    } else {
        sqlStatementNoLog("INSERT INTO patient_access_onsite SET portal_username=?,portal_login_username=?,portal_pwd=?,portal_pwd_status=0,pid=?", $query_parameters);
    }

    // Create the message
    $message = messageCreate($_POST['uname'], $_POST['login_uname'], $clear_pass);
    // Email and display/print the message
    if (emailLogin($pid, $message)) {
        // email was sent
        $credMessage = displayLogin($pid, $message, true);
    } else {
        // email wasn't sent
        $credMessage = displayLogin($pid, $message, false);
    }
} ?>
<html>
<head>

<?php Header::setupHeader('opener'); ?>
<style>
    @media print {
        body {
            font-size: 24pt !important;
        }
        .alert {
            border: 0 !important;
        }
        .alert-success {
            color: #000 !important;
            background-color: #fff !important;
        }
    }
</style>
<script>
function transmit(){
    // get a public key to encrypt the password info and send
    document.getElementById('form_save').value='submit';
    document.forms[0].submit();
}
<?php if (!empty($credMessage)) { ?>
    $(function () {
        top.printLogPrint(window);
    });
<?php } ?>
</script>
</head>
<body class="body_top">
    <div class="container-fluid">

        <?php if (!empty($credMessage)) { ?>
        <div class="alert alert-success" role="alert">
            <p class="font-weight-bold"><?php echo xlt("Portal Credential Information"); ?></p>
            <?php echo $credMessage; ?>
        </div>
        <?php } else { ?>
        <form name="portallogin" action="" method="post">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

            <p class="text-center font-weight-bold"><?php echo text(xl("Generate Username And Password For") . " " . $row['fname']);?></p>

            <div class="form-group">
                <label class="font-weight-bold" for="uname"><?php echo text(xl('Account Name') . ':');?></label>
                <input type="text" class="form-control" name="uname" id="uname" value="<?php echo ($row['portal_username']) ? attr($row['portal_username']) : attr($row['fname'] . $row['id']); ?>" size="10" readonly />
            </div>
            <div class="form-group">
                <label class="font-weight-bold" for="login_uname"><?php echo text(xl('Login User Name') . ':');?></label>
                <input type="text" class="form-control" name="login_uname" id="login_uname" value="<?php echo (!empty($trustedUserName) ? text($trustedUserName) : attr($row['portal_username'])); ?>" readonly />
            </div>
            <label class="font-weight-bold" for="pwd"><?php echo text(xl('Password') . ':');?></label>
            <div class="input-group">
                <?php $pwd = RandomGenUtils::generatePortalPassword(); ?>
                <input type="text" class="form-control" name="pwd" id="pwd" value="<?php echo attr($pwd); ?>" size="14" />
                <div class="input-group-append">
                    <a href="#" class="btn btn-primary" onclick="top.restoreSession(); javascript:document.location.reload()"><?php echo xlt('Change'); ?></a>
                </div>
            </div>
            <?php if ($GLOBALS['enforce_signin_email']) { ?>
            <div class="form-group">
                <label class="font-weight-bold" for="email_direct"><?php echo xlt("Login Trusted Email") . ":" ?></label>
                <?php echo (!empty(trim($trustedEmail['email_direct'])) ? text($trustedEmail['email_direct']) : xlt("Is Required. Please Add in Contacts.")) ?>
            </div>
            <?php } ?>
            <hr />
            <input type="hidden" name="form_save" id="form_save" />
            <a href="#" class="btn btn-primary" onclick="return transmit()"><?php echo xlt('Save');?></a>
            <input type="hidden" name="form_cancel" id="form_cancel" />
            <a href="#" class="btn btn-secondary" onclick="top.restoreSession(); dlgclose();"><?php echo xlt('Cancel');?></a>
        </form>
        <?php } ?>
    </div>
</body>
</html>
