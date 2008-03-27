<?php
//INCLUDES, DO ANY ACTIONS, THEN GET OUR DATA
include_once("../globals.php");
include_once("$srcdir/registry.inc");
include_once("$srcdir/sql.inc");
include_once("../../library/acl.inc");
include_once("batchcom.inc.php");

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
$type = "Email";
$email_sender = "EMR Group";
$email_subject = "Welcome to EMR Group";
// process form
if ($_POST['form_action']=='Save') 
{
    //validation uses the functions in notification.inc.php
    if ($_POST['email_sender']=="") $form_err.=xl('Empty value in "Email Sender"','','<br>');
    if ($_POST['email_subject']=="") $form_err.=xl('Empty value in "Email Subject"','','<br>');
    //validate dates
    if (!check_date_format($_POST['next_app_date'])) $form_err.=xl('Date format for "Next Appointment" is not valid','','<br>');
    // validates and or
    if ($_POST['provider_name']=="") $form_err.=xl('Empty value in "Name of Provider"','','<br>');
    if ($_POST['message']=="") $form_err.=xl('Empty value in "Email Text"','','<br>');
    //process sql
    if (!$form_err) 
    {
        $next_app_time = $_POST[hour].":".$_POST['min'];
        $sql_text=" ( `notification_id` , `sms_gateway_type` , `next_app_date` , `next_app_time` , `provider_name` , `message` , `email_sender` , `email_subject` , `type` ) ";
        $sql_value=" ( '".$_POST[notification_id]."' , '".$_POST[sms_gateway_type]."' , '".$_POST[next_app_date]."' , '".$next_app_time."' , '".$_POST[provider_name]."' , '".$_POST[message]."' , '".$_POST[email_sender]."' , '".$_POST[email_subject]."' , '".$type."' ) ";
        $query = "REPLACE INTO `automatic_notification` $sql_text VALUES $sql_value";
        //echo $query;
        $id = sqlInsert($query);
        $sql_msg="ERROR!... in Update";
        if($id)    $sql_msg="Email Notification Settings Updated Successfully";
    } 
}

// fetch data from table
$sql="select * from automatic_notification where type='$type'";
$result = sqlQuery($sql);
if($result)
{
    $notification_id = $result[notification_id];
    $sms_gateway_type = $result[sms_gateway_type];
    $next_app_date = $result[next_app_date];
    list($hour,$min) = @explode(":",$result[next_app_time]);
    $provider_name=$result[provider_name];
    $email_sender=$result[email_sender];
    $email_subject=$result[email_subject];
    $message=$result[message];
}
//my_print_r($result);

// menu arrays (done this way so it's easier to validate input on validate selections)
$hour_array =array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','21','21','22','23');
$min_array = array('00','05','10','15','20','25','30','35','40','45','50','55');

//START OUT OUR PAGE....
?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="batchcom.css" type="text/css">
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>


</head>
<body class="body_top">
<span class="title"><?php include_once("batch_navigation.php");?></span>
<span class="title"><?php xl('Email Notification','e')?></span>
<br><br>
<!-- for the popup date selector -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<FORM name="select_form" METHOD=POST ACTION="">
<input type="Hidden" name="type" value="<?php echo $type;?>">
<input type="Hidden" name="notification_id" value="<?php echo $notification_id;?>">
<div class="text">
    <div class="main_box">
        <?php
        if ($form_err) {
            echo ("The following errors occurred<br>$form_err<br><br>");
        }
        if ($sql_msg) {
            echo ("$sql_msg<br><br>");
        }
        ?>
        <?php xl('Email Sender','e')?> :
        <INPUT TYPE="text" NAME="email_sender" size="40" value="<?php echo $email_sender?>">
        <br>
        <?php xl('Email Subject','e')?> :
        <INPUT TYPE="text" NAME="email_subject" size="60" value="<?php echo $email_subject?>">
        <br>
        
        <?php xl('Name of Provider','e')?> :
        <INPUT TYPE="text" NAME="provider_name" size="40" value="<?php echo $provider_name?>">
        <br>
        <?php xl('SMS Text, Usable Tag: ***NAME***, ***PROVIDER***, ***DATE***, ***STARTTIME***, ***ENDTIME***<br> i.e. Dear ***NAME***','e')?>
        <br>
        <TEXTAREA NAME="message" ROWS="8" COLS="35"><?php echo $message?></TEXTAREA>
        <br><br>
        <INPUT TYPE="submit" name="form_action" value="Save">
    </div>
</div>
</FORM>
