<?php

/**
 * Billing notes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/forms.inc");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$ptid     = $_GET['ptid'] + 0;
$encid    = $_GET['encid'] + 0;
$formname = strtr(
    $_GET['formname'],
    array('.' => '', '\\' => '', '/' => '', '\'' => '', '"' => '', "\r" => '', "\n" => '')
);
$formid   = $_GET['formid'] + 0;

if (!hasFormPermission($formname)) {
    exit;
}

if (substr($formname, 0, 3) == 'LBF') {
    include_once("{$GLOBALS['incdir']}/forms/LBF/report.php");
    lbf_report($ptid, $encid, 2, $formid, $formname);
} else {
    include_once("{$GLOBALS['incdir']}/forms/$formname/report.php");
    $report_function = $formname . '_report';
    if (!function_exists($report_function)) {
        exit;
    }

    call_user_func($report_function, $ptid, $encid, 2, $formid);
}
