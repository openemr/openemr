<?php
include_once("../../globals.php");
?>
<html>
<head>
<? html_header_show();?>
<title><? xl('Patient Summary','e'); ?></title>
</head>

<frameset cols="25%,*">
 <frame src="stats.php" name="Stats" scrolling="auto">
 <frame src="pnotes.php" name="Notes" scrolling="auto">
</frameset>

</html>
