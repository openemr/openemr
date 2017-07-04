<?php
/**
 * smsnotification script.
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

include_once("../globals.php");
include_once("$srcdir/registry.inc");
include_once("../../library/acl.inc");
include_once("batchcom.inc.php");

use OpenEMR\Core\Header;

// gacl control
$thisauth = acl_check('admin', 'notification');

if (!$thisauth) {
    echo "<html>\n<body>\n";
    echo "<p>".xl('You are not authorized for this.','','','</p>')."\n";
    echo "</body>\n</html>\n";
    exit();
}

 // default value
$next_app_date = date("Y-m-d");
$hour="12";
$min="15";
$provider_name="EMR Group";
$message="Welcome to EMR Group";

// process form
if ($_POST['form_action']=='save') {
    //validation uses the functions in notification.inc.php
    //validate dates
    if (!check_date_format($_POST['next_app_date'])) $form_err.=xl('Date format for "Next Appointment" is not valid','','<br>');
    // validate selections
    if ($_POST['sms_gateway_type']=="") $form_err.=xl('Error in "SMS Gateway" selection','','<br>');
    // validates and or
    if ($_POST['provider_name']=="") $form_err.=xl('Empty value in "Name of Provider"','','<br>');
    if ($_POST['message']=="") $form_err.=xl('Empty value in "SMS Text"','','<br>');
    //process sql
    if (!$form_err) {
        $next_app_time = $_POST[hour].":".$_POST['min'];
        $sql_text=" ( `notification_id` , `sms_gateway_type` , `next_app_date` , `next_app_time` , `provider_name` , `message` , `email_sender` , `email_subject` , `type` ) ";
        $sql_value=" ( '".$_POST[notification_id]."' , '".$_POST[sms_gateway_type]."' , '".$_POST[next_app_date]."' , '".$next_app_time."' , '".$_POST[provider_name]."' , '".$_POST[message]."' , '".$_POST[email_sender]."' , '".$_POST[email_subject]."' , 'SMS' ) ";
        $query = "REPLACE INTO `automatic_notification` $sql_text VALUES $sql_value";
        //echo $query;
        $id = sqlInsert($query);
        $sql_msg = "ERROR!... in Update";
        if($id) { $sql_msg = "SMS Notification Settings Updated Successfully"; }
    }
}

// fetch data from table
$sql = "select * from automatic_notification where type='SMS'";
$result = sqlQuery($sql);
if($result) {
    $notification_id = $result[notification_id];
    $sms_gateway_type = $result[sms_gateway_type];
    $next_app_date = $result[next_app_date];
    list($hour,$min) = @explode(":",$result[next_app_time]);
    $provider_name=$result[provider_name];
    $message=$result[message];
}

// menu arrays (done this way so it's easier to validate input on validate selections)
$sms_gateway=array ('CLICKATELL','TMB4');
$hour_array =array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','21','21','22','23');
$min_array = array('00','05','10','15','20','25','30','35','40','45','50','55');

//START OUT OUR PAGE....
?>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker']); ?>
    <title><?php xl("SMS Notification"); ?></title>
</head>
<body class="body_top">
    <?php include_once("batch_navigation.php");?>
    <header class="text-center">
        <h1>
            <?php xl('Batch Communication Tool','e'); ?>
            <small><?php xl('SMS Notification','e')?></small>
        </h1>
    </header>

    <main class="container">
        <?php if ($form_err) { echo "<div class=\"alert alert-danger\">".xl("The following errors occurred").": $form_err</div>"; } ?>
        <?php if ($sql_msg) { echo "<div class=\"alert alert-info\">".xl("The following errors occurred").": $sql_msg</div>"; } ?>
        <form name="select_form" method="post" action="">
            <input type="hidden" name="type" value="SMS">
            <input type="hidden" name="notification_id" value="<?php echo $notification_id;?>">
            <div class="row">
                <div class="col-md-12">
                    <label for="sms_gateway_type"><?php xl('SMS Gateway','e') ?>:</label>
                    <select name="sms_gateway_type">
                        <option value="">Select SMS Gateway</option>
                        <?php foreach ($sms_gateway as $value) {?>
                            <option value="<?php echo $value;?>" <?php if ($sms_gateway_type == $value) {echo "selected";}?>><?php echo $value;?></option>
                        <?php }?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label for="provider_name"><?php xl('Name of Provider','e')?>:</label>
                    <input type="text" name="provider_name" size="40" value="<?php echo $provider_name; ?>" placeholder="provider name">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <label for="message"><?php xl('SMS Text, Usable Tags: ','e'); ?>***NAME***, ***PROVIDER***, ***DATE***, ***STARTTIME***, ***ENDTIME*** (i.e. Dear ***NAME***):</label>
                </div>
            </div>                    
            <div class="row">
                <div class="col-md-12">
                    <textarea cols="35" rows="8" name="message"><?php echo $message; ?></textarea>
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