<?php
/**
 * smsnotification script.
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
require_once("../../library/acl.inc");
require_once("batchcom.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

// gacl control
if (!acl_check('admin', 'notification')) {
    echo "<html>\n<body>\n<h1>";
    echo xlt('You are not authorized for this.');
    echo "</h1>\n</body>\n</html>\n";
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
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    //validation uses the functions in notification.inc.php
    //validate dates
    if (!check_date_format($_POST['next_app_date'])) {
        $form_err .= xl('Date format for "Next Appointment" is not valid') . '<br>';
    }

    // validate selections
    if ($_POST['sms_gateway_type'] == "") {
        $form_err .= xl('Error in "SMS Gateway" selection') . '<br>';
    }

    // validates and or
    if ($_POST['provider_name'] == "") {
        $form_err .= xl('Empty value in "Name of Provider"') . '<br>';
    }

    if ($_POST['message'] == "") {
        $form_err .= xl('Empty value in "SMS Text"') . '<br>';
    }

    //process sql
    if (!$form_err) {
        $next_app_time = $_POST[hour].":".$_POST['min'];
        $sql_text=" ( `notification_id` , `sms_gateway_type` , `next_app_date` , `next_app_time` , `provider_name` , `message` , `email_sender` , `email_subject` , `type` ) ";
        $sql_value=" (?, ?, ?, ?, ?, ?, ?, 'SMS') ";
        $values = array($_POST['notification_id'], $_POST['sms_gateway_type'], $_POST['next_app_date'], $next_app_time,
                        $_POST['provider_name'], $_POST['message'], $_POST['email_sender'], $_POST['email_subject']);
        $query = "REPLACE INTO `automatic_notification` $sql_text VALUES $sql_value";
        //echo $query;
        $id = sqlInsert($query);
        $sql_msg = xl("ERROR!... in Update");
        if ($id) {
            $sql_msg = xl("SMS Notification Settings Updated Successfully");
        }
    }
}

// fetch data from table
$sql = "select * from automatic_notification where type='SMS'";
$result = sqlQuery($sql);
if ($result) {
    $notification_id = $result['notification_id'];
    $sms_gateway_type = $result['sms_gateway_type'];
    $next_app_date = $result['next_app_date'];
    list($hour,$min) = @explode(":", $result['next_app_time']);
    $provider_name = $result['provider_name'];
    $message = $result['message'];
}

// menu arrays (done this way so it's easier to validate input on validate selections)
$sms_gateway=array ('CLICKATELL','TMB4');
$hour_array =array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','21','21','22','23');
$min_array = array('00','05','10','15','20','25','30','35','40','45','50','55');

//START OUT OUR PAGE....
?>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("SMS Notification"); ?></title>
</head>
<body class="body_top container">
    <header class="row">
        <?php require_once("batch_navigation.php");?>
        <h1 class="col-md-12">
            <a href="batchcom.php"><?php echo xlt('Batch Communication Tool'); ?></a>
            <small><?php echo xlt('SMS Notification'); ?></small>
        </h1>
    </header>
    <main>
        <?php
        if ($form_err) {
            echo '<div class="alert alert-danger">' . xlt('The following errors occurred') . ': ' . text($form_err) . '</div>';
        }

        if ($sql_msg) {
            echo '<div class="alert alert-info">' . xlt('The following occurred') . ': ' . text($sql_msg) . '</div>';
        }
        ?>
        <form name="select_form" method="post" action="">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type="hidden" name="type" value="SMS">
            <input type="hidden" name="notification_id" value="<?php echo attr($notification_id); ?>">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label for="sms_gateway_type"><?php echo xlt('SMS Gateway') ?>:</label>
                    <select name="sms_gateway_type" class="form-control">
                        <option value=""><?php echo xlt('Select SMS Gateway'); ?></option>
                        <?php foreach ($sms_gateway as $value) { ?>
                            <option value="<?php echo attr($value); ?>"
                            <?php
                            if ($sms_gateway_type == $value) {
                                echo "selected";
                            }

                            echo text($value);
                            ?>
                            </option>
                        <?php }?>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label for="provider_name"><?php echo xlt('Name of Provider'); ?>:</label>
                    <input class="form-control" type="text" name="provider_name" size="40" value="<?php echo attr($provider_name); ?>" placeholder="<?php xla('provider name'); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                    <label for="message"><?php echo xlt('SMS Text, Usable Tags: '); ?>***NAME***, ***PROVIDER***, ***DATE***, ***STARTTIME***, ***ENDTIME*** (i.e. Dear ***NAME***):</label>
                    <textarea class="form-control" cols="35" rows="8" name="message"><?php echo text($message); ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 form-group">
                    <button class="btn btn-default btn-save" type="submit" name="form_action" value="save"><?php echo xlt('Save'); ?></button>
                </div>
            </div>
        </form>
    </main>
</body>
</html>
