<?php

/**
 * will display the couchdb log
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Crypto\CryptoGen;

$filename = $GLOBALS['OE_SITE_DIR'] . '/documents/couchdb/log.txt';

if (!file_exists($filename)) {
    echo xlt("CouchDB error log is empty");
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
    echo xlt("CouchDB error log is empty");
}
