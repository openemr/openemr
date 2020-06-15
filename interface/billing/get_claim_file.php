<?php

/**
 * get_claim_file.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../globals.php");
require_once $GLOBALS['OE_SITE_DIR'] . "/config.php";

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$content_type = "text/plain";
$claim_file_dir = $GLOBALS['OE_SITE_DIR'] . "/documents/edi/";

$fname = $_GET['key'];
$fname = preg_replace("[/]", "", $fname);
$fname = preg_replace("[\.\.]", "", $fname);
$fname = preg_replace("[\\\\]", "", $fname);

if (strtolower(substr($fname, (strlen($fname) - 4))) == ".pdf") {
    $content_type = "application/pdf";
}

$fname = $claim_file_dir . $fname;

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
}

exit;
