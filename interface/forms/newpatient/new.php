<?php

/**
 * Encounter form new script.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Core\OEGlobalsBag;

// Hoist legacy `globals.php` locals so PHPStan can see them (#11792 Phase 5).
$srcdir = OEGlobalsBag::getInstance()->getSrcDir();
$pid = PatientSessionUtil::getPid();

require_once("$srcdir/lists.inc.php");
require_once("$srcdir/patient.inc.php");

// Check permission to create encounters.
$tmp = getPatientData($pid, "squad");
if (($tmp['squad'] && ! AclMain::aclCheckCore('squads', $tmp['squad'])) || !AclMain::aclCheckForm('newpatient', '', ['write', 'addonly'])) {
    // TODO: why is this reversed?
    echo "<body>\n<html>\n";
    echo "<p>(" . xlt('New encounters not authorized') . ")</p>\n";
    echo "</body>\n</html>\n";
    exit();
}

$viewmode = false;
require_once("common.php");
