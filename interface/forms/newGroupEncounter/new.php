<?php

/**
 * Encounter form new script.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\OEGlobalsBag;

// Hoist legacy `globals.php` locals so PHPStan can see them (#11792 Phase 5).
$srcdir = OEGlobalsBag::getInstance()->getSrcDir();

require_once("$srcdir/lists.inc.php");

$viewmode = false;
if (AclMain::aclCheckCore("groups", "glog", '', 'write')) {
    require_once("common.php");
} else {
    echo xlt("access not allowed");
}
