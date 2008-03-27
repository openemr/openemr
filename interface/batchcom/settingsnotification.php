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

 $type = 'SMS/Email Settings';
// process form
if ($_POST['form_action']=='Save') 
{
    if ($_POST['Send_SMS_Before_Hours']=="") $form_err.=xl('Empty value in "SMS Hours"','','<br>');
    if ($_POST['Send_Email_Before_Hours']=="") $form_err.=xl('Empty value in "Email Hours"','','<br>');
    if ($_POST['SMS_gateway_username']=="") $form_err.=xl('Empty value in "Username"','','<br>');
    if ($_POST['SMS_gateway_password']=="") $form_err.=xl('Empty value in "Password"','','<br>');
    //process sql
    if (!$form_err) 
    {
        $sql_text=" ( `SettingsId` , `Send_SMS_Before_Hours` , `Send_Email_Before_Hours` , `SMS_gateway_password` , `SMS_gateway_apikey` , `SMS_gateway_username` , `type` ) ";
        $sql_value=" ( '".$_POST[SettingsId]."' , '".$_POST[Send_SMS_Before_Hours]."' , '".$_POST[Send_Email_Before_Hours]."' , '".$_POST[SMS_gateway_password]."' , '".$_POST[SMS_gateway_apikey]."' , '".$_POST[SMS_gateway_username]."' , '".$type."' ) ";
        $query = "REPLACE INTO `notification_settings` $sql_text VALUES $sql_value";
        //echo $query;
        $id = sqlInsert($query);
        $sql_msg="ERROR!... in Update";
        if($id)    $sql_msg="SMS/Email Alert Settings Updated Successfully";
    } 
}

// fetch data from table
$sql="select * from notification_settings where type='$type'";
$result = sqlQuery($sql);
if($result)
{
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
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="batchcom.css" type="text/css">
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>


</head>
<body class="body_top">
<span class="title"><?php include_once("batch_navigation.php");?></span>
<span class="title"><?php xl('SMS/Email Alert Settings','e')?></span>
<br><br>
<!-- for the popup date selector -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<FORM name="select_form" METHOD=POST ACTION="">
<input type="Hidden" name="type" value="<?php echo $type;?>">
<input type="Hidden" name="SettingsId" value="<?php echo $SettingsId;?>">
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
        <?php xl('SMS send before','e')?> :
        <INPUT TYPE="text" NAME="Send_SMS_Before_Hours" size="10" maxlength="3" value="<?php echo $Send_SMS_Before_Hours?>"> <strong>Hrs.</strong>
        <br>
        <?php xl('Email send before','e')?> :
        <INPUT TYPE="text" NAME="Send_Email_Before_Hours" size="10" maxlength="3" value="<?php echo $Send_Email_Before_Hours?>"> <strong>Hrs.</strong>
        <br>
        <?php xl('Username for SMS Gateway','e')?> :
        <INPUT TYPE="password" NAME="SMS_gateway_username" size="40" value="<?php $SMS_gateway_username?>">
        <br>
        <?php xl('Password for SMS Gateway','e')?> :
        <INPUT TYPE="password" NAME="SMS_gateway_password" size="40" value="<?php $SMS_gateway_password?>">
        <br>
        <?php xl('SMS Gateway API key','e')?> :
        <INPUT TYPE="text" NAME="SMS_gateway_apikey" size="40" value="<?php $SMS_gateway_apikey?>">
        
        <br><br>
        <INPUT TYPE="submit" name="form_action" value="Save">
    </div>
</div>
</FORM>
