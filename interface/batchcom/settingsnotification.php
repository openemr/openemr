<?php

/**
 * Notification Settings Script
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
require_once("$srcdir/registry.inc.php");
require_once("batchcom.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

// gacl control
if (!AclMain::aclCheckCore('admin', 'notification')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Notification Settings")]);
    exit;
}

 $type = 'SMS/Email Settings';
// process form
if (!empty($_POST['form_action']) && ($_POST['form_action'] == 'save')) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if ($_POST['Send_SMS_Before_Hours'] == "") {
        $form_err .= xl('Empty value in "SMS Hours"') . '<br />';
    }

    if ($_POST['Send_Email_Before_Hours'] == "") {
        $form_err .= xl('Empty value in "Email Hours"') . '<br />';
    }

    if ($_POST['SMS_gateway_username'] == "") {
        $form_err .= xl('Empty value in "Username"') . '<br />';
    }

    if ($_POST['SMS_gateway_password'] == "") {
        $form_err .= xl('Empty value in "Password"') . '<br />';
    }

    //process sql
    if (!$form_err) {
        $sql_text = " ( `SettingsId` , `Send_SMS_Before_Hours` , `Send_Email_Before_Hours` , `SMS_gateway_password` , `SMS_gateway_apikey` , `SMS_gateway_username` , `type` ) ";
        $sql_value = " (?, ?, ?, ?, ?, ?, ?) ";
        $values = array($_POST['SettingsId'], $_POST['Send_SMS_Before_Hours'], $_POST['Send_Email_Before_Hours'],
                        $_POST['SMS_gateway_password'], $_POST['SMS_gateway_apikey'], $_POST['SMS_gateway_username'],
                        $type);
        $query = "REPLACE INTO `notification_settings` $sql_text VALUES $sql_value";
        //echo $query;
        $id = sqlInsert($query, $values);
        $sql_msg = xl("ERROR!... in Update");
        if ($id) {
            $sql_msg = xl("SMS/Email Alert Settings Updated Successfully");
        }
    }
}

// fetch data from table
$sql = "select * from notification_settings where type='SMS/Email Settings'";
$result = sqlQuery($sql);
if ($result) {
    $SettingsId = $result['SettingsId'];
    $Send_SMS_Before_Hours = $result['Send_SMS_Before_Hours'];
    $Send_Email_Before_Hours = $result['Send_Email_Before_Hours'];
    $SMS_gateway_password = $result['SMS_gateway_password'];
    $SMS_gateway_username = $result['SMS_gateway_username'];
    $SMS_gateway_apikey = $result['SMS_gateway_apikey'];
}

//my_print_r($result);
//START OUT OUR PAGE....
?>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("Notification Settings"); ?></title>
</head>
<body class="body_top container">
    <header class="row">
        <?php require_once("batch_navigation.php");?>
        <h1 class="col-md-12">
            <a href="batchcom.php"><?php echo xlt('Batch Communication Tool'); ?></a>
            <small><?php echo xlt('SMS/Email Alert Settings'); ?></small>
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
            <input type="hidden" name="type" value="SMS">
            <input type="Hidden" name="SettingsId" value="<?php echo attr($SettingsId);?>">

            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="Send_SMS_Before_Hours"><?php echo xlt('SMS send before')?>:</label>
                    <input class="form-control" type="num" name="Send_SMS_Before_Hours" size="10" maxlength="3" value="<?php echo attr($Send_SMS_Before_Hours); ?>" placeholder="###">
                </div>
                <div class="col-md-6 form-group">
                    <label for="Send_Email_Before_Hours"><?php echo xlt('Email send before')?>:</label>
                    <input class="form-control" type="num" name="Send_Email_Before_Hours" size="10" maxlength="3" value="<?php echo attr($Send_Email_Before_Hours); ?>" placeholder="###">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="SMS_gateway_username"><?php echo xlt('Username for SMS Gateway')?>:</label>
                    <input class="form-control" type="text" name="SMS_gateway_username" size="40" value="<?php echo attr($SMS_gateway_username); ?>" placeholder="<?php echo xla('username'); ?>">
                </div>
                <div class="col-md-6 form-group">
                    <label for="SMS_gateway_password"><?php echo xlt('Password for SMS Gateway')?>:</label>
                    <input class="form-control" type="password" name="SMS_gateway_password" size="40" value="<?php echo attr($SMS_gateway_password); ?>" placeholder="<?php echo xla('password'); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="SMS_gateway_apikey"><?php echo xlt('SMS Gateway API key')?>:</label>
                    <input class="form-control" type="text" name="SMS_gateway_apikey" size="40" value="<?php echo attr($SMS_gateway_apikey); ?>" placeholder="<?php echo xla('key'); ?>">
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
