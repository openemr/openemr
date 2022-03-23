<?php

/**
 * get_claim_file.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../globals.php");
require_once $GLOBALS['OE_SITE_DIR'] . "/config.php";

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$content_type = "text/plain";

// The key contains the filename
$fname = convert_safe_file_dir_name($_GET['key']);

// Because of the way the billing tables are constructed (as of 2021)
// We may not know exactly where the file is, so we need to try a couple
// different places. This is mainly because the full path is not stored
// in the database. Also, the file could have been generated with the
// 'gen_x12_based_on_ins_co' global set to 'on' but if it was turned off,
// we still want to be able to download the file. So, we have to do a bit
// of searching.
// The edi directory is the default location.

// the loc, if set, may tell us where the file is
$location = $_GET['location'];
$claim_file_found = false;
if ($location === 'tmp') {
    $claim_file_dir = rtrim($GLOBALS['temporary_files_dir'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (file_exists($claim_file_dir . $fname)) {
        $claim_file_found = true;
    }
}

// See if the file exists in the x-12 partner's SFTP directory
// If it's not there, try the edi directory
if (
    false === $claim_file_found &&
    isset($_GET['partner'])
) {
    $x12_partner_id = $_GET['partner'];
    // First look in the database for the file so we know
    // which partner directory to check
    $sql = "SELECT `X`.`id`, `X`.`x12_sftp_local_dir`
        FROM `x12_partners` `X`
        WHERE `X`.`id` = ?
        LIMIT 1";
    $row = sqlQuery($sql, [$x12_partner_id]);
    if ($row) {
        $claim_file_dir = $row['x12_sftp_local_dir'];
    }

    if (file_exists($claim_file_dir . $fname)) {
        $claim_file_found = true;
    }
}

if ($claim_file_found === false) {
    $claim_file_dir = $GLOBALS['OE_SITE_DIR'] . "/documents/edi/";
}

$fname = $claim_file_dir . $fname;

if (strtolower(substr($fname, (strlen($fname) - 4))) == ".pdf") {
    $content_type = "application/pdf";
}

if (!file_exists($fname)) {
    echo xlt("The claim file: ") . text($_GET['key']) . xlt(" could not be accessed.");
} else {
    $fp = fopen($fname, 'r');

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: $content_type");
    header("Content-Length: " . filesize($fname));
    header("Content-Disposition: attachment; filename=" . basename($fname));

    // dump the picture and stop the script
    fpassthru($fp);

    // If the caller sets the delete flag, delete the file when we're done serving it
    // This is the common case of a temporary file when validation-only is performed
    // by the BillingProcessor
    if (
        isset($_GET['delete']) &&
        $_GET['delete'] == 1
    ) {
        unlink($fname);
    }
}

exit;
