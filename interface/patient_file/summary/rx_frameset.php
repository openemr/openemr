<?php
include_once("../../globals.php");
?>
<html>
<head>
<? html_header_show();?>
<title><? xl('Prescriptions','e'); ?></title>
</head>

<frameset cols="25%,*">
 <frame src="rx_left.php" name="RxLeft" scrolling="auto">
 <frame src="<?php echo $GLOBALS['webroot'] ?>/controller.php?prescription&list&id=<?php echo $pid ?>"
  name="RxRight" scrolling="auto">
</frameset>

</html>
