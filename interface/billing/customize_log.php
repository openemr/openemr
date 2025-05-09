<?php

/**
 * interface/billing/customize_log.php - starting point for customization of billing log
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Twig\TwigContainer;

//ensure user has proper access
if (!AclMain::aclCheckCore('acct', 'eob', '', 'write') && !AclMain::aclCheckCore('acct', 'bill', '', 'write')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Billing Log")]);
    exit;
}

$filename = $GLOBALS['OE_SITE_DIR'] . '/documents/edi/process_bills.log';

if (!file_exists($filename)) {
    echo xlt("Billing log is empty");
    exit;
}

$fh = file_get_contents($filename);

$cryptoGen = new CryptoGen();
if ($cryptoGen->cryptCheckStandard($fh)) {
    $fh = $cryptoGen->decryptStandard($fh, null, 'database');
}

if (!empty($fh)) {
    echo nl2br(text($fh));
} else {
    echo xlt("Billing log is empty");
}
