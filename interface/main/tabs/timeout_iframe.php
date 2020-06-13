<?php

/**
 * This script runs in a hidden iframe and reloads itself periodically
 * to support auto logout timeout.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Tell auth.inc that this is the daemon script; this is so that
// inactivity timeouts will still work, and to avoid logging an
// event every time we run.
$GLOBALS['DAEMON_FLAG'] = true;

require_once(__DIR__ . "/../../globals.php");

$daemon_interval = 120; // Interval in seconds between reloads.
?>

<html>
<body>
<script>

function timerint() {
    top.restoreSession();
    location.reload();
    return;
}

setTimeout('timerint()', <?php echo $daemon_interval * 1000; ?>);

</script>
</body>
</html>
