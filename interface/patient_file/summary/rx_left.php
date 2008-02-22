<?php
include_once("../../globals.php");
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>
<body <?php echo $bottom_bg_line;?> topmargin='0' rightmargin='0' leftmargin='2'
 bottommargin='0' marginwidth='2' marginheight='0'>

<dl>
<dt><span class="title"><?php xl('Prescriptions','e'); ?></span></dt>
<dd><a class="text" href="<?=$GLOBALS['webroot']?>/controller.php?prescription&list&id=<?=$pid?>"
 target="RxRight" onclick="top.restoreSession()">
<?php xl('List Prescriptions','e'); ?></a></dd>
<dd><a class="text" href="<?=$GLOBALS['webroot']?>/controller.php?prescription&edit&id=&pid=<?=$pid?>"
 target="RxRight" onclick="top.restoreSession()">
<?php xl('Add Prescription','e'); ?></a></dd>
</dl>

</body>
</html>
