<?php
include_once("../../globals.php");
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_bottom">

<dl>
<dt><span class="title"><?php xl('Prescriptions','e'); ?></span></dt>
<dd><a class="text" href="<?php echo $GLOBALS['webroot']?>/controller.php?prescription&list&id=<?php echo $pid?>"
 target="RxRight" onclick="top.restoreSession()">
<?php xl('List Prescriptions','e'); ?></a></dd>
<dd><a class="text" href="<?php echo $GLOBALS['webroot']?>/controller.php?prescription&edit&id=&pid=<?php echo $pid?>"
 target="RxRight" onclick="top.restoreSession()">
<?php xl('Add Prescription','e'); ?></a></dd>
</dl>

</body>
</html>
