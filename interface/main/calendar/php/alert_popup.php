<?php

require_once("../../../globals.php");
require_once("$srcdir/patient.inc");

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
$pid = strip_tags($_REQUEST['pid']);

if(!isset($_REQUEST['message'])) $_REQUEST['message'] = '';
$message = strip_tags($_REQUEST['message']);

if(!empty($pid)) {
	//Load Patient data
	$result  = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
}

?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js"></script>
</head>

<body class="body_top">

<table cellspacing='0' cellpadding='0' border='0'>
    <tr>
        <td><span class="title"><?php echo xlt("Alert") ?></span></td>
    </tr>
</table>
<br>
    <?php if(isset($result['alert_info']) && !empty(trim($result['alert_info']))) { ?>
        <?php echo trim($result['alert_info']); ?>
    <?php } else if(isset($message) && !empty(trim($message))) { ?>
    	<?php echo trim($message); ?>
    <?php } ?>
</body>
</html>
