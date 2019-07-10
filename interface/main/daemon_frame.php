<?php
/**
 * This script runs in a hidden frame, reloads itself periodically,
 * and does whatever might need doing in the background.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


 // Tell auth.inc that this is the daemon script; this is so that
 // inactivity timeouts will still work, and to avoid logging an
 // event every time we run.
 $GLOBALS['DAEMON_FLAG'] = true;

 require_once("../globals.php");

 $daemon_interval = 120; // Interval in seconds between reloads.
 $colorh = '#ff0000';    // highlight color
 $colorn = '#000000';    // normal color

 // Check if there are faxes in the recvq.
 $faxcount = 0;
if ($GLOBALS['enable_hylafax']) {
    $statlines = array();
    exec("faxstat -r -l -h " . escapeshellarg($GLOBALS['hylafax_server']), $statlines);
    foreach ($statlines as $line) {
        if (substr($line, 0, 1) == '-') {
            ++$faxcount;
        }
    }
}

 $color_fax = $faxcount ? $colorh : $colorn;

 // Check if this user has any active patient notes assigned to them.
 $row = sqlQuery("SELECT count(*) AS count FROM pnotes WHERE " .
  "activity = 1 ".
  " AND deleted != 1 ". // exlude ALL deleted notes
  " AND assigned_to = ?", array($_SESSION['authUser']));
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
  setTimeout('timerint()', <?php echo attr(($daemon_interval * 1000)); ?>);

  var elem = ld.getElementById('lbl_fax');
  if (elem) elem.style.color = '<?php echo attr($color_fax); ?>';

  elem = ld.getElementById('lbl_aun');
  if (elem) elem.style.color = '<?php echo attr($color_aun); ?>';
 }
 else {
  // Nav frame is not fully loaded yet, so wait a few secs.
  setTimeout('timerint()', 5000);
 }

</script>
</body>
</html>
