<?php
/**
 * This script runs in a hidden iframe and reloads itself periodically
 * to support auto logout timeout.
 *
 * Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 * Copyright (C) 2016 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */




// Tell auth.inc that this is the daemon script; this is so that
// inactivity timeouts will still work, and to avoid logging an
// event every time we run.
$GLOBALS['DAEMON_FLAG'] = true;

require_once(dirname(__FILE__)) . "/../../globals.php";

$daemon_interval = 120; // Interval in seconds between reloads.
?>

<html>
<body>
<script type="text/javascript">

function timerint() {
    top.restoreSession();
    location.reload();
    return;
}

setTimeout('timerint()', <?php echo $daemon_interval * 1000; ?>);

</script>
</body>
</html>
