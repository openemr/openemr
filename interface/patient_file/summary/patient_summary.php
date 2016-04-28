<?php
include_once("../../globals.php");
?>
<html>
<head>
<?php html_header_show();?>
<title><?php xl('Patient Summary','e'); ?></title>
</head>
<frameset rows="50%,50%">
 <frame src="demographics.php" name="Demographics" scrolling="auto">
 <frameset cols="20%,80%">
  <frame src="stats.php" name="Stats" scrolling="auto">
  <frame src="pnotes.php" name="Notes" scrolling="auto">
 </frameset>
</frameset>
</html>
