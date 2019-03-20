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



$filename = $GLOBALS['OE_SITE_DIR'] . '/documents/couchdb/log.txt';

$fh = file_get_contents($filename);

if (cryptCheckStandard($fh)) {
    $fh = decryptStandard($fh, null, 'database');
}

if (!empty($fh)) {
    echo nl2br(text($fh));
} else {
    echo xlt("CouchDB error log is empty");
}
