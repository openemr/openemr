<?php
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This script runs in a hidden frame, reloads itself periodically,
 // and does whatever might need doing in the background.

 // Tell auth.inc that this is the daemon script; this is so that
 // inactivity timeouts will still work, and to avoid logging an
 // event every time we run.
 $GLOBALS['DAEMON_FLAG'] = true;

 include_once("../globals.php");

 $daemon_interval = 120; // Interval in seconds between reloads.
 $colorh = '#ff0000';    // highlight color
 $colorn = '#000000';    // normal color

 // Check if there are faxes in the recvq.
 $faxcount = 0;
 if ($GLOBALS['hylafax_server']) {
  $statlines = array();
  exec("faxstat -r -l -h " . $GLOBALS['hylafax_server'], $statlines);
  foreach ($statlines as $line) {
   if (substr($line, 0, 1) == '-') ++$faxcount;
  }
 }
 $color_fax = $faxcount ? $colorh : $colorn;

 // Check if this user has any active patient notes assigned to them.
 $row = sqlQuery("SELECT count(*) AS count FROM pnotes WHERE " .
  "activity = 1 ".
  " AND deleted != 1 ". // exlude ALL deleted notes
  " AND assigned_to = '" . $_SESSION['authUser'] . "'");
 $color_aun = $row['count'] ? $colorh : $colorn;
?>
<html>
<body bgcolor="#000000">
<script language='JavaScript'>

 function timerint() {
  location.reload();
  return;
 }

 var ld = parent.left_nav.document;

 if (ld && ld.getElementById('searchFields')) {
  setTimeout('timerint()', <?php echo $daemon_interval * 1000; ?>);

  var elem = ld.getElementById('lbl_fax');
  if (elem) elem.style.color = '<?php echo $color_fax; ?>';

  elem = ld.getElementById('lbl_aun');
  if (elem) elem.style.color = '<?php echo $color_aun; ?>';
 }
 else {
  // Nav frame is not fully loaded yet, so wait a few secs.
  setTimeout('timerint()', 5000);
 }

</script>
</body>
</html>
