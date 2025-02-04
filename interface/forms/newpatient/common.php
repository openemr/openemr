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
/**
 * @global $srcdir  Note all globals come from the globals.php file
 */
require_once("$srcdir/options.inc.php");
require_once("$srcdir/lists.inc.php");

if ($GLOBALS['enable_group_therapy']) {
    require_once("$srcdir/group.inc.php");
}
// I'd prefer to pull this into src... but it breaks the modularity of this form.  Not sure how to handle that.
require_once "C_EncounterVisitForm.class.php";

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Forms\NewPatient\C_EncounterVisitForm;

try {
    /**
     * @global $rootdir
     */
    $controller = new C_EncounterVisitForm(__DIR__, $GLOBALS['kernel'], $GLOBALS['ISSUE_TYPES'], $rootdir, 'newpatient/common.php');
    /**
     * @global $pid
     */
    $controller->render($pid);
} catch (\Exception $e) {
    // any twig errors or other errors are caught
    (new SystemLogger())->error($e->getMessage(), ['trace' => $e->getTraceAsString(), 'pid' => $pid]);
    echo $e->getMessage();
    die();
}
