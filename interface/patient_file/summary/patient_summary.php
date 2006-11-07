<?php
include_once("../../globals.php");
?>

<HTML>
<HEAD>
<TITLE>
<? xl('Patient Summary','e'); ?>
</TITLE>
</HEAD>

<frameset rows="50%,50%" cols="*">
 <frame src="demographics.php" name="Demographics" scrolling="auto">
<?php if ($GLOBALS['athletic_team']) { ?>
 <frameset rows="*" cols="50%,50%">
<?php } else { ?>
 <frameset rows="*" cols="20%,80%">
<?php } ?>
  <frame src="stats.php" name="Stats" scrolling="auto">
  <frame src="pnotes.php" name="Notes" scrolling="auto">
 </frameset>
</frameset>

<noframes><body bgcolor="#FFFFFF">
</body></noframes>

</HTML>
