<?php
/**
 * library/video_consultation.inc Functions for BigBlueButton integration
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Toam Jose <pithonsoft@gmail.com>
 * @copyright Copyright (c) 2020 pithonsoft@gmail.com
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . '/../../globals.php');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/forms.inc');
require_once($GLOBALS['srcdir'].'/calendar.inc');
require_once($GLOBALS['srcdir'].'/video_consultation.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/encounter_events.inc.php');
require_once($GLOBALS['srcdir'].'/patient_tracker.inc.php');
require_once($GLOBALS['incdir']."/main/holidays/Holidays_Controller.php");
require_once($GLOBALS['srcdir'].'/group.inc');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

 //Check access control
if (!AclMain::aclCheckCore('patients', 'appt', '', array('write','wsome'))) {
    die(xl('Access not allowed'));
}


/* Things that might be passed by our opener. */
 $eid           = $_GET['eid'];         // only for existing events
?>

<html>
<head>

<title><?php echo xlt('Appointment Details') ?></title>


<script language="JavaScript">
  function copyToClipBoard() {
  var copyText = document.getElementById("md");
  copyText.select();
  copyText.setSelectionRange(0, 99999); /*For mobile devices*/
  document.execCommand("copy");
  alert("Copied the text: " + copyText.value);
}
</script>
</head>

<body>
<table>
<tr></tr>
<tr><td><b>
<?php
echo xlt("Send the following details to patient. Patient can join the meeting by clicking the link or pasting the link in a browser");
?>
</b></td></tr>
<tr>
<td>
<textarea  id="md" readonly cols="80" rows="10" >
<?php
echo getMeetingDetails($eid)
?>
</textarea>
</td></tr>
<tr>
</td><td><input type="button" value="Copy Apnt Details" onclick="copyToClipBoard()"></td>
</tr>
</table>
</body></html>


