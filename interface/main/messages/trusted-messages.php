<?php

/**
 * trusted-messages.php displays the GUI and handles the interactions with the backend ajax processor for sending
 * messages and file attachments to Trusted email addresses using the Direct protocol.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2022 Discover and Change <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Core\Header;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\OeUI\OemrUI;
use OpenEMR\Services\PatientService;

$message = '';
if (isset($_REQUEST['message_code'])) {
    $message_code = $_REQUEST['message_code'] ?? null;
    switch ($message_code) {
        case 'success':
            $message = xlt("Trusted message was sent");
            break;
    }
}

// check if we have a selected patient already
$pid = "";
$patientName = "";
if (!empty($_SESSION['pid'])) {
    $patientService = new PatientService();
    $patientArray = $patientService->findByPid($_SESSION['pid']);
    if (!empty($patientArray)) {
        $pid = $_SESSION['pid'];
        // if things are empty this ends up being blank.
        $patientName = trim(($patientArray['fname'] ?? '') . ' ' . ($patientArray['lname'] ?? ''));
    }
}

if ($GLOBALS['phimail_verifyrecipientreceived_enable'] == '1') {
    $verifyMessageReceivedChecked = "checked";
} else {
    $verifyMessageReceivedChecked = '';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <?php Header::setupHeader(['datetime-picker', 'opener', 'moment', 'select2']); ?>
    <link rel="stylesheet" href="<?php echo $webroot; ?>/interface/main/messages/css/reminder_style.css?v=<?php echo $v_js_includes; ?>">

    <?php

    $arrOeUiSettings = array(
        'heading_title' => xlt('Messages, Reminders, Recalls'),
        'include_patient_name' => false,// use only in appropriate pages
        'expandable' => false,
        'expandable_files' => array(""),//all file names need suffix _xpd
        'action' => "",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "",//only for actions - reset, link or back
        'show_help_icon' => false,
//        'help_file_name' => ""
    );
    $oemr_ui = new OemrUI($arrOeUiSettings);

    echo "<title>" .  xlt('Messages, Reminders, Recalls') . "</title>";
    ?>
    <script src="js/trusted-messages.js" type="text/javascript"></script>
</head>
<body class='body_top'>
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?>">
        <div class="row">
            <div class="col-sm-12">
                <div class="clearfix">
                    <?php echo  $oemr_ui->pageHeading() . "\r\n"; ?>
                </div>
            </div>
        </div>
        <div class="container-fluid mb-3">
            <ul class="nav nav-pills">
                <li class="nav-item" id='li-mess'>
                    <a href='messages.php' class="active nav-link font-weight-bold" id='messages-li'><?php echo xlt('Back to Messages'); ?></a>
                </li>
            </ul>
        </div>
        <div class="container-fluid mb-3">
            <div class="row" id="messages-div">
                <div class="col-sm-12">
                    <form class="jumbotron jumbotron-fluid p-3" id="trustedForm">
                        <h4><?php echo text("Create New Trusted Direct Message"); ?></h4>

                        <div class="row">
                            <div id="error-validation-failed" class="col-12 d-none alert alert-danger">
                                <p>
                                    <?php echo xlt("One or more required fields are missing in order to send your message"); ?>.
                                    <?php echo xlt("The following fields are required to send your message"); ?>.
                                </p>
                                <ul>
                                    <li><?php echo xlt("Trusted Email Address"); ?></li>
                                    <li><?php echo xlt("Patient"); ?></li>
                                    <li><?php echo xlt("Message or an attached document"); ?></li>
                                </ul>
                            </div>
                            <div id='error-serverError' class="col-12 d-none alert alert-danger">
                                <p>
                                    <?php echo xlt("There was an unknown system error"); ?>.
                                    <?php echo xlt("Check the server logs for more details"); ?>.
                                </p>
                            </div>
                            <div id='error-networkError' class="col-12 d-none alert alert-danger">
                                <p>
                                    <?php echo xlt("There was an error in communicating with the network"); ?>.
                                    <?php echo xlt("Please try again or check your internet connection"); ?>.
                                    <?php echo xlt("Contact support if you continue to have issues"); ?>.
                                </p>
                            </div>
                            <div id='error-directError' class="col-12 d-none alert alert-danger">
                                <p>
                                    <?php echo xlt("There was an error in sending the message to your target recipient"); ?>.
                                </p>

                                <p id="directErrorMessage">
                                </p>
                            </div>
                            <div id="error-invalidDocumentFormat" class="col-12 d-none alert alert-danger">
                                <p>
                                    <?php echo xlt("The system received a document it does not know how to process through the Direct protocol"); ?>.
                                    <?php echo xlt("Supported document mime types are the following"); ?>.
                                </p>
                                <ul>
                                    <?php /* We don't translate these as they don't change across languages */ ?>
                                    <li>application/pdf</li>
                                    <li>application/xml</li>
                                    <li>text/xml</li>
                                </ul>

                                <p id="directErrorMessage">
                                </p>
                            </div>
                            <div id='success' class="col-12 d-none alert alert-success">
                                <p>
                                    <?php echo xlt("Your message was successfully sent"); ?>.
                                </p>

                                <p id="directErrorMessage">
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 oe-custom-line">
                                <div class="row">
                                    <div class="col-6 col-md-4">
                                        <label class='font-weight-bold' for="patientName"><?php echo xlt('Patient'); ?></label>
                                        <input id="patientName" class="form-control" type="textbox" name="patientName" value="<?php echo attr($patientName); ?>" placeholder="<?php echo xla('Choose a patient'); ?>" readonly />
                                        <input id="patientPid" type="hidden" name="pid" value="<?php echo attr($pid); ?>" />
                                    </div>
                                    <div class="col-6 col-md-8 d-flex align-items-end flex-wrap">
                                        <label class='font-weight-bold' for="trusted_email"><?php echo xlt("To (Trusted Email Address)"); ?>*</label>
                                        <input class="form-control" type="textbox" name="trusted_email" value="" />
                                        <?php
                                        // TODO: good future improvement is to allow selecting the address from the address book
                                        ?>
                                        <input class="btn btn-secondary d-none" type="button" value="<?php xla('Open Address Book'); ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 oe-custom-line">
                                <div class="row">
                                    <div class="col-12">
                                        <label><?php echo xlt("Message"); ?></label><textarea class="form-control" name="message"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 oe-custom-line">
                                <div class="row">
                                    <div class="col-12">
                                        <label for="documentName"><?php echo xlt("Attachment"); ?></label>
                                        <input id="documentId" type="hidden" name="documentId" value="" />
                                        <input id="documentName" class="form-control" type="textbox" name="documentName" value="" placeholder="<?php echo xla('Choose a document to attach'); ?>" />
                                        <p class="alert alert-info mt-2"><?php echo xlt("CCD/CCR/CCDA Documents must be in a xml or pdf format in order to send via Direct"); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 oe-custom-line">
                                <div class="row">
                                    <div class="col-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="verifyMessageReceived" id="verifyMessageReceived" value="1" <?php echo $verifyMessageReceivedChecked; ?>>
                                            <label for="verifyMessageReceived" class="form-check-label"><?php echo xlt("Verify Message Received"); ?></label>
                                        </div>

                                    </div>
                                    <div class="col-9">
                                        <p class="alert alert-warning mt-2"><?php echo xlt("Forcing confirmation that recipient received a message could fail to send if the recipient's system does not support or has disabled receipt confirmation."); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 oe-custom-line">
                                <div class="row">
                                    <div class="col-12">
                                        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                        <input id='message-submit' type="submit" class="btn-transmit btn btn-primary" name="submit" value="<?php echo xla("Send"); ?>" />
                                        <i id='message-spinner' class="fa fa-spinner d-none"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
