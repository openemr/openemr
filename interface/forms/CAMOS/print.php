<?php
require_once("../../globals.php");
require_once("../../../library/api.inc");
formHeader("Form: CAMOS");
?>
<html><head>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
</head>
<body <?php echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<form method=post action="<?php echo $rootdir;?>/forms/CAMOS/save.php?mode=new" name="my_form"
 onsubmit="return top.restoreSession()">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(collectCsrfToken()); ?>" />
<h1><php echo xlt('CAMOS'); ?> </h1>
<hr>
<input type="submit" name="submit form" value="<?php echo xla('submit form'); ?>" />
<?php
echo "<a href='{$GLOBALS['form_exit_url']}' onclick='top.restoreSession()'>[" .
xlt('do not save') . "]</a>";
?>
<table></table><h3><php echo xlt('Computer Aided Medical Ordering System'); ?></h3>
<table><tr><td><php echo xlt('category'); ?></td> <td><input type="text" name="category"  /></td></tr>
<tr><td><php echo xlt('subcategory'); ?></td> <td><input type="text" name="subcategory"  /></td></tr>
<tr><td><php echo xlt('item'); ?></td> <td><input type="text" name="item"  /></td></tr>
<tr><td><php echo xlt('content'); ?></td> <td><input type="text" name="content"  /></td></tr>
</table><input type="submit" name="submit form" value="submit form" />
<?php
echo "<a href='{$GLOBALS['form_exit_url']}' onclick='top.restoreSession()'>[" .
xlt('do not save') ."]</a>";
?>

</form>
<?php
formFooter();
