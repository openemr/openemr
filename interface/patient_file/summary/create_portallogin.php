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
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// Collect portalsite parameter (either off for offsite or on for onsite); only allow off or on
$portalsite = isset($_GET['portalsite']) ? $_GET['portalsite'] : $portalsite = "off";
if ($portalsite != "off" && $portalsite != "on") {
    $portalsite = "off";
}

$row = sqlQuery("SELECT pd.*,pao.portal_username,pao.portal_pwd,pao.portal_pwd_status FROM patient_data AS pd LEFT OUTER JOIN patient_access_" . escape_identifier($portalsite, array("on","off"), true) . "site AS pao ON pd.pid=pao.pid WHERE pd.pid=?", array($pid));

function generatePassword($length = 6, $strength = 1)
{
    $consonants = 'bdghjmnpqrstvzacefiklowxy';
    $numbers = '0234561789';
    $specials = '@#$%';


    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length/3; $i++) {
        if ($alt == 1) {
            $password .= $consonants[(rand() % strlen($consonants))].$numbers[(rand() % strlen($numbers))].$specials[(rand() % strlen($specials))];
            $alt = 0;
        } else {
            $password .= $numbers[(rand() % strlen($numbers))].$specials[(rand() % strlen($specials))].$consonants[(rand() % strlen($consonants))];
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

function messageCreate($uname, $pass, $site)
{
    $message = xlt("Patient Portal Web Address") . ":<br>";
    if ($site == "on") {
        if ($GLOBALS['portal_onsite_two_enable']) {
            $message .= "<a href='" . attr($GLOBALS['portal_onsite_two_address']) . "'>" .
                text($GLOBALS['portal_onsite_two_address']) . "</a><br>";
        }

        $message .= "<br>";
    } else { // $site == "off"
        $offsite_portal_patient_link = $GLOBALS['portal_offsite_address_patient_link'] ?  $GLOBALS['portal_offsite_address_patient_link'] : "https://mydocsportal.com";
        $message .= "<a href='" . attr($offsite_portal_patient_link) . "'>" .
            text($offsite_portal_patient_link) . "</a><br><br>";
        $message .= xlt("Provider Id") . ": " .
            text($GLOBALS['portal_offsite_providerid']) . "<br><br>";
    }

        $message .= xlt("User Name") . ": " .
            text($uname) . "<br><br>" .
            xlt("Password") . ": " .
            text($pass) . "<br><br>";
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

    $mail = new MyMailer();
    $pt_name=$patientData['fname'].' '.$patientData['lname'];
    $pt_email=$patientData['email'];
    $email_subject=xl('Access Your Patient Portal');
    $email_sender=$GLOBALS['patient_reminder_sender_email'];
    $mail->AddReplyTo($email_sender, $email_sender);
    $mail->SetFrom($email_sender, $email_sender);
    $mail->AddAddress($pt_email, $pt_name);
    $mail->Subject = $email_subject;
    $mail->MsgHTML("<html><body><div class='wrapper'>".$message."</div></body></html>");
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
        $message = "<br><br>" .
            xlt("Email was sent to following address") . ": " .
            text($patientData['email']) . "<br><br>" .
            $message;
    }

    echo "<html><body onload='top.printLogPrint(window);'>" . $message . "</body></html>";
}

if (isset($_POST['form_save']) && $_POST['form_save']=='SUBMIT') {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    require_once("$srcdir/authentication/common_operations.php");

    $clear_pass=$_POST['pwd'];

    $res = sqlStatement("SELECT * FROM patient_access_" . escape_identifier($portalsite, array("on","off"), true) . "site WHERE pid=?", array($pid));
    $query_parameters=array($_POST['uname']);
    $salt_clause="";
    if ($portalsite=='on') {
        // For onsite portal create a blowfish based hash and salt.
        $new_salt = oemr_password_salt();
        $salt_clause = ",portal_salt=? ";
        array_push($query_parameters, oemr_password_hash($clear_pass, $new_salt), $new_salt);
    } else {
        // For offsite portal still create and SHA1 hashed password
        // When offsite portal is updated to handle blowfish, then both portals can use the same execution path.
        array_push($query_parameters, SHA1($clear_pass));
    }

    array_push($query_parameters, $pid);
    if (sqlNumRows($res)) {
        sqlStatement("UPDATE patient_access_" . escape_identifier($portalsite, array("on","off"), true) . "site SET portal_username=?,portal_pwd=?,portal_pwd_status=0 " . $salt_clause . " WHERE pid=?", $query_parameters);
    } else {
        sqlStatement("INSERT INTO patient_access_" . escape_identifier($portalsite, array("on","off"), true) . "site SET portal_username=?,portal_pwd=?,portal_pwd_status=0" . $salt_clause . " ,pid=?", $query_parameters);
    }

    // Create the message
    $message = messageCreate($_POST['uname'], $clear_pass, $portalsite);
    // Email and display/print the message
    if (emailLogin($pid, $message)) {
        // email was sent
        displayLogin($pid, $message, true);
    } else {
        // email wasn't sent
        displayLogin($pid, $message, false);
    }

    exit;
} ?>
<html>
<head>

<?php Header::setupHeader(['no_bootstrap', 'no_fontawesome', 'no_textformat', 'no_dialog', 'opener']); ?>

<script type="text/javascript">
function transmit(){
    // get a public key to encrypt the password info and send
    document.getElementById('form_save').value='SUBMIT';
    document.forms[0].submit();
}
</script>
</head>
<body class="body_top">
    <form name="portallogin" action="" method="POST">
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

    <table align="center" style="margin-top:10px">
        <tr class="text">
            <th colspan="5" align="center"><?php echo text(xl("Generate Username And Password For")." ".$row['fname']);?></th>
        </tr>
    <?php
    if ($portalsite == 'off') {
        ?>
    <tr class="text">
    <td><?php echo text(xl('Provider Id').':');?></td>
    <td><span><?php echo text($GLOBALS['portal_offsite_providerid']);?></span></td>
    </tr>
        <?php
    }
    ?>
        <tr class="text">
            <td><?php echo text(xl('User Name').':');?></td>
            <td><input type="text" name="uname" value="<?php echo ($row['portal_username']) ? attr($row['portal_username']) : attr($row['fname'].$row['id']); ?>" size="10" readonly></td>
        </tr>
        <tr class="text">
            <td><?php echo text(xl('Password').':');?></td>
            <?php
            $pwd = generatePassword();
            ?>
            <td><input type="text" name="pwd" id="pwd" value="<?php echo attr($pwd); ?>" size="10"/>
            </td>
            <td><a href="#" class="css_button" onclick="top.restoreSession(); javascript:document.location.reload()"><span><?php echo xlt('Change'); ?></span></a></td>
        </tr>
        <tr class="text">
            <td><input type="hidden" name="form_save" id="form_save"></td>
            <td colspan="5" align="center">
                <a href="#" class="css_button" onclick="return transmit()"><span><?php echo xlt('Save');?></span></a>
                <input type="hidden" name="form_cancel" id="form_cancel">
                <a href="#" class="css_button" onclick="top.restoreSession(); dlgclose();"><span><?php echo xlt('Cancel');?></span></a>
            </td>
        </tr>
    </table>
    </form>
</body>
