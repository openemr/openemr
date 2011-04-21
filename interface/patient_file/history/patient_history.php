<?php

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

include_once("../../globals.php");
?>
	
<HTML>
<head>
<?php html_header_show();?>
<TITLE>
<?php echo htmlspecialchars(xl('Patient History'),ENT_NOQUOTES); ?>
</TITLE>
</HEAD>
<frameset rows="50%,50%" cols="*">
  <frame src="history.php" name="History" scrolling="auto">
  <frame src="encounters.php" name="Encounters" scrolling="auto">
</frameset>

<noframes><body bgcolor="#FFFFFF">

</body></noframes>

</HTML>
