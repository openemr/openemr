<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/clinical_rules.php");

?>
<html>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css" media="screen" />
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar_setup.js"></script>
<SCRIPT LANGUAGE="JavaScript">

$(document).ready(function(){
  $("#close").click(function() { parent.$.fn.fancybox.close(); });
});

</script>
</head>

<body class="body_top">
<?php

// Set the session flag to show that notification was last done with this patient
$_SESSION['alert_notify_pid'] = $pid;

// Ensure user is authorized
if (!acl_check('patients', 'med')) {
  echo "<p>(" . htmlspecialchars( xl('Not authorized'), ENT_NOQUOTES) . ")</p>\n";
  echo "</body>\n</html>\n";
  exit();
}

?>
<table cellspacing='0' cellpadding='0' border='0'>
<tr>
<td><span class="title"><?php echo htmlspecialchars ( xl("Active Alerts/Reminders"),ENT_NOQUOTES); ?></span>&nbsp;&nbsp;&nbsp;</td>
<td><a href="#" id="close" class="css_button large_button"><span class='css_button_span large_button_span'><?php echo htmlspecialchars( xl('Close'), ENT_NOQUOTES);?></span></a></td>
</tr>
</table>
<br><br>

<?php echo active_alert_summary($pid,"reminders-due"); ?>

</body>
</html>
