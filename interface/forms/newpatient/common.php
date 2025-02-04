<?php
/**
 * Common script for the encounter form (new and view) scripts.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2025 Mountain Valley Health <mvhinspire@mountainvalleyhealthinc.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @license   There are segments of code in this file that have been generated via Claude.ai and are licensed as Public Domain.  They have been marked with a header and footer.
 */

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/lists.inc.php");

if ($GLOBALS['enable_group_therapy']) {
    require_once("$srcdir/group.inc.php");
}

require_once "C_EncounterVisitForm.class.php";

$controller = new \OpenEMR\Forms\NewPatient\C_EncounterVisitForm(__DIR__, $GLOBALS['kernel'], $GLOBALS['ISSUE_TYPES']);
/**
 * @global $pid
 */
$controller->render($pid);
