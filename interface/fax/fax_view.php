<?php

/**
 * fax_view.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2016 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$ffname = '';
$jobid = $_GET['jid'];
if ($jobid) {
    $jfname = $GLOBALS['hylafax_basedir'] . "/sendq/q" . check_file_dir_name($jobid);
    if (!file_exists($jfname)) {
        $jfname = $GLOBALS['hylafax_basedir'] . "/doneq/q" . check_file_dir_name($jobid);
    }

    $jfhandle = fopen($jfname, 'r');
    if (!$jfhandle) {
        echo "I am in these groups: ";
        passthru("groups");
        echo "<br />";
        die(xlt("Cannot open ") . text($jfname));
    }

    while (!feof($jfhandle)) {
        $line = trim(fgets($jfhandle));
        if (substr($line, 0, 12) == '!postscript:') {
            $ffname = $GLOBALS['hylafax_basedir'] . '/' .
                substr($line, strrpos($line, ':') + 1);
            break;
        }
    }

    fclose($jfhandle);
    if (!$ffname) {
        die(xlt("Cannot find postscript document reference in ") . text($jfname));
    }
} elseif ($_GET['scan']) {
    $ffname = $GLOBALS['scanner_output_directory'] . '/' . check_file_dir_name($_GET['scan']);
} else {
    $ffname = $GLOBALS['hylafax_basedir'] . '/recvq/' . check_file_dir_name($_GET['file']);
}

if (!file_exists($ffname)) {
    die(xlt("Cannot find ") . text($ffname));
}

if (!is_readable($ffname)) {
    die(xlt("I do not have permission to read ") . text($ffname));
}

ob_start();

$ext = substr($ffname, strrpos($ffname, '.'));
if ($ext == '.ps') {
    passthru("TMPDIR=/tmp ps2pdf '" . escapeshellarg($ffname) . "' -");
} elseif ($ext == '.pdf' || $ext == '.PDF') {
    readfile($ffname);
} else {
    passthru("tiff2pdf '" . escapeshellarg($ffname) . "'");
}

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/pdf");
header("Content-Length: " . ob_get_length());
header("Content-Disposition: inline; filename=" . basename($ffname, $ext) . '.pdf');

ob_end_flush();

exit;
