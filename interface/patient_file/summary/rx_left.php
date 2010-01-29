<?php
include_once("../../globals.php");
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">

<span class="title"><?php xl('Prescriptions','e'); ?></span>
<table>
<tr height="20px">
<td>
<a href="<?php echo $GLOBALS['webroot']?>/controller.php?prescription&list&id=<?php echo $pid?>"  target='RxRight' class="css_button" onclick="top.restoreSession()">
<span><?php xl('List', 'e');?></span></a>
<a href="<?php echo $GLOBALS['webroot']?>/controller.php?prescription&edit&id=&pid=<?php echo $pid?>"  target='RxRight' class="css_button" onclick="top.restoreSession()">
<span><?php xl('Add','e');?></span></a>
</td>
</tr>
</table>

</body>
</html>
