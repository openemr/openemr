<?php
include_once("../../globals.php");
?>
<html>
<head>
<title><? xl('Patient Summary','e'); ?></title>
</head>

<frameset cols="20%,40%,*">
 <frame src="stats.php" name="Stats" scrolling="auto">
 <frame src="<?php echo $GLOBALS['webroot'] ?>/controller.php?prescription&edit&id=&pid=<?php echo $pid ?>"
  name="Prescription" scrolling="auto">
 <frame src="pnotes.php" name="Notes" scrolling="auto">
</frameset>

</html>
