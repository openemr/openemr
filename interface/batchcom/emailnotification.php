<?php

/**
 * emailnotification script.
 *
 * @package OpenEMR
 * @author  cfapress
 * @author  Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @link    http://www.open-emr.org
 * @copyright Copyright (c) 2008 cfapress
 * @copyright Copyright (c) 2017 Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/registry.inc");
require_once("batchcom.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// gacl control
if (!AclMain::aclCheckCore('admin', 'notification')) {
    echo "<html>\n<body>\n<h1>";
    echo xlt('You are not authorized for this.');
    echo "</h1>\n</body>\n</html>\n";
    exit();
}

// process form
if (!empty($_POST['form_action']) && ($_POST['form_action'] == 'save')) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if (! is_numeric($_POST['notification_id'])) {  // shouldn't happen
        $form_err .= xl('Missing/invalid notification id') . '<br />';
    }

    if (empty($_POST['email_sender'])) {
        $form_err .= xl('Empty value in "Email Sender"') . '<br />';
    }

    if (empty($_POST['email_subject'])) {
        $form_err .= xl('Empty value in "Email Subject"') . '<br />';
    }

    if (empty($_POST['provider_name'])) {
        $form_err .= xl('Empty value in "Name of Provider"') . '<br />';
    }

    if (empty($_POST['message'])) {
        $form_err .= xl('Empty value in "Email Text"') . '<br />';
    }

    // Store the new settings.  sms_gateway_type is not used for email.
    // notification_id is the pk, and should always be 2 for email settings.

    if (!$form_err) {
        $sql_text = " ( `notification_id` , `sms_gateway_type` , `provider_name` , `message` , `email_sender` , `email_subject` , `type` ) ";
        $sql_value = " (?, ?, ?, ?, ?, ?, ?) ";
        $values = array($_POST['notification_id'], '', $_POST['provider_name'],
                        $_POST['message'], $_POST['email_sender'],
                        $_POST['email_subject'], 'Email');
        $query = "REPLACE INTO `automatic_notification` $sql_text VALUES $sql_value";
        //echo $query;
        $id = sqlInsert($query, $values);
        $sql_msg = xl("ERROR!... in Update");
        if ($id) {
            $sql_msg = xl("Email Notification Settings Updated Successfully");
        }
    }
}

// fetch email config from table.  This should never fail, because one row
// of each type is seeded when the db is created.
$sql = "select * from automatic_notification where type='Email'";
$result = sqlQuery($sql);
if ($result) {
    $notification_id = $result['notification_id'];
    $provider_name = $result['provider_name'];
    $email_sender = $result['email_sender'];
    $email_subject = $result['email_subject'];
    $message = $result['message'];
} else {
    $sql_msg = xl('Missing email config record');
}

//my_print_r($result);

//START OUT OUR PAGE....
?>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("Email Notification"); ?></title>
</head>
<body class="body_top container">
    <header class="row">
        <?php require_once("batch_navigation.php");?>
        <h1 class="col-md-12">
            <a href="batchcom.php"><?php echo xlt('Batch Communication Tool'); ?></a>
            <small><?php echo xlt('Email Notification'); ?></small>
        </h1>
    </header>
    <main class="mx-4">
        <?php
        if (!empty($form_err)) {
            echo '<div class="alert alert-danger">' . xlt('The following errors occurred') . ': ' . text($form_err) . '</div>';
        }

        if (!empty($sql_msg)) {
            echo '<div class="alert alert-info">' . xlt('The following occurred') . ': ' . text($sql_msg) . '</div>';
        }
        ?>
        <form name="select_form" method="post" action="">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type="Hidden" name="type" value="Email">
            <input type="Hidden" name="notification_id" value="<?php echo attr($notification_id);?>">
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="email_sender"><?php echo xlt('Email Sender')?>:</label>
                    <input class="form-control" type="text" name="email_sender" size="40" value="<?php echo attr($email_sender); ?>" placeholder="<?php xla('sender name'); ?>">
                </div>
                <div class="col-md-4 form-group">
                    <label for="email_subject"><?php echo xlt('Email Subject')?>:</label>
                    <input class="form-control" type="text" name="email_subject" size="40" value="<?php echo attr($email_subject); ?>" placeholder="<?php xla('email subject'); ?>">
                </div>
                <div class="col-md-4 form-group">
                    <label for="provider_name"><?php echo xlt('Name of Provider')?>:</label>
                    <input class="form-control" type="text" name="provider_name" size="40" value="<?php echo attr($provider_name); ?>" placeholder="<?php xla('provider name'); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="message"><?php echo xlt('Email Text Usable Tags'); ?>: ***NAME***, ***PROVIDER***, ***DATE***, ***STARTTIME***, ***ENDTIME*** (i.e. Dear ***NAME***):</label>
                    <textarea class="form-control" cols="35" rows="8" name="message"><?php echo text($message); ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                    <button class="btn btn-secondary btn-save" type="submit" name="form_action" value="save"><?php echo xlt('Save'); ?></button>
                </div>
            </div>
        </form>
    </main>
</body>
</html>
