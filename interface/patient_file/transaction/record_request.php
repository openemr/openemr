<?php
// Copyright (C) 2010 Brady Miller <brady@sparmy.com>
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
  $("#req_button").click(function() {
    // hide the button, show the message, and send the ajax call
    $('#req_button').hide();
    $('#openreq').show();
    top.restoreSession();
    $.post( "../../../library/ajax/amc_misc_data.php",
      { amc_id: "provide_rec_pat_amc",
        complete: false,
        mode: "add_force",
        patient_id: <?php echo htmlspecialchars($pid,ENT_NOQUOTES); ?>
      }
    );
  });

});

</script>
</head>

<?php // collect data
  $recordRequest = sqlQuery("SELECT * FROM `amc_misc_data` WHERE `pid`=? AND `amc_id`='provide_rec_pat_amc' AND (`date_completed` IS NULL OR `date_completed`='') ORDER BY `date_created` DESC", array($pid) );
?>

<body class="body_top">

<table cellspacing='0' cellpadding='0' border='0'>
<tr>
<td><span class="title"><?php echo htmlspecialchars( xl('Patient Records Request'), ENT_NOQUOTES); ?></span>&nbsp;&nbsp;&nbsp;</td>
</tr>
</table>
<br>
<br>

<?php if (empty($recordRequest)) { ?>
  <a href="javascript:void(0)" id="req_button" class="css_button"><span><?php echo htmlspecialchars( xl('Patient Record Request'), ENT_NOQUOTES);?></span></a>
  <br>
  <span class="text" id="openreq" style="display:none"><?php echo htmlspecialchars(xl('The patient record request has been recorded.'), ENT_NOQUOTES) ?></span>
<?php } else { ?>
  <a href="javascript:void(0)" id="req_button" class="css_button" style="display:none"><span><?php echo htmlspecialchars( xl('Patient Record Request'), ENT_NOQUOTES);?></span></a>
  <br>
  <span class="text" id="openreq"><?php echo htmlspecialchars(xl('There is already an open patient record request.'), ENT_NOQUOTES) ?></span>
<?php } ?>

</body>
</html>
