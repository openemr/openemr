<?php
include_once("../../globals.php");
?>

<HTML>
<head>
<? html_header_show();?>
<TITLE>
<?php xl('Patient History','e'); ?>
</TITLE>
</HEAD>
<frameset rows="50%,50%" cols="*">
  <frame src="history.php" name="History" scrolling="auto">
  <frame src="encounters.php" name="Encounters" scrolling="auto">
</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML>
