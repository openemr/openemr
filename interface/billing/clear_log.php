<?php

/**
 * interface/billing/clear_log.php - backup, then clear billing log
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

//ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'eob', '', 'write') && !AclMain::aclCheckCore('acct', 'bill', '', 'write')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Billing Log")]);
    exit;
}

$filename = $GLOBALS['OE_SITE_DIR'] . '/documents/edi/process_bills.log';
if (file_exists($filename)) {
    $newlog = $GLOBALS['OE_SITE_DIR'] . '/documents/edi/' . date("Y-m-d-His") . '_process_bills.log';
    rename($filename, $newlog);
    echo xlt("Log is cleared. Please close window.");
} else {
    echo xlt("Log was already empty. Please close window.");
}
