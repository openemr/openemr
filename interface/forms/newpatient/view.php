<?php

/**
 * Encounter form view script.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\OEGlobalsBag;

// Hoist legacy `globals.php` locals so PHPStan can see them (#11792 Phase 5).
$srcdir = OEGlobalsBag::getInstance()->getSrcDir();

require_once("$srcdir/lists.inc.php");

$disabled = "disabled";

// If we are allowed to change encounter dates...
if (AclMain::aclCheckCore('encounters', 'date_a')) {
    $disabled = "";
}

$viewmode = true;
require_once("common.php");
