<?php

/**
 * Display patient notes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/pnotes.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

$prow = getPatientData($pid, "squad, title, fname, mname, lname");

// Check authorization.
$thisauth = AclMain::aclCheckCore('patients', 'notes');
if (!$thisauth) {
    die(xlt('Not authorized'));
}

if ($prow['squad'] && ! AclMain::aclCheckCore('squads', $prow['squad'])) {
    die(xlt('Not authorized for this squad.'));
}

$noteid = $_REQUEST['noteid'];

$ptname = $prow['title'] . ' ' . $prow['fname'] . ' ' . $prow['mname'] .
  ' ' . $prow['lname'];

$title       = '';
$assigned_to = '';
$body        = '';
$activity    = 0;
if ($noteid) {
    $nrow = getPnoteById($noteid, 'title,assigned_to,activity,body');
    $title = $nrow['title'];
    $assigned_to = $nrow['assigned_to'];
    $activity = $nrow['activity'];
    $body = $nrow['body'];
}
?>
<html>
<head>
<?php Header::setupHeader(); ?>
</head>

<body class="body_top">

<p><?php echo "<b>" .
  generate_display_field(array('data_type' => '1','list_id' => 'note_type'), $title) .
  "</b>" . ' ' . xlt('for') . ' ' .
  "<b>" . attr($ptname) . "</b>"; ?></p>

<p><?php echo xlt('Assigned To'); ?>: <?php echo text($assigned_to); ?></p>

<p><?php echo xlt('Active{{Note}}'); ?>: <?php echo ($activity ? xlt('Yes') : xlt('No')); ?></p>

<p><?php echo nl2br(text($body)); ?></p>

<script>
opener.top.printLogPrint(window);
</script>

</body>
</html>
