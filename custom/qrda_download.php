<?php

/**
 *
 * QRDA Download
 *
 * Copyright (C) 2015 Ensoftek, Inc
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ensoftek
 * @link    http://www.open-emr.org
 */

// This program exports(Download) to QRDA Category III XML.

require_once("../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$qrda_fname = $_GET['qrda_fname'];
check_file_dir_name($qrda_fname);

if ($qrda_fname != "") {
    $qrda_file_path = $GLOBALS['OE_SITE_DIR'] . "/documents/cqm_qrda/";
    $xmlurl = $qrda_file_path . $qrda_fname;

    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false); // required for certain browsers
    header('Content-type: application/xml');
    header("Content-Disposition: attachment; filename=\"" . basename($xmlurl) . "\";");
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . filesize($xmlurl));
    ob_clean();
    flush();
    readfile($xmlurl);
} else {
    echo xlt("File path not found.");
}
