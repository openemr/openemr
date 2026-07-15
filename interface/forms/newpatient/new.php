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
$squad = $tmp['squad'] ?? '';
$squad = is_string($squad) ? $squad : '';

if (($squad === '' || AclMain::aclCheckCore('squads', $squad)) && AclMain::aclCheckForm('newpatient', '', ['write', 'addonly'])) {
    $viewmode = false;
    require_once("common.php");
    return;
}
http_response_code(403);
$notAuthorizedText = xlt('New encounters not authorized');
?>
<html>
    <head><title><?php echo $notAuthorizedText; ?></title></head>
    <body><p><?php echo $notAuthorizedText; ?></p></body>
</html>
