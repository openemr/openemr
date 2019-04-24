<?php
/**
 * interface/billing/customize_log.php - starting point for customization of billing log
 *
 * @package OpenEMR
 * @author Stephen Waite <stephen.waite@cmsvt.com>
 * @link http://www.open-emr.org
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/



     
require_once("../globals.php");

$filename = $GLOBALS['OE_SITE_DIR'] . '/edi/process_bills.log';
$date = date("Y-m-d");

$newlog = $GLOBALS['OE_SITE_DIR'] . '/edi/' . $date . '_process_bills.log';

rename($filename, $newlog);

file_put_contents($filename, " ");

echo "Log is clear, please close window";
/*
$fh = fopen($filename, 'r');

while ($line = fgets($fh)) {
    echo(text($line));
    echo("<br />");
}

    fclose($fh);
*/
