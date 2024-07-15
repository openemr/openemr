<?php

/**
 * Patient Reminder Setup
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../globals.php");

use OpenEMR\Core\Header;
use OpenEMR\Modules\FaxSMS\Controller\EmailClient;

$emailSetup = new EmailClient();
$credentials = $emailSetup->getEmailSetup();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $credentials = array(
        'sender_name' => $_POST['sender_name'],
        'sender_email' => $_POST['sender_email'],
        'notification_email' => $_POST['notification_email'],
        'email_transport' => $_POST['email_transport'],
        'smtp_host' => $_POST['smtp_host'],
        'smtp_port' => $_POST['smtp_port'],
        'smtp_user' => $_POST['smtp_user'],
        'smtp_password' => $_POST['smtp_password'],
        'smtp_security' => $_POST['smtp_security'],
        'notification_hours' => $_POST['notification_hours'],
        'smsMessage' => $_POST['smsmessage']
    );
    $emailSetup->saveEmailSetup($credentials);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt("Patient Reminder Setup") ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php Header::setupHeader(); ?>
    <script>
        const popNotify = function (e, ppath) {
            try {
                top.restoreSession();
            } catch (error) {
                console.log('Session restore failed!');
            }
            let msg = <?php echo xlj('Are you sure you wish to send all scheduled reminders now?') ?>;
            if (e === 'live') {
                let yn = confirm(msg);
                if (!yn) {
                    return false;
                }
            }
            let msg1 = <?php echo xlj('Appointment Reminder Alerts!') ?>;
            dlgopen(ppath, '_blank', 1240, 900, true, msg1);
        };
    </script>
</head>
<body>
    <div class="container">
        <div class="alert alert-info">
            <?php echo xlt("Use Config Notifications to setup SMTP. Setup here is work in progress for a secondary client. Sending Email reminders is still available from Reminder Actions dropdown.") ?>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <h4><?php echo xlt("Setup Patient Reminder Credentials") ?></h4>
            <div class="dropdown mr-1">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php echo xlt("Reminder Actions") ?>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href="#" onclick="popNotify('', './library/rc_sms_notification.php?dryrun=1&type=email&site=<?php echo attr_url($_SESSION['site_id']) ?>')"><?php echo xlt('Test Email Reminders'); ?></a>
                    <a class="dropdown-item" href="#" onclick="popNotify('live', './library/rc_sms_notification.php?type=email&site=<?php echo attr_url($_SESSION['site_id']) ?>')"><?php echo xlt('Send Email Reminders'); ?></a>
                </div>
            </div>
        </div>
        <form class="form" id="setup-form" method="POST" role="form">
            <div class="messages"></div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sender_name"><?php echo xlt("Patient Reminder Sender Name") ?></label>
                        <input id="sender_name" type="text" name="sender_name" class="form-control" value='<?php echo attr($credentials['sender_name']) ?>' required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sender_email"><?php echo xlt("Patient Reminder Sender Email") ?></label>
                        <input id="sender_email" type="email" name="sender_email" class="form-control" value='<?php echo attr($credentials['sender_email']) ?>' required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="notification_email"><?php echo xlt("Notification Email Address") ?></label>
                        <input id="notification_email" type="email" name="notification_email" class="form-control" value='<?php echo attr($credentials['notification_email']) ?>' required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email_transport"><?php echo xlt("Email Transport Method") ?></label>
                        <input id="email_transport" type="text" name="email_transport" class="form-control" value='<?php echo attr($credentials['email_transport']) ?>' required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="smtp_host"><?php echo xlt("SMTP Server Hostname") ?></label>
                        <input id="smtp_host" type="text" name="smtp_host" class="form-control" value='<?php echo attr($credentials['smtp_host']) ?>' required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="smtp_port"><?php echo xlt("SMTP Server Port Number") ?></label>
                        <input id="smtp_port" type="number" name="smtp_port" class="form-control" value='<?php echo attr($credentials['smtp_port']) ?>' required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="smtp_user"><?php echo xlt("SMTP User for Authentication") ?></label>
                        <input id="smtp_user" type="text" name="smtp_user" class="form-control" value='<?php echo attr($credentials['smtp_user']) ?>' required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="smtp_password"><?php echo xlt("SMTP Password for Authentication") ?></label>
                        <input id="smtp_password" type="password" name="smtp_password" class="form-control" value='<?php echo attr($credentials['smtp_password']) ?>' required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="smtp_security"><?php echo xlt("SMTP Security Protocol") ?></label>
                        <input id="smtp_security" type="text" name="smtp_security" class="form-control" value='<?php echo attr($credentials['smtp_security']) ?>' required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="notification_hours"><?php echo xlt("Email Notification Hours") ?></label>
                        <input id="notification_hours" type="number" name="notification_hours" class="form-control" value='<?php echo attr($credentials['notification_hours']) ?>' required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="form_message"><?php echo xlt("Message Template") ?></label>
                <span style="font-size:12px;font-style: italic;">&nbsp;
                    <?php echo xlt("Tags") ?>: ***NAME***, ***PROVIDER***, ***DATE***, ***STARTTIME***, ***ENDTIME***, ***ORG***</span>
                <textarea id="form_message" rows="3" name="smsmessage" class="form-control" required><?php echo text($credentials['smsMessage']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-success float-right m-2"><?php echo xlt("Save Settings") ?></button>
        </form>
    </div>
</body>
</html>
