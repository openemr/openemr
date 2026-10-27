<?php

/**
 * This contains the tab set for encounter forms.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// globals.php decides read-only vs writable session before it hands control
// back, so the request has to be read here. globals.php calls CurrentRequest::get()
// itself, so this is the same instance, not a second one.
require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Tabs\TabsWrapper;

// When invoked with set_encounter this script writes pid/encounter to the
// session, so declare the write intent up front and take the session lock once.
// Without it globals.php opens the session read-only (read_and_close) and each
// setpid()/setencounter() call has to reopen it mid-request, which logs a
// "Session reopened for writing after read_and_close" warning per write.
// Requests without set_encounter (returning from an encounter form) do not
// write, so they stay lock-free.
$sessionAllowWrite = CurrentRequest::get()->query->has('set_encounter');

require_once(__DIR__ . '/../../globals.php');
$srcdir = \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir();

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$encounter = $session->get('encounter', 0);
$rootdir = \OpenEMR\Core\OEGlobalsBag::getInstance()->getString('rootdir');

if (isset($_GET["set_encounter"])) {
    // The billing page might also be setting a new pid.
    if (isset($_GET["set_pid"])) {
        $set_pid = $_GET["set_pid"];
    } elseif (isset($_GET["pid"])) {
        $set_pid = $_GET["pid"];
    } else {
        $set_pid = false;
    }

    if ($set_pid && $set_pid != $session->get("pid")) {
        setpid($set_pid);
    }

    setencounter($_GET["set_encounter"]);
}

$tabset = new TabsWrapper('enctabs');
$tabset->declareInitialTab(
    xl('Summary'),
    "<iframe class='w-100' style='height:94.5vh;border: 0;' src='forms.php'>" . xlt('Problem loading.') . "</iframe>"
);
// We might have been invoked to load a particular encounter form.
// In that case it will be the second tab, and removable.
if (!empty($_GET['formname'])) {
    $url = $rootdir . "/patient_file/encounter/load_form.php?formname=" . attr_url($_GET['formname']);
    $tabset->declareInitialTab(
        $_GET['formdesc'],
        "<iframe name='enctabs-2' class='w-100' style='height:94.5vh;border: 0;' src='$url'>" . xlt('Problem loading.') . "</iframe>",
        true
    );
}

// This is for making the page title which will be picked up as the tab label.
$dateres = getEncounterDateByEncounter($encounter);
$encounter_date = date("Y-m-d", strtotime((string) $dateres["date"]));
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo text(oeFormatShortDate($encounter_date)) . ' ' . xlt('Encounter'); ?></title>
    <?php Header::setupHeader(); ?>
<?php echo $tabset->genCss(); ?>
<?php echo $tabset->genJavaScript(); ?>
<script>

$(function () {
  // Initialize support for the tab set.
  twSetup('enctabs');
});

// This is called to refresh encounter display data after something has changed it.
// Currently only the encounter summary tab will be refreshed.
function refreshVisitDisplay() {
  for (var i = 0; i < window.frames.length; ++i) {
    if (window.frames[i].refreshVisitDisplay) {
      window.frames[i].refreshVisitDisplay();
    }
  }
}

// Called from the individual iframes when their forms want to close.
// The iframe window name is passed and identifies which tab it is.
// The "refresh" argument indicates if encounter data may have changed.
function closeTab(winname, refresh) {
  twCloseTab('enctabs', winname);
  if (refresh) {
    refreshVisitDisplay();
  }
}

</script>
</head>
<body class='m-0'>
<?php echo $tabset->genHtml(); ?>
</body>
</html>
