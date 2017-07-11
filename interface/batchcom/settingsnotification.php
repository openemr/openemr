<?php
/**
 * emailnotification script.
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
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @link    http://www.open-emr.org
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @todo    KNOWN SQL INJECTION VECTOR
 */
//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
require_once("../globals.php");
require_once("$srcdir/registry.inc");
require_once("../../library/acl.inc");
require_once("batchcom.inc.php");

use OpenEMR\Core\Header;

// gacl control
if (!acl_check('admin', 'notification')) {
    echo "<html>\n<body>\n<h1>";
    echo xlt('You are not authorized for this.');
    echo "</h1>\n</body>\n</html>\n";
    exit();
}

 $type = 'SMS/Email Settings';
// process form
if ($_POST['form_action']=='save') {
    if ($_POST['Send_SMS_Before_Hours']=="") {
        $form_err .= xlt('Empty value in "SMS Hours"') . '<br>';
    }
    if ($_POST['Send_Email_Before_Hours']=="") {
        $form_err .= xlt('Empty value in "Email Hours"') . '<br>';
    }
    if ($_POST['SMS_gateway_username']=="") {
        $form_err .= xlt('Empty value in "Username"') . '<br>';
    }
    if ($_POST['SMS_gateway_password']=="") {
        $form_err .= xlt('Empty value in "Password"') . '<br>';
    }
    //process sql
    if (!$form_err) {
        $sql_text=" ( `SettingsId` , `Send_SMS_Before_Hours` , `Send_Email_Before_Hours` , `SMS_gateway_password` , `SMS_gateway_apikey` , `SMS_gateway_username` , `type` ) ";
        $sql_value=" ( '".$_POST[SettingsId]."' , '".$_POST[Send_SMS_Before_Hours]."' , '".$_POST[Send_Email_Before_Hours]."' , '".$_POST[SMS_gateway_password]."' , '".$_POST[SMS_gateway_apikey]."' , '".$_POST[SMS_gateway_username]."' , '".$type."' ) ";
        $query = "REPLACE INTO `notification_settings` $sql_text VALUES $sql_value";
        //echo $query;
        $id = sqlInsert($query);
        $sql_msg="ERROR!... in Update";
        if ($id) {
            $sql_msg="SMS/Email Alert Settings Updated Successfully";
        }
    }
}

// fetch data from table
$sql="select * from notification_settings where type='$type'";
$result = sqlQuery($sql);
if ($result) {
    $SettingsId = $result[SettingsId];
    $Send_SMS_Before_Hours = $result[Send_SMS_Before_Hours];
    $Send_Email_Before_Hours = $result[Send_Email_Before_Hours];
    $SMS_gateway_password=$result[SMS_gateway_password];
    $SMS_gateway_username=$result[SMS_gateway_username];
    $SMS_gateway_apikey=$result[SMS_gateway_apikey];
}
//my_print_r($result);
//START OUT OUR PAGE....
?>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <title><?php echo xlt("Notification Settings"); ?></title>
</head>
<body class="body_top">
    <?php require_once("batch_navigation.php");?>
    <header class="text-center">
        <h1>
            <?php echo xlt('Batch Communication Tool'); ?>
            <small><?php echo xlt('SMS/Email Alert Settings')?></small>
        </h1>
    </header>

    <main class="container">
        <?php
        if ($form_err) {
             echo "<div class=\"alert alert-danger\">" . xlt("The following errors occurred") . ": $form_err</div>";
        }
        if ($sql_msg) {
            echo "<div class=\"alert alert-info\">" . xlt("The following errors occurred") . ": $sql_msg</div>";
        }
        ?>
        <form name="select_form" method="post" action="">
            <input type="hidden" name="type" value="SMS">
            <input type="Hidden" name="SettingsId" value="<?php echo $SettingsId;?>">

            <div class="row">
                <div class="col-md-12">
                    <label for="Send_SMS_Before_Hours"><?php echo xlt('SMS send before')?>:</label>
                    <input type="num" name="Send_SMS_Before_Hours" size="10" maxlength="3" value="<?php echo $Send_SMS_Before_Hours; ?>" placeholder="###">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label for="Send_Email_Before_Hours"><?php echo xlt('Email send before')?>:</label>
                    <input type="num" name="Send_Email_Before_Hours" size="10" maxlength="3" value="<?php echo $Send_Email_Before_Hours; ?>" placeholder="###">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label for="SMS_gateway_username"><?php echo xlt('Username for SMS Gateway')?>:</label>
                    <input type="text" name="SMS_gateway_username" size="40" value="<?php echo $SMS_gateway_username; ?>" placeholder="username">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label for="SMS_gateway_password"><?php echo xlt('Password for SMS Gateway')?>:</label>
                    <input type="password" name="SMS_gateway_password" size="40" value="<?php echo $SMS_gateway_password; ?>" placeholder="password">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label for="SMS_gateway_apikey"><?php echo xlt('SMS Gateway API key')?>:</label>
                    <input type="text" name="SMS_gateway_apikey" size="40" value="<?php echo $SMS_gateway_apikey; ?>" placeholder="key">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <input class="btn btn-primary" type="submit" name="form_action" value="save">
                </div>
            </div>
            
        </form>
    </div>
    
</body>
</html>